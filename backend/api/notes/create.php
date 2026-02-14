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

$newId = dbInsert('case_notes', $data);

logActivity($userId, 'created', 'note', $newId, ['case_id' => $caseId]);

$record = dbFetchOne(
    "SELECT n.*, u.full_name AS author_name
     FROM case_notes n
     LEFT JOIN users u ON n.user_id = u.id
     WHERE n.id = ?",
    [$newId]
);

successResponse($record, 'Note created successfully');
