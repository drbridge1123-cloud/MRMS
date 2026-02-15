<?php
// POST /api/health-ledger/import - Import CSV file (V2 parent/child structure)
$userId = requireAuth();

if (empty($_FILES['file'])) {
    errorResponse('No file uploaded');
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    errorResponse('File upload error');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    errorResponse('Only CSV files are allowed');
}

if ($file['size'] > 5 * 1024 * 1024) {
    errorResponse('File too large (max 5MB)');
}

$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    errorResponse('Could not read file');
}

// Read header row
$header = fgetcsv($handle);
if (!$header) {
    fclose($handle);
    errorResponse('Empty CSV file');
}

// Normalize headers
$header = array_map(function($h) {
    return strtolower(trim(preg_replace('/[\x{FEFF}]/u', '', $h)));
}, $header);

// Map CSV columns to expected fields
$colMap = [];
$knownColumns = [
    'req date' => 'request_date',
    'name' => 'client_name',
    'case no' => 'case_number',
    'insurance carrier' => 'insurance_carrier',
    '(via)' => 'request_method',
    'req' => 'sent_date',
    'req ' => 'sent_date',
    'by' => 'assigned_to',
    '1st follow up' => 'first_followup',
    '2nd follow up' => 'second_followup',
    'note' => 'note',
    'status' => 'status',
];

foreach ($header as $idx => $col) {
    $col = trim($col);
    if (isset($knownColumns[$col])) {
        $colMap[$knownColumns[$col]] = $idx;
    }
}

// Pre-load user mapping
$users = dbFetchAll("SELECT id, full_name FROM users", []);
$userMap = [];
foreach ($users as $u) {
    $userMap[strtolower($u['full_name'])] = (int)$u['id'];
}

// Pre-load cases mapping
$cases = dbFetchAll("SELECT id, case_number FROM cases", []);
$caseMap = [];
foreach ($cases as $c) {
    $caseMap[$c['case_number']] = (int)$c['id'];
}

$itemsCreated = 0;
$requestsCreated = 0;
$skipped = 0;
$errors = [];
$rowNum = 1;

// Method mapping
$methodMap = [
    'fax' => 'fax',
    'email' => 'email',
    'e-mail' => 'email',
    'portal' => 'portal',
    'phone' => 'phone',
    'mail' => 'mail',
];

// Status mapping (old → V2 overall_status)
$statusMap = [
    'sent' => 'requesting',
    'follow up' => 'follow_up',
    'received' => 'received',
    'done' => 'done',
];

// Track current parent row context
$currentClient = '';
$currentCaseNumber = '';

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;

    // Skip completely empty rows
    $nonEmpty = array_filter($row, function($v) { return trim($v) !== ''; });
    if (empty($nonEmpty)) continue;

    // Get values by column index
    $getValue = function($field) use ($colMap, $row) {
        return isset($colMap[$field]) && isset($row[$colMap[$field]]) ? trim($row[$colMap[$field]]) : '';
    };

    $clientName = $getValue('client_name');
    $caseNumber = $getValue('case_number');
    $carrier = $getValue('insurance_carrier');

    // Update parent context if this row has a client name
    if ($clientName) $currentClient = $clientName;
    if ($caseNumber) $currentCaseNumber = $caseNumber;

    // Skip rows without carrier (no meaningful data)
    if (!$carrier) continue;

    // Use parent context for child rows
    $finalClient = $clientName ?: $currentClient;
    $finalCaseNumber = $caseNumber ?: $currentCaseNumber;

    if (!$finalClient) {
        $skipped++;
        continue;
    }

    // Parse method
    $methodRaw = strtolower($getValue('request_method'));
    $method = $methodMap[$methodRaw] ?? null;

    // Parse dates
    $sentDate = parseImportDate($getValue('sent_date'));
    $followup1 = parseImportDate($getValue('first_followup'));
    $followup2 = parseImportDate($getValue('second_followup'));

    // Parse assigned user
    $assignedRaw = strtolower($getValue('assigned_to'));
    $assignedId = $userMap[$assignedRaw] ?? null;

    // Parse status → V2 overall_status
    $statusRaw = strtolower($getValue('status'));
    $overallStatus = $statusMap[$statusRaw] ?? ($sentDate ? 'requesting' : 'not_started');

    // Match case
    $caseId = $caseMap[$finalCaseNumber] ?? null;

    // Parse note
    $note = $getValue('note');

    try {
        // 1. Insert parent: health_ledger_items
        $itemData = [
            'case_id' => $caseId,
            'case_number' => $finalCaseNumber ?: null,
            'client_name' => $finalClient,
            'insurance_carrier' => $carrier,
            'overall_status' => $overallStatus,
            'assigned_to' => $assignedId,
            'note' => $note ?: null,
        ];
        $itemId = dbInsert('health_ledger_items', $itemData);
        $itemsCreated++;

        // 2. Insert child requests if there was a sent date + method
        if ($sentDate && $method) {
            $requestData = [
                'item_id' => $itemId,
                'request_type' => 'initial',
                'request_date' => $sentDate,
                'request_method' => $method,
                'send_status' => 'sent',
                'sent_at' => $sentDate . ' 00:00:00',
                'created_by' => $assignedId ?? $userId,
            ];
            dbInsert('hl_requests', $requestData);
            $requestsCreated++;

            // Follow-up 1
            if ($followup1 && $method) {
                $fu1Data = [
                    'item_id' => $itemId,
                    'request_type' => 'follow_up',
                    'request_date' => $followup1,
                    'request_method' => $method,
                    'send_status' => 'sent',
                    'sent_at' => $followup1 . ' 00:00:00',
                    'created_by' => $assignedId ?? $userId,
                ];
                dbInsert('hl_requests', $fu1Data);
                $requestsCreated++;
            }

            // Follow-up 2
            if ($followup2 && $method) {
                $fu2Data = [
                    'item_id' => $itemId,
                    'request_type' => 'follow_up',
                    'request_date' => $followup2,
                    'request_method' => $method,
                    'send_status' => 'sent',
                    'sent_at' => $followup2 . ' 00:00:00',
                    'created_by' => $assignedId ?? $userId,
                ];
                dbInsert('hl_requests', $fu2Data);
                $requestsCreated++;
            }
        }
    } catch (Exception $e) {
        $errors[] = ['row' => $rowNum, 'message' => 'Insert failed: ' . $e->getMessage()];
        $skipped++;
    }
}

fclose($handle);

logActivity($userId, 'health_ledger_imported', 'health_ledger_item', null, [
    'items' => $itemsCreated,
    'requests' => $requestsCreated,
    'skipped' => $skipped
]);

successResponse([
    'items_created' => $itemsCreated,
    'requests_created' => $requestsCreated,
    'skipped' => $skipped,
    'errors' => array_slice($errors, 0, 20),
], "Imported {$itemsCreated} items with {$requestsCreated} requests, skipped {$skipped}");

// Helper: parse various date formats from CSV
function parseImportDate($dateStr) {
    if (!$dateStr) return null;
    $dateStr = trim($dateStr);

    // Invalid patterns
    if (preg_match('/^0\/0\/0/', $dateStr)) return null;
    if (!preg_match('/\d/', $dateStr)) return null;

    // Try m/d/Y format
    $d = DateTime::createFromFormat('n/j/Y', $dateStr);
    if ($d) return $d->format('Y-m-d');

    $d = DateTime::createFromFormat('m/d/Y', $dateStr);
    if ($d) return $d->format('Y-m-d');

    // Try Y-m-d format
    $d = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($d) return $d->format('Y-m-d');

    return null;
}
