<?php
// POST /api/cases/{id}/change-status - Forward status transition with notification
$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

$input = getInput();
$errors = validateRequired($input, ['new_status']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$newStatus = $input['new_status'];

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

$currentStatus = $case['status'];

// Valid forward transitions only
$forwardTransitions = [
    'collecting'   => ['in_review'],
    'in_review'    => ['verification', 'completed'],
    'verification' => ['completed'],
    'completed'    => ['closed'],
];

if (!isset($forwardTransitions[$currentStatus]) || !in_array($newStatus, $forwardTransitions[$currentStatus])) {
    errorResponse('Invalid status transition from "' . $currentStatus . '" to "' . $newStatus . '"');
}

// Auto-assign based on new status
$newOwner = STATUS_OWNER_MAP[$newStatus] ?? null;

$updateData = ['status' => $newStatus];
if ($newOwner) {
    $updateData['assigned_to'] = $newOwner;
}

dbUpdate('cases', $updateData, 'id = ?', [$caseId]);

$statusLabels = [
    'collecting'   => 'Collecting',
    'in_review'    => 'In Review',
    'verification' => 'Verification',
    'completed'    => 'Completed',
    'closed'       => 'Closed',
];

// Notify the new owner
if ($newOwner) {
    dbInsert('notifications', [
        'user_id' => $newOwner,
        'type' => 'status_changed',
        'message' => "Case {$case['case_number']} moved to {$statusLabels[$newStatus]} — assigned to you",
        'due_date' => date('Y-m-d')
    ]);
}

logActivity($userId, 'status_changed', 'case', $caseId, [
    'case_number' => $case['case_number'],
    'from_status' => $currentStatus,
    'to_status' => $newStatus,
    'assigned_to' => $newOwner
]);

$updatedCase = dbFetchOne(
    "SELECT c.*, u.full_name AS assigned_to_name
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE c.id = ?",
    [$caseId]
);

successResponse($updatedCase, 'Status updated successfully');
