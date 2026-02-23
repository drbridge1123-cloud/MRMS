<?php
// PUT /api/adjusters/{id} - Update an adjuster

$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('Adjuster ID is required', 400);
}

$existing = dbFetchOne("SELECT id, first_name, last_name FROM adjusters WHERE id = ?", [$id]);
if (!$existing) {
    errorResponse('Adjuster not found', 404);
}

$input = getInput();

$updateData = [];
$stringFields = ['first_name', 'last_name', 'title', 'phone', 'fax', 'email', 'notes'];
foreach ($stringFields as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = $input[$field] !== null ? sanitizeString($input[$field]) : null;
    }
}

if (array_key_exists('insurance_company_id', $input)) {
    if (!empty($input['insurance_company_id'])) {
        $company = dbFetchOne("SELECT id FROM insurance_companies WHERE id = ?", [(int)$input['insurance_company_id']]);
        if (!$company) {
            errorResponse('Insurance company not found', 422);
        }
        $updateData['insurance_company_id'] = (int)$input['insurance_company_id'];
    } else {
        $updateData['insurance_company_id'] = null;
    }
}

if (array_key_exists('adjuster_type', $input)) {
    $validTypes = ['pip', 'um', 'uim', '3rd_party', 'liability', 'pd', 'bi'];
    if (!empty($input['adjuster_type']) && !in_array($input['adjuster_type'], $validTypes)) {
        errorResponse('Invalid adjuster type', 422);
    }
    $updateData['adjuster_type'] = !empty($input['adjuster_type']) ? $input['adjuster_type'] : null;
}

if (array_key_exists('is_active', $input)) {
    $updateData['is_active'] = $input['is_active'] ? 1 : 0;
}

if (!empty($updateData)) {
    dbUpdate('adjusters', $updateData, 'id = ?', [$id]);
}

logActivity($userId, 'update', 'adjuster', $id, [
    'name' => $existing['first_name'] . ' ' . $existing['last_name'],
    'updated_fields' => array_keys($updateData)
]);

$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS insurance_company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON a.insurance_company_id = ic.id
     WHERE a.id = ?",
    [$id]
);

successResponse($adjuster, 'Adjuster updated successfully');
