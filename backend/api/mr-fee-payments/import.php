<?php
// POST /api/mr-fee-payments/import - Import cost ledger from Excel-exported CSV
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

// Require case_id
$caseId = (int)($_POST['case_id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$case = dbFetchOne("SELECT id, case_number, client_name FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Check file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    errorResponse('No file uploaded or upload error');
}

if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    errorResponse('File size exceeds 5MB limit');
}

$content = file_get_contents($_FILES['file']['tmp_name']);
if (!$content || strlen(trim($content)) === 0) {
    errorResponse('File is empty');
}

// Remove BOM, normalize line endings
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
$content = str_replace(["\r\n", "\r"], "\n", $content);

$lines = explode("\n", $content);
$parsedRows = [];
$currentCategory = 'mr_cost';

// Build staff name → user ID lookup
$staffUsers = dbFetchAll("SELECT id, full_name FROM users");
$staffMap = [];
foreach ($staffUsers as $u) {
    // Map by first name (lowercase)
    $firstName = strtolower(explode(' ', trim($u['full_name']))[0]);
    $staffMap[$firstName] = (int)$u['id'];
    // Also map full name
    $staffMap[strtolower(trim($u['full_name']))] = (int)$u['id'];
}

// Get case providers for matching
$caseProviders = dbFetchAll(
    "SELECT cp.id, p.name AS provider_name
     FROM case_providers cp
     JOIN providers p ON cp.provider_id = p.id
     WHERE cp.case_id = ?",
    [$caseId]
);

foreach ($lines as $line) {
    $cols = str_getcsv($line);

    // Pad columns to avoid index errors
    while (count($cols) < 30) {
        $cols[] = '';
    }

    // Detect section markers
    $col4 = strtolower(trim($cols[4] ?? ''));
    if (strpos($col4, 'costs') !== false && strpos($col4, 'other') === false) {
        $currentCategory = 'mr_cost';
        continue;
    }
    if (strpos($col4, 'litigation') !== false) {
        $currentCategory = 'litigation';
        continue;
    }
    if (strpos($col4, 'other') !== false) {
        $currentCategory = 'other';
        continue;
    }

    // Skip header rows, empty rows, name row, etc.
    if (strpos($col4, 'date') !== false || strpos($col4, 'name') !== false) continue;

    // Parse data: need at least a provider name (col 6) or description (col 10)
    $providerName = trim($cols[6] ?? '');
    $description = trim($cols[10] ?? '');
    $billedRaw = trim($cols[15] ?? '');

    // Skip if no meaningful data
    if ($providerName === '' && $description === '' && $billedRaw === '') continue;

    // Parse amounts - remove $, spaces, parentheses
    $billedAmount = parseAmount($billedRaw);
    $paidAmount = parseAmount(trim($cols[23] ?? ''));

    // Skip zero rows (empty template rows with $0 or $-)
    if ($billedAmount == 0 && $paidAmount == 0) continue;

    // Parse date
    $dateRaw = trim($cols[4] ?? '');
    $paymentDate = parseExcelDate($dateRaw);
    $paidDateRaw = trim($cols[25] ?? '');
    $paidDate = parseExcelDate($paidDateRaw);

    // Use paid date if available, otherwise payment date
    $finalDate = $paidDate ?: $paymentDate;

    // Parse payment type
    $paymentTypeRaw = strtolower(trim($cols[19] ?? ''));
    $paymentType = null;
    if (strpos($paymentTypeRaw, 'check') !== false) $paymentType = 'check';
    elseif (strpos($paymentTypeRaw, 'card') !== false) $paymentType = 'card';
    elseif (strpos($paymentTypeRaw, 'cash') !== false) $paymentType = 'cash';
    elseif (strpos($paymentTypeRaw, 'wire') !== false) $paymentType = 'wire';
    elseif ($paymentTypeRaw !== '') $paymentType = 'other';

    // Parse staff code → user ID
    $staffCode = strtolower(trim($cols[21] ?? ''));
    $paidBy = $staffMap[$staffCode] ?? null;

    // Try to match provider to case_providers
    $cpId = null;
    if ($providerName !== '') {
        foreach ($caseProviders as $cp) {
            if (stripos($cp['provider_name'], $providerName) !== false
                || stripos($providerName, $cp['provider_name']) !== false) {
                $cpId = (int)$cp['id'];
                break;
            }
        }
    }

    $parsedRows[] = [
        'case_id' => $caseId,
        'case_provider_id' => $cpId,
        'expense_category' => $currentCategory,
        'provider_name' => $providerName ?: null,
        'description' => $description ?: null,
        'billed_amount' => $billedAmount,
        'paid_amount' => $paidAmount,
        'payment_type' => $paymentType,
        'payment_date' => $finalDate,
        'paid_by' => $paidBy,
        'created_by' => $userId,
    ];
}

if (empty($parsedRows)) {
    errorResponse('No cost entries found in the CSV. Make sure the file matches the expected Excel format.');
}

// Check for preview mode
if (!empty($_POST['preview'])) {
    // Return parsed data for preview without inserting
    $preview = array_map(function($row) use ($staffUsers) {
        $staffName = '';
        if ($row['paid_by']) {
            foreach ($staffUsers as $u) {
                if ((int)$u['id'] === $row['paid_by']) {
                    $staffName = $u['full_name'];
                    break;
                }
            }
        }
        return [
            'provider_name' => $row['provider_name'],
            'description' => $row['description'],
            'expense_category' => $row['expense_category'],
            'billed_amount' => $row['billed_amount'],
            'paid_amount' => $row['paid_amount'],
            'payment_type' => $row['payment_type'],
            'payment_date' => $row['payment_date'],
            'paid_by_name' => $staffName,
            'matched_provider' => $row['case_provider_id'] ? true : false,
        ];
    }, $parsedRows);

    jsonResponse([
        'success' => true,
        'preview' => $preview,
        'count' => count($preview),
        'total_billed' => array_sum(array_column($parsedRows, 'billed_amount')),
        'total_paid' => array_sum(array_column($parsedRows, 'paid_amount')),
    ]);
}

// Insert all rows
$imported = 0;
$errors = [];
foreach ($parsedRows as $i => $row) {
    try {
        dbInsert('mr_fee_payments', $row);
        $imported++;

        // Sync MBDS office_paid if linked to a provider
        if ($row['case_provider_id']) {
            syncMbdsOfficePaid($row['case_provider_id']);
        }
    } catch (Exception $e) {
        $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
    }
}

logActivity($userId, 'cost_import', 'case', $caseId, [
    'case_number' => $case['case_number'],
    'imported' => $imported,
    'total_rows' => count($parsedRows),
]);

jsonResponse([
    'success' => true,
    'imported' => $imported,
    'total_rows' => count($parsedRows),
    'errors' => $errors,
    'message' => "Imported {$imported} cost entries",
]);

// --- Helper functions ---

function parseAmount($str) {
    if (!$str || $str === '' || $str === '-') return 0;
    // Remove $, spaces, commas
    $str = str_replace(['$', ' ', ','], '', $str);
    // Handle parentheses for negative
    if (preg_match('/^\((.+)\)$/', $str, $m)) {
        return -abs((float)$m[1]);
    }
    $val = (float)$str;
    return round($val, 2);
}

function parseExcelDate($str) {
    if (!$str || trim($str) === '') return null;
    $str = trim($str);
    // Try M/D/YYYY format
    $ts = strtotime($str);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }
    return null;
}

function syncMbdsOfficePaid($caseProviderId) {
    $total = dbFetchOne(
        "SELECT COALESCE(SUM(paid_amount), 0) AS total FROM mr_fee_payments WHERE case_provider_id = ?",
        [$caseProviderId]
    );
    $line = dbFetchOne(
        "SELECT id FROM mbds_lines WHERE case_provider_id = ?",
        [$caseProviderId]
    );
    if ($line) {
        dbUpdate('mbds_lines', ['office_paid' => $total['total']], 'id = ?', [$line['id']]);
    }
}
