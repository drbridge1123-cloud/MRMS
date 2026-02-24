<?php
// POST /api/adjusters/import - Import adjusters from CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$csv = parseCSV('file');

$validTypes = ['pip', 'um', 'uim', '3rd_party', 'liability', 'pd', 'bi'];
$typeAliases = [
    'pip' => 'pip',
    'um' => 'um',
    'uim' => 'uim',
    '3rd party' => '3rd_party',
    '3rd_party' => '3rd_party',
    'liability' => 'liability',
    'pd' => 'pd',
    'bi' => 'bi',
];
$truthy = ['1', 'true', 'yes', 'y'];

$imported = 0;
$skipped = 0;
$errors = [];

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    foreach ($csv['rows'] as $i => $row) {
        $rowNum = $i + 2;

        // Required: first_name, last_name
        $firstName = trim($row['first_name'] ?? '');
        $lastName = trim($row['last_name'] ?? '');
        if ($firstName === '' || $lastName === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'first_name and last_name are required'];
            $skipped++;
            continue;
        }

        $data = [
            'first_name' => sanitizeString($firstName),
            'last_name' => sanitizeString($lastName),
        ];

        // Resolve insurance_company by name
        $companyName = trim($row['insurance_company'] ?? '');
        if ($companyName !== '') {
            $company = dbFetchOne("SELECT id FROM insurance_companies WHERE name = ?", [$companyName]);
            if ($company) {
                $data['insurance_company_id'] = $company['id'];
            } else {
                $errors[] = ['row' => $rowNum, 'message' => "Insurance company '{$companyName}' not found. Import insurance companies first."];
                $skipped++;
                continue;
            }
        }

        // Validate adjuster_type
        $adjType = strtolower(trim($row['adjuster_type'] ?? ''));
        if ($adjType !== '') {
            if (isset($typeAliases[$adjType])) {
                $adjType = $typeAliases[$adjType];
            }
            if (!in_array($adjType, $validTypes)) {
                $errors[] = ['row' => $rowNum, 'message' => "invalid adjuster_type '{$adjType}'. Allowed: " . implode(', ', $validTypes)];
                $skipped++;
                continue;
            }
            $data['adjuster_type'] = $adjType;
        }

        // Optional string fields
        $stringFields = ['title', 'phone', 'fax', 'email', 'notes'];
        foreach ($stringFields as $field) {
            $val = trim($row[$field] ?? '');
            if ($val !== '') {
                $data[$field] = sanitizeString($val);
            }
        }

        // is_active (default 1)
        $active = strtolower(trim($row['is_active'] ?? ''));
        if ($active !== '') {
            $data['is_active'] = in_array($active, $truthy) ? 1 : 0;
        }

        dbInsert('adjusters', $data);
        $imported++;
    }

    $pdo->commit();

    logActivity($userId, 'bulk_import', 'adjuster', null, [
        'imported' => $imported,
        'skipped' => $skipped,
    ]);

    successResponse([
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors,
    ], "{$imported} adjusters imported successfully");
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Import failed: ' . $e->getMessage(), 500);
}
