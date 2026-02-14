<?php
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$cpId = (int)($_GET['id'] ?? 0);
if (!$cpId) {
    errorResponse('Case provider ID is required');
}

$input = getInput();
$errors = validateRequired($input, ['assigned_to']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$assignedTo = (int)$input['assigned_to'];

$cp = dbFetchOne(
    "SELECT cp.*, c.case_number, p.name AS provider_name
     FROM case_providers cp
     JOIN cases c ON cp.case_id = c.id
     JOIN providers p ON cp.provider_id = p.id
     WHERE cp.id = ?",
    [$cpId]
);
if (!$cp) {
    errorResponse('Case provider not found', 404);
}

$assignee = dbFetchOne("SELECT id, full_name FROM users WHERE id = ? AND is_active = 1", [$assignedTo]);
if (!$assignee) {
    errorResponse('Assigned user not found', 404);
}

dbUpdate('case_providers', ['assigned_to' => $assignedTo], 'id = ?', [$cpId]);

dbInsert('notifications', [
    'user_id' => $assignedTo,
    'case_provider_id' => $cpId,
    'type' => 'new_assignment',
    'message' => "Assigned: {$cp['provider_name']} for case {$cp['case_number']}.",
    'due_date' => date('Y-m-d')
]);

logActivity($userId, 'assigned', 'case_provider', $cpId, [
    'assigned_to' => $assignedTo,
    'assigned_to_name' => $assignee['full_name']
]);

$updated = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
successResponse($updated, 'Staff assigned successfully');
