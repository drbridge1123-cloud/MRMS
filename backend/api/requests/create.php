<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$input = getInput();
$errors = validateRequired($input, ['case_provider_id', 'request_date', 'request_method']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$cpId = (int)$input['case_provider_id'];

$cp = dbFetchOne("SELECT id FROM case_providers WHERE id = ?", [$cpId]);
if (!$cp) {
    errorResponse('Case provider not found', 404);
}

if (!validateDate($input['request_date'])) {
    errorResponse('Invalid request_date format');
}

$data = [
    'case_provider_id' => $cpId,
    'request_date' => $input['request_date'],
    'request_method' => sanitizeString($input['request_method']),
    'request_type' => !empty($input['request_type']) ? sanitizeString($input['request_type']) : 'initial',
    'requested_by' => $userId
];

if (isset($input['sent_to'])) $data['sent_to'] = sanitizeString($input['sent_to']);
if (isset($input['authorization_sent'])) $data['authorization_sent'] = $input['authorization_sent'] ? 1 : 0;
if (isset($input['notes'])) $data['notes'] = sanitizeString($input['notes']);

$data['next_followup_date'] = !empty($input['next_followup_date'])
    ? $input['next_followup_date']
    : calculateNextFollowup($input['request_date']);

$newId = dbInsert('record_requests', $data);

dbUpdate('case_providers', ['overall_status' => 'requesting'], 'id = ?', [$cpId]);

logActivity($userId, 'request_sent', 'record_request', $newId, ['case_provider_id' => $cpId]);

$record = dbFetchOne("SELECT * FROM record_requests WHERE id = ?", [$newId]);
successResponse($record, 'Request created successfully');
