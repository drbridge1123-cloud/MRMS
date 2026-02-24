<?php
// POST /api/insurance-companies - Create a new insurance company

$userId = requireAuth();
$input = getInput();

$errors = validateRequired($input, ['name', 'type']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors), 422);
}

$allowedTypes = ['auto', 'health', 'workers_comp', 'liability', 'um_uim', 'government', 'other'];
if (!validateEnum($input['type'], $allowedTypes)) {
    errorResponse('Invalid insurance type. Allowed: ' . implode(', ', $allowedTypes), 422);
}

$data = [
    'name' => sanitizeString($input['name']),
    'type' => $input['type']
];

$optionalFields = ['phone', 'fax', 'email', 'address', 'city', 'state', 'zip', 'website', 'notes'];
foreach ($optionalFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

$id = dbInsert('insurance_companies', $data);

logActivity($userId, 'create', 'insurance_company', $id, [
    'name' => $data['name'],
    'type' => $data['type']
]);

$company = dbFetchOne("SELECT * FROM insurance_companies WHERE id = ?", [$id]);
successResponse($company, 'Insurance company created successfully');
