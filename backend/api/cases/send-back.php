<?php
// POST /api/cases/{id}/send-back - Send case back to a previous status
$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

$input = getInput();

// Validate required fields
$errors = validateRequired($input, ['target_status', 'reason']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$targetStatus = $input['target_status'];
$reason = sanitizeString($input['reason']);

// Get current case
$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

$currentStatus = $case['status'];

// Validate allowed rollback paths
$allowedPaths = [
    'verification'        => ['collecting'],
    'completed'           => ['collecting', 'verification'],
    'rfd'                 => ['collecting', 'verification', 'completed'],
    'final_verification'  => ['collecting', 'verification', 'completed', 'rfd'],
    'disbursement'        => ['collecting', 'verification', 'completed', 'rfd', 'final_verification'],
    'accounting'          => ['collecting', 'verification', 'completed', 'rfd', 'final_verification', 'disbursement'],
    'closed'              => ['collecting', 'verification', 'completed', 'rfd', 'final_verification', 'disbursement', 'accounting'],
];

if (!isset($allowedPaths[$currentStatus]) || !in_array($targetStatus, $allowedPaths[$currentStatus])) {
    errorResponse('Invalid status transition. Cannot send back from "' . $currentStatus . '" to "' . $targetStatus . '"');
}

// Auto-assign based on target status
$newOwner = STATUS_OWNER_MAP[$targetStatus] ?? null;

$updateData = ['status' => $targetStatus];
if ($newOwner) {
    $updateData['assigned_to'] = $newOwner;
}

dbUpdate('cases', $updateData, 'id = ?', [$caseId]);

$notifyUserId = $newOwner;

$statusLabels = [
    'collecting'          => 'Collection',
    'verification'        => 'Verification',
    'completed'           => 'Completed',
    'rfd'                 => 'Attorney',
    'final_verification'  => 'Final Verification',
    'disbursement'        => 'Disbursement',
    'accounting'          => 'Accounting',
    'closed'              => 'Closed',
];
$targetLabel = $statusLabels[$targetStatus] ?? $targetStatus;

// Create notification for the assignee
if ($notifyUserId) {
    dbInsert('notifications', [
        'user_id' => $notifyUserId,
        'type' => 'case_sent_back',
        'message' => "Case {$case['case_number']} sent back to {$targetLabel} — assigned to you. Reason: {$reason}",
        'due_date' => date('Y-m-d')
    ]);
}

// Log activity
logActivity($userId, 'sent_back', 'case', $caseId, [
    'case_number' => $case['case_number'],
    'from_status' => $currentStatus,
    'to_status' => $targetStatus,
    'reason' => $reason,
    'assigned_to' => $newOwner
]);

// Return updated case
$updatedCase = dbFetchOne(
    "SELECT c.*, u.full_name AS assigned_to_name
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE c.id = ?",
    [$caseId]
);

successResponse($updatedCase, 'Case sent back successfully');
