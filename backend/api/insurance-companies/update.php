<?php
// PUT /api/insurance-companies/{id} - Update an insurance company

$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('Insurance company ID is required', 400);
}

$existing = dbFetchOne("SELECT id, name FROM insurance_companies WHERE id = ?", [$id]);
if (!$existing) {
    errorResponse('Insurance company not found', 404);
}

$input = getInput();

if (!empty($input['type'])) {
    $allowedTypes = ['auto', 'health', 'workers_comp', 'liability', 'um_uim', 'other'];
    if (!validateEnum($input['type'], $allowedTypes)) {
        errorResponse('Invalid insurance type. Allowed: ' . implode(', ', $allowedTypes), 422);
    }
}

$updateData = [];
$stringFields = ['name', 'phone', 'fax', 'email', 'address', 'city', 'state', 'zip', 'website', 'notes'];
foreach ($stringFields as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = $input[$field] !== null ? sanitizeString($input[$field]) : null;
    }
}

if (array_key_exists('type', $input)) {
    $updateData['type'] = $input['type'];
}

if (!empty($updateData)) {
    dbUpdate('insurance_companies', $updateData, 'id = ?', [$id]);
}

logActivity($userId, 'update', 'insurance_company', $id, [
    'name' => $existing['name'],
    'updated_fields' => array_keys($updateData)
]);

$company = dbFetchOne("SELECT * FROM insurance_companies WHERE id = ?", [$id]);
successResponse($company, 'Insurance company updated successfully');
