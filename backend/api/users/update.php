<?php
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$currentUserId = requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);
if (!$targetId) {
    errorResponse('User ID is required');
}

$user = dbFetchOne("SELECT * FROM users WHERE id = ?", [$targetId]);
if (!$user) {
    errorResponse('User not found', 404);
}

$input = getInput();
$data = [];

if (isset($input['full_name']) && trim($input['full_name']) !== '') {
    $data['full_name'] = sanitizeString($input['full_name']);
}

if (isset($input['username']) && trim($input['username']) !== '') {
    $newUsername = sanitizeString($input['username']);
    if ($newUsername !== $user['username']) {
        $existing = dbFetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$newUsername, $targetId]);
        if ($existing) {
            errorResponse('Username already taken');
        }
        $data['username'] = $newUsername;
    }
}

if (isset($input['role']) && validateEnum($input['role'], ['admin', 'manager', 'staff'])) {
    $data['role'] = $input['role'];
}

if (empty($data)) {
    errorResponse('No fields to update');
}

dbUpdate('users', $data, 'id = ?', [$targetId]);

logActivity($currentUserId, 'user_updated', 'user', $targetId, $data);

$updated = dbFetchOne(
    "SELECT id, username, full_name, role, is_active, created_at, updated_at FROM users WHERE id = ?",
    [$targetId]
);

successResponse($updated, 'User updated successfully');
