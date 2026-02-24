<?php
// POST /api/insurance-companies/import - Import insurance companies from CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$csv = parseCSV('file');

// Column name aliases — auto-map common variations
$columnAliases = [
    'company_name' => 'name',
    'company' => 'name',
    'insurance_name' => 'name',
    'insurance_type' => 'type',
    'ins_type' => 'type',
    'phone_number' => 'phone',
    'fax_number' => 'fax',
    'email_address' => 'email',
    'web' => 'website',
    'url' => 'website',
    'zip_code' => 'zip',
    'zipcode' => 'zip',
    'note' => 'notes',
    'comment' => 'notes',
    'comments' => 'notes',
];

// Remap CSV headers using aliases
$remappedRows = [];
foreach ($csv['rows'] as $row) {
    $mapped = [];
    foreach ($row as $key => $value) {
        $normalizedKey = strtolower(trim(str_replace(' ', '_', $key)));
        $finalKey = $columnAliases[$normalizedKey] ?? $normalizedKey;
        // Don't overwrite if already set (prefer direct match)
        if (!isset($mapped[$finalKey]) || $mapped[$finalKey] === '') {
            $mapped[$finalKey] = $value;
        }
    }
    $remappedRows[] = $mapped;
}

$allowedTypes = ['auto', 'health', 'workers_comp', 'liability', 'um_uim', 'government', 'other'];
$typeAliases = [
    'auto' => 'auto',
    'automobile' => 'auto',
    'car' => 'auto',
    'health' => 'health',
    'medical' => 'health',
    'workers comp' => 'workers_comp',
    'workers_comp' => 'workers_comp',
    'workers compensation' => 'workers_comp',
    'work_comp' => 'workers_comp',
    'liability' => 'liability',
    'um/uim' => 'um_uim',
    'um_uim' => 'um_uim',
    'government' => 'government',
    'govt' => 'government',
    'medicaid' => 'government',
    'medicare' => 'government',
    'va' => 'government',
    'tricare' => 'government',
    'other' => 'other',
];

$imported = 0;
$skipped = 0;
$errors = [];

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    foreach ($remappedRows as $i => $row) {
        $rowNum = $i + 2;

        // Required: name
        $name = trim($row['name'] ?? '');
        if ($name === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'name is required'];
            $skipped++;
            continue;
        }

        // Required: type (fall back to 'other' if missing)
        $type = strtolower(trim($row['type'] ?? ''));
        if ($type === '' || $type === 'insurance') {
            // If type column just says "Insurance" (generic), try insurance_type or default to other
            $type = 'other';
        }
        if (isset($typeAliases[$type])) {
            $type = $typeAliases[$type];
        }
        if (!validateEnum($type, $allowedTypes)) {
            $errors[] = ['row' => $rowNum, 'message' => "invalid type '{$type}'. Allowed: " . implode(', ', $allowedTypes)];
            $skipped++;
            continue;
        }

        // Check duplicate name
        $existing = dbFetchOne("SELECT id FROM insurance_companies WHERE name = ?", [sanitizeString($name)]);
        if ($existing) {
            $errors[] = ['row' => $rowNum, 'message' => "Insurance company '{$name}' already exists"];
            $skipped++;
            continue;
        }

        $data = [
            'name' => sanitizeString($name),
            'type' => $type,
        ];

        $stringFields = ['phone', 'fax', 'email', 'address', 'city', 'state', 'zip', 'website', 'notes'];
        foreach ($stringFields as $field) {
            $val = trim($row[$field] ?? '');
            if ($val !== '') {
                $data[$field] = sanitizeString($val);
            }
        }

        dbInsert('insurance_companies', $data);
        $imported++;
    }

    $pdo->commit();

    logActivity($userId, 'bulk_import', 'insurance_company', null, [
        'imported' => $imported,
        'skipped' => $skipped,
    ]);

    successResponse([
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors,
    ], "{$imported} insurance companies imported successfully");
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Import failed: ' . $e->getMessage(), 500);
}
