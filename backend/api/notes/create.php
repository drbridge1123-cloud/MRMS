<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$input = getInput();
$errors = validateRequired($input, ['case_id', 'content']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$caseId = (int)$input['case_id'];

$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

$data = [
    'case_id' => $caseId,
    'user_id' => $userId,
    'content' => sanitizeString($input['content']),
    'note_type' => !empty($input['note_type']) ? sanitizeString($input['note_type']) : 'general'
];

if (!empty($input['case_provider_id'])) {
    $data['case_provider_id'] = (int)$input['case_provider_id'];
}

if (!empty($input['contact_method'])) {
    $allowedMethods = ['phone','fax','email','portal','mail','in_person','other'];
    if (validateEnum($input['contact_method'], $allowedMethods)) {
        $data['contact_method'] = $input['contact_method'];
    }
}

if (!empty($input['contact_date'])) {
    $data['contact_date'] = sanitizeString($input['contact_date']);
}

$newId = dbInsert('case_notes', $data);

logActivity($userId, 'created', 'note', $newId, ['case_id' => $caseId]);

$record = dbFetchOne(
    "SELECT n.*, u.full_name AS author_name,
            p.name AS provider_name
     FROM case_notes n
     LEFT JOIN users u ON n.user_id = u.id
     LEFT JOIN case_providers cp ON n.case_provider_id = cp.id
     LEFT JOIN providers p ON cp.provider_id = p.id
     WHERE n.id = ?",
    [$newId]
);

successResponse($record, 'Note created successfully');
