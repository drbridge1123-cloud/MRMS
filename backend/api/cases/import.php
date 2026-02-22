<?php
// POST /api/cases/import - Import cases from CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$csv = parseCSV('file');

$allowedStatuses = ['collecting','verification','completed','rfd','final_verification','disbursement','accounting','closed'];
$imported = 0;
$skipped = 0;
$errors = [];

// Pre-fetch users for assigned_to matching
$users = dbFetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
$userMap = [];
foreach ($users as $u) {
    $userMap[strtolower(trim($u['full_name']))] = $u['id'];
}

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    foreach ($csv['rows'] as $i => $row) {
        $rowNum = $i + 2; // +2 because row 1 is header, array is 0-indexed
        $rowErrors = [];

        // Required: case_number
        $caseNumber = trim($row['case_number'] ?? '');
        if ($caseNumber === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'case_number is required'];
            $skipped++;
            continue;
        }

        // Required: client_name
        $clientName = trim($row['client_name'] ?? '');
        if ($clientName === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'client_name is required'];
            $skipped++;
            continue;
        }

        // Check duplicate case_number
        $existing = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [sanitizeString($caseNumber)]);
        if ($existing) {
            $errors[] = ['row' => $rowNum, 'message' => "Case number '{$caseNumber}' already exists"];
            $skipped++;
            continue;
        }

        // Build insert data
        $data = [
            'case_number' => sanitizeString($caseNumber),
            'client_name' => sanitizeString($clientName),
        ];

        // Optional: client_dob
        $dob = trim($row['client_dob'] ?? '');
        if ($dob !== '') {
            if (!validateDate($dob)) {
                $rowErrors[] = "invalid client_dob '{$dob}'";
            } else {
                $data['client_dob'] = $dob;
            }
        }

        // Optional: doi
        $doi = trim($row['doi'] ?? '');
        if ($doi !== '') {
            if (!validateDate($doi)) {
                $rowErrors[] = "invalid doi '{$doi}'";
            } else {
                $data['doi'] = $doi;
            }
        }

        // Optional: attorney_name
        $attorney = trim($row['attorney_name'] ?? '');
        if ($attorney !== '') {
            $data['attorney_name'] = sanitizeString($attorney);
        }

        // Optional: status
        $status = strtolower(trim($row['status'] ?? ''));
        if ($status !== '') {
            if (!validateEnum($status, $allowedStatuses)) {
                $rowErrors[] = "invalid status '{$status}'";
            } else {
                $data['status'] = $status;
            }
        }

        // Optional: notes
        $notes = trim($row['notes'] ?? '');
        if ($notes !== '') {
            $data['notes'] = sanitizeString($notes);
        }

        // Optional: assigned_to (match by full_name)
        $assignedTo = strtolower(trim($row['assigned_to'] ?? ''));
        if ($assignedTo !== '' && isset($userMap[$assignedTo])) {
            $data['assigned_to'] = $userMap[$assignedTo];
        }

        // Skip row if validation errors
        if (!empty($rowErrors)) {
            $errors[] = ['row' => $rowNum, 'message' => implode('; ', $rowErrors)];
            $skipped++;
            continue;
        }

        dbInsert('cases', $data);
        $imported++;
    }

    $pdo->commit();

    // Log activity
    logActivity($userId, 'bulk_import', 'case', null, [
        'imported' => $imported,
        'skipped' => $skipped,
    ]);

    successResponse([
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors,
    ], "{$imported} cases imported successfully");
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Import failed: ' . $e->getMessage(), 500);
}
