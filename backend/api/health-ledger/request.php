<?php
// POST /api/health-ledger/request - Create a request/follow-up for an item
$userId = requireAuth();
$input = getInput();

$errors = validateRequired($input, ['item_id', 'request_date', 'request_method']);
if (!empty($errors)) errorResponse(implode(', ', $errors));

$itemId = (int)$input['item_id'];
$item = dbFetchOne("SELECT * FROM health_ledger_items WHERE id = ?", [$itemId]);
if (!$item) errorResponse('Item not found', 404);

if (!validateDate($input['request_date'])) errorResponse('Invalid request_date format');

$allowedMethods = ['fax', 'email', 'portal', 'phone', 'mail'];
if (!in_array($input['request_method'], $allowedMethods)) errorResponse('Invalid request method');

$requestType = $input['request_type'] ?? 'initial';
if (!in_array($requestType, ['initial', 'follow_up', 're_request'])) {
    $requestType = 'initial';
}

$data = [
    'item_id' => $itemId,
    'request_type' => $requestType,
    'request_date' => $input['request_date'],
    'request_method' => $input['request_method'],
    'send_status' => 'draft',
    'created_by' => $userId,
];

if (isset($input['sent_to'])) $data['sent_to'] = sanitizeString($input['sent_to']);
if (isset($input['notes'])) $data['notes'] = sanitizeString($input['notes']);
if (isset($input['template_id'])) $data['template_id'] = (int)$input['template_id'];
if (isset($input['template_data'])) $data['template_data'] = json_encode($input['template_data']);

$data['next_followup_date'] = !empty($input['next_followup_date'])
    ? $input['next_followup_date']
    : calculateNextFollowup($input['request_date']);

$newId = dbInsert('hl_requests', $data);

// Update item status
$newStatus = $requestType === 'follow_up' ? 'follow_up' : 'requesting';
dbUpdate('health_ledger_items', ['overall_status' => $newStatus], 'id = ?', [$itemId]);

logActivity($userId, 'hl_request_created', 'hl_request', $newId, [
    'item_id' => $itemId,
    'type' => $requestType
]);

$record = dbFetchOne("SELECT * FROM hl_requests WHERE id = ?", [$newId]);
successResponse($record, 'Request created');
