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

$cpId = (int) $input['case_provider_id'];

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
    'request_type' => 'follow_up',
    'requested_by' => $userId,
    'next_followup_date' => !empty($input['next_followup_date'])
        ? $input['next_followup_date']
        : calculateNextFollowup($input['request_date'])
];

if (isset($input['sent_to']))
    $data['sent_to'] = sanitizeString($input['sent_to']);
if (isset($input['authorization_sent']))
    $data['authorization_sent'] = $input['authorization_sent'] ? 1 : 0;
if (isset($input['notes']))
    $data['notes'] = sanitizeString($input['notes']);
if (isset($input['template_id']))
    $data['template_id'] = (int)$input['template_id'];
$data['send_status'] = 'draft';

$newId = dbInsert('record_requests', $data);

dbUpdate('case_providers', ['overall_status' => 'follow_up'], 'id = ?', [$cpId]);

logActivity($userId, 'followup_sent', 'record_request', $newId, ['case_provider_id' => $cpId]);

$record = dbFetchOne("SELECT * FROM record_requests WHERE id = ?", [$newId]);
successResponse($record, 'Follow-up request created successfully');
