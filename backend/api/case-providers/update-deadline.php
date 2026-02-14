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
$errors = validateRequired($input, ['deadline', 'reason']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

if (!validateDate($input['deadline'])) {
    errorResponse('Invalid deadline format (YYYY-MM-DD)');
}

$reason = sanitizeString($input['reason']);
if (strlen($reason) < 5) {
    errorResponse('Reason must be at least 5 characters');
}

$cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
if (!$cp) {
    errorResponse('Case provider not found', 404);
}

$oldDeadline = $cp['deadline'];
$newDeadline = $input['deadline'];

dbInsert('deadline_changes', [
    'case_provider_id' => $cpId,
    'old_deadline' => $oldDeadline,
    'new_deadline' => $newDeadline,
    'reason' => $reason,
    'changed_by' => $userId
]);

dbUpdate('case_providers', ['deadline' => $newDeadline], 'id = ?', [$cpId]);

logActivity($userId, 'changed_deadline', 'case_provider', $cpId, [
    'old_deadline' => $oldDeadline,
    'new_deadline' => $newDeadline,
    'reason' => $reason
]);

successResponse(null, 'Deadline updated successfully');
