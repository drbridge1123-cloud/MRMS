<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$input = getInput();
$errors = validateRequired($input, ['case_provider_id', 'received_date', 'received_method']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$cpId = (int)$input['case_provider_id'];

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

if (!validateDate($input['received_date'])) {
    errorResponse('Invalid received_date format');
}

$isComplete = !empty($input['is_complete']) ? 1 : 0;

$data = [
    'case_provider_id' => $cpId,
    'received_date' => $input['received_date'],
    'received_method' => sanitizeString($input['received_method']),
    'received_by' => $userId,
    'is_complete' => $isComplete
];

foreach (['has_medical_records', 'has_billing', 'has_chart', 'has_imaging', 'has_op_report'] as $field) {
    if (isset($input[$field])) $data[$field] = $input[$field] ? 1 : 0;
}

if (isset($input['incomplete_reason'])) $data['incomplete_reason'] = sanitizeString($input['incomplete_reason']);
if (isset($input['file_location'])) $data['file_location'] = sanitizeString($input['file_location']);
if (isset($input['notes'])) $data['notes'] = sanitizeString($input['notes']);

$newId = dbInsert('record_receipts', $data);

$newStatus = $isComplete ? 'received_complete' : 'received_partial';
$statusUpdate = ['overall_status' => $newStatus];
if (!$cp['received_date']) {
    $statusUpdate['received_date'] = $input['received_date'];
}
dbUpdate('case_providers', $statusUpdate, 'id = ?', [$cpId]);

if ($isComplete) {
    $adminUser = dbFetchOne("SELECT id FROM users WHERE role = 'admin' AND is_active = 1 LIMIT 1");
    if ($adminUser) {
        dbInsert('notifications', [
            'user_id' => $adminUser['id'],
            'case_provider_id' => $cpId,
            'type' => 'handoff',
            'message' => "Records received from {$cp['provider_name']} for case {$cp['case_number']}.",
            'due_date' => date('Y-m-d')
        ]);
    }
}

logActivity($userId, 'record_received', 'record_receipt', $newId, ['case_provider_id' => $cpId]);

$record = dbFetchOne("SELECT * FROM record_receipts WHERE id = ?", [$newId]);
successResponse($record, 'Receipt logged successfully');
