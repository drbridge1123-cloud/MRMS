<?php
$userId = requireAuth();

$user = dbFetchOne("SELECT id, username, full_name, role, permissions, is_active, created_at FROM users WHERE id = ?", [$userId]);
if (!$user) {
    errorResponse('User not found', 404);
}

$unreadCount = dbCount('notifications', 'user_id = ? AND is_read = 0', [$userId]);
$user['unread_notifications'] = $unreadCount;
$user['permissions'] = $user['permissions'] ? json_decode($user['permissions'], true) : getDefaultPermissions($user['role']);

successResponse($user);
