<?php
// POST /api/cases - Create a new case
$userId = requireAuth();

$input = getInput();

// Validate required fields
$errors = validateRequired($input, ['case_number', 'client_name', 'client_dob', 'doi', 'assigned_to']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

// Check for duplicate: same case_number AND same client_dob
$clientDob = !empty($input['client_dob']) ? $input['client_dob'] : null;
if ($clientDob) {
    $existing = dbFetchOne(
        "SELECT id FROM cases WHERE case_number = ? AND client_dob = ?",
        [sanitizeString($input['case_number']), $clientDob]
    );
    if ($existing) {
        errorResponse('A case with this case number and date of birth already exists');
    }
}

// Validate optional date fields
if (!empty($input['client_dob']) && !validateDate($input['client_dob'])) {
    errorResponse('Invalid date format for client_dob. Use YYYY-MM-DD');
}

if (!empty($input['doi']) && !validateDate($input['doi'])) {
    errorResponse('Invalid date format for doi. Use YYYY-MM-DD');
}

// Validate status if provided
if (!empty($input['status'])) {
    $allowedStatuses = ['collecting', 'in_review', 'verification', 'completed', 'closed'];
    if (!validateEnum($input['status'], $allowedStatuses)) {
        errorResponse('Invalid status. Allowed: ' . implode(', ', $allowedStatuses));
    }
}

// Validate assigned_to if provided
if (!empty($input['assigned_to'])) {
    $assignee = dbFetchOne("SELECT id FROM users WHERE id = ? AND is_active = 1", [(int)$input['assigned_to']]);
    if (!$assignee) {
        errorResponse('Assigned user not found or inactive');
    }
}

// Build insert data
$data = [
    'case_number' => sanitizeString($input['case_number']),
    'client_name' => sanitizeString($input['client_name']),
];

// Optional fields
if (isset($input['client_dob']) && $input['client_dob'] !== '') {
    $data['client_dob'] = $input['client_dob'];
}
if (isset($input['doi']) && $input['doi'] !== '') {
    $data['doi'] = $input['doi'];
}
if (isset($input['assigned_to']) && $input['assigned_to'] !== '') {
    $data['assigned_to'] = (int)$input['assigned_to'];
}
if (isset($input['attorney_name']) && $input['attorney_name'] !== '') {
    $data['attorney_name'] = sanitizeString($input['attorney_name']);
}
if (isset($input['ini_completed'])) {
    $data['ini_completed'] = $input['ini_completed'] ? 1 : 0;
}
if (isset($input['notes']) && $input['notes'] !== '') {
    $data['notes'] = sanitizeString($input['notes']);
}
if (!empty($input['status'])) {
    $data['status'] = $input['status'];
}

// Auto-assign based on status owner
$caseStatus = $data['status'] ?? 'collecting';
$statusOwner = STATUS_OWNER_MAP[$caseStatus] ?? null;
if ($statusOwner) {
    $data['assigned_to'] = $statusOwner;
}

$newId = dbInsert('cases', $data);

logActivity($userId, 'create', 'case', $newId, [
    'case_number' => $data['case_number'],
    'client_name' => $data['client_name']
]);

// Return the newly created case
$newCase = dbFetchOne(
    "SELECT c.*, u.full_name AS assigned_to_name
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE c.id = ?",
    [$newId]
);

successResponse($newCase, 'Case created successfully');
