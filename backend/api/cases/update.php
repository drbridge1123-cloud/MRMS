<?php
// PUT /api/cases/{id} - Update an existing case
$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

// Check case exists
$existingCase = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$caseId]);
if (!$existingCase) {
    errorResponse('Case not found', 404);
}

$input = getInput();

if (empty($input)) {
    errorResponse('No data provided for update');
}

// Validate date fields if provided
if (isset($input['client_dob']) && $input['client_dob'] !== '' && !validateDate($input['client_dob'])) {
    errorResponse('Invalid date format for client_dob. Use YYYY-MM-DD');
}

if (isset($input['doi']) && $input['doi'] !== '' && !validateDate($input['doi'])) {
    errorResponse('Invalid date format for doi. Use YYYY-MM-DD');
}

// Validate status if provided
if (!empty($input['status'])) {
    $allowedStatuses = ['active', 'pending_review', 'completed', 'on_hold'];
    if (!validateEnum($input['status'], $allowedStatuses)) {
        errorResponse('Invalid status. Allowed: ' . implode(', ', $allowedStatuses));
    }
}

// Validate assigned_to if provided
if (isset($input['assigned_to']) && $input['assigned_to'] !== '' && $input['assigned_to'] !== null) {
    $assignee = dbFetchOne("SELECT id FROM users WHERE id = ? AND is_active = 1", [(int)$input['assigned_to']]);
    if (!$assignee) {
        errorResponse('Assigned user not found or inactive');
    }
}

// If case_number is being changed, check for duplicates
if (!empty($input['case_number']) && $input['case_number'] !== $existingCase['case_number']) {
    $duplicate = dbFetchOne("SELECT id FROM cases WHERE case_number = ? AND id != ?", [
        sanitizeString($input['case_number']),
        $caseId
    ]);
    if ($duplicate) {
        errorResponse('A case with this case number already exists');
    }
}

// Build update data from allowed fields
$allowedFields = ['case_number', 'client_name', 'client_dob', 'doi', 'assigned_to', 'status', 'attorney_name', 'ini_completed', 'notes'];
$data = [];
$changes = [];

foreach ($allowedFields as $field) {
    if (!array_key_exists($field, $input)) {
        continue;
    }

    $value = $input[$field];

    switch ($field) {
        case 'case_number':
        case 'client_name':
        case 'attorney_name':
        case 'notes':
            if ($value === '' || $value === null) {
                // Allow nulling optional fields, but not required ones
                if ($field === 'case_number' || $field === 'client_name') {
                    errorResponse("{$field} cannot be empty");
                }
                $data[$field] = null;
            } else {
                $data[$field] = sanitizeString($value);
            }
            break;

        case 'client_dob':
        case 'doi':
            $data[$field] = ($value === '' || $value === null) ? null : $value;
            break;

        case 'assigned_to':
            $data[$field] = ($value === '' || $value === null) ? null : (int)$value;
            break;

        case 'ini_completed':
            $data[$field] = $value ? 1 : 0;
            break;

        case 'status':
            $data[$field] = $value;
            break;
    }

    // Track changes for activity log
    if (isset($data[$field]) && $existingCase[$field] != $data[$field]) {
        $changes[$field] = [
            'from' => $existingCase[$field],
            'to' => $data[$field]
        ];
    }
}

if (empty($data)) {
    errorResponse('No valid fields provided for update');
}

dbUpdate('cases', $data, 'id = ?', [$caseId]);

logActivity($userId, 'update', 'case', $caseId, [
    'case_number' => $existingCase['case_number'],
    'changes' => $changes
]);

// Return the updated case
$updatedCase = dbFetchOne(
    "SELECT c.*, u.full_name AS assigned_to_name
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE c.id = ?",
    [$caseId]
);

successResponse($updatedCase, 'Case updated successfully');
