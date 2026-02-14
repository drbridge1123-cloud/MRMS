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
$errors = validateRequired($input, ['overall_status']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$allowedStatuses = ['not_started', 'requesting', 'follow_up', 'received_partial', 'received_complete', 'verified'];
if (!validateEnum($input['overall_status'], $allowedStatuses)) {
    errorResponse('Invalid status');
}

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

$oldStatus = $cp['overall_status'];
$newStatus = $input['overall_status'];

dbUpdate('case_providers', ['overall_status' => $newStatus], 'id = ?', [$cpId]);

if ($newStatus === 'received_complete' && $oldStatus !== 'received_complete') {
    $adminUser = dbFetchOne("SELECT id FROM users WHERE role = 'admin' AND is_active = 1 LIMIT 1");
    if ($adminUser) {
        dbInsert('notifications', [
            'user_id' => $adminUser['id'],
            'case_provider_id' => $cpId,
            'type' => 'handoff',
            'message' => "Records from {$cp['provider_name']} for case {$cp['case_number']} are complete.",
            'due_date' => date('Y-m-d')
        ]);
    }
}

logActivity($userId, 'updated_status', 'case_provider', $cpId, [
    'old_status' => $oldStatus,
    'new_status' => $newStatus
]);

$updated = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
successResponse($updated, 'Status updated successfully');
