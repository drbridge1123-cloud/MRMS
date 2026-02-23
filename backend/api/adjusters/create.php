<?php
// POST /api/adjusters - Create a new adjuster

$userId = requireAuth();
$input = getInput();

$errors = validateRequired($input, ['first_name', 'last_name']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors), 422);
}

$data = [
    'first_name' => sanitizeString($input['first_name']),
    'last_name' => sanitizeString($input['last_name'])
];

// Validate insurance_company_id if provided
if (!empty($input['insurance_company_id'])) {
    $company = dbFetchOne("SELECT id FROM insurance_companies WHERE id = ?", [(int)$input['insurance_company_id']]);
    if (!$company) {
        errorResponse('Insurance company not found', 422);
    }
    $data['insurance_company_id'] = (int)$input['insurance_company_id'];
}

// Validate adjuster_type if provided
$validTypes = ['pip', 'um', 'uim', '3rd_party', 'liability', 'pd', 'bi'];
if (!empty($input['adjuster_type'])) {
    if (!in_array($input['adjuster_type'], $validTypes)) {
        errorResponse('Invalid adjuster type', 422);
    }
    $data['adjuster_type'] = $input['adjuster_type'];
}

$optionalFields = ['title', 'phone', 'fax', 'email', 'notes'];
foreach ($optionalFields as $field) {
    if (isset($input[$field])) {
        $data[$field] = sanitizeString($input[$field]);
    }
}

$id = dbInsert('adjusters', $data);

logActivity($userId, 'create', 'adjuster', $id, [
    'name' => $data['first_name'] . ' ' . $data['last_name']
]);

$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS insurance_company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON a.insurance_company_id = ic.id
     WHERE a.id = ?",
    [$id]
);

successResponse($adjuster, 'Adjuster created successfully');
