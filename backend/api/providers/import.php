<?php
// POST /api/providers/import - Import providers from CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$csv = parseCSV('file');

$allowedTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'other'];
$allowedMethods = ['email', 'fax', 'portal', 'phone', 'mail'];
$allowedLevels = ['easy', 'medium', 'hard'];
$truthy = ['1', 'true', 'yes', 'y'];

$imported = 0;
$skipped = 0;
$errors = [];

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    foreach ($csv['rows'] as $i => $row) {
        $rowNum = $i + 2;
        $rowErrors = [];

        // Required: name
        $name = trim($row['name'] ?? '');
        if ($name === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'name is required'];
            $skipped++;
            continue;
        }

        // Required: type
        $type = strtolower(trim($row['type'] ?? ''));
        if ($type === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'type is required'];
            $skipped++;
            continue;
        }
        if (!validateEnum($type, $allowedTypes)) {
            $errors[] = ['row' => $rowNum, 'message' => "invalid type '{$type}'. Allowed: " . implode(', ', $allowedTypes)];
            $skipped++;
            continue;
        }

        // Check duplicate name
        $existing = dbFetchOne("SELECT id FROM providers WHERE name = ?", [sanitizeString($name)]);
        if ($existing) {
            $errors[] = ['row' => $rowNum, 'message' => "Provider '{$name}' already exists"];
            $skipped++;
            continue;
        }

        $data = [
            'name' => sanitizeString($name),
            'type' => $type,
        ];

        // Optional string fields
        $stringFields = ['address', 'phone', 'fax', 'email', 'portal_url',
                         'third_party_name', 'third_party_contact', 'notes'];
        foreach ($stringFields as $field) {
            $val = trim($row[$field] ?? '');
            if ($val !== '') {
                $data[$field] = sanitizeString($val);
            }
        }

        // Optional: preferred_method
        $method = strtolower(trim($row['preferred_method'] ?? ''));
        if ($method !== '') {
            if (!validateEnum($method, $allowedMethods)) {
                $rowErrors[] = "invalid preferred_method '{$method}'";
            } else {
                $data['preferred_method'] = $method;
            }
        }

        // Optional: difficulty_level
        $level = strtolower(trim($row['difficulty_level'] ?? ''));
        if ($level !== '') {
            if (!validateEnum($level, $allowedLevels)) {
                $rowErrors[] = "invalid difficulty_level '{$level}'";
            } else {
                $data['difficulty_level'] = $level;
            }
        }

        // Optional: uses_third_party
        $thirdParty = strtolower(trim($row['uses_third_party'] ?? ''));
        if ($thirdParty !== '') {
            $data['uses_third_party'] = in_array($thirdParty, $truthy) ? 1 : 0;
        }

        // Skip row if validation errors
        if (!empty($rowErrors)) {
            $errors[] = ['row' => $rowNum, 'message' => implode('; ', $rowErrors)];
            $skipped++;
            continue;
        }

        dbInsert('providers', $data);
        $imported++;
    }

    $pdo->commit();

    logActivity($userId, 'bulk_import', 'provider', null, [
        'imported' => $imported,
        'skipped' => $skipped,
    ]);

    successResponse([
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors,
    ], "{$imported} providers imported successfully");
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Import failed: ' . $e->getMessage(), 500);
}
