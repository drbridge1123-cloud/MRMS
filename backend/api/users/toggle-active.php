<?php
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$currentUserId = requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);
if (!$targetId) {
    errorResponse('User ID is required');
}

if ($targetId === $currentUserId) {
    errorResponse('Cannot deactivate your own account');
}

$user = dbFetchOne("SELECT id, username, full_name, is_active FROM users WHERE id = ?", [$targetId]);
if (!$user) {
    errorResponse('User not found', 404);
}

$newStatus = $user['is_active'] ? 0 : 1;
dbUpdate('users', ['is_active' => $newStatus], 'id = ?', [$targetId]);

$action = $newStatus ? 'user_activated' : 'user_deactivated';
logActivity($currentUserId, $action, 'user', $targetId, [
    'username' => $user['username']
]);

$updated = dbFetchOne(
    "SELECT id, username, full_name, role, is_active, created_at, updated_at FROM users WHERE id = ?",
    [$targetId]
);

$msg = $newStatus ? 'User activated' : 'User deactivated';
successResponse($updated, $msg);
