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

$allowedStatuses = ['not_started', 'requesting', 'follow_up', 'action_needed', 'received_partial', 'on_hold', 'received_complete', 'verified'];
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

$statusUpdate = ['overall_status' => $newStatus];
if (in_array($newStatus, ['received_complete', 'received_partial']) && !$cp['received_date']) {
    $statusUpdate['received_date'] = date('Y-m-d');
}
dbUpdate('case_providers', $statusUpdate, 'id = ?', [$cpId]);

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

// Auto-move case to In Review if all providers are received_complete
if ($newStatus === 'received_complete') {
    $incomplete = dbFetchOne(
        "SELECT COUNT(*) as cnt FROM case_providers WHERE case_id = ? AND overall_status != 'received_complete' AND overall_status != 'verified'",
        [$cp['case_id']]
    );
    if ($incomplete && (int)$incomplete['cnt'] === 0) {
        $newOwner = STATUS_OWNER_MAP['in_review'] ?? null;
        $updateData = ['status' => 'in_review'];
        if ($newOwner) {
            $updateData['assigned_to'] = $newOwner;
        }
        dbUpdate('cases', $updateData, 'id = ?', [$cp['case_id']]);

        // Notify the new owner (Ella)
        if ($newOwner) {
            dbInsert('notifications', [
                'user_id' => $newOwner,
                'type' => 'status_changed',
                'message' => "Case {$cp['case_number']} auto-moved to In Review — all records complete. Assigned to you.",
                'due_date' => date('Y-m-d')
            ]);
        }
    }
}

$updated = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
successResponse($updated, 'Status updated successfully');
