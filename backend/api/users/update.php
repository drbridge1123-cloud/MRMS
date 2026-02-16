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

if (array_key_exists('title', $input)) {
    $data['title'] = $input['title'] ? sanitizeString($input['title']) : null;
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

if (array_key_exists('email', $input)) {
    $data['email'] = $input['email'] ? sanitizeString($input['email']) : null;
}

if (array_key_exists('smtp_email', $input)) {
    $data['smtp_email'] = $input['smtp_email'] ? sanitizeString($input['smtp_email']) : null;
}

if (array_key_exists('smtp_app_password', $input)) {
    $data['smtp_app_password'] = $input['smtp_app_password'] ? $input['smtp_app_password'] : null;
}

if (empty($data)) {
    errorResponse('No fields to update');
}

dbUpdate('users', $data, 'id = ?', [$targetId]);

logActivity($currentUserId, 'user_updated', 'user', $targetId, $data);

$updated = dbFetchOne(
    "SELECT id, username, full_name, title, email, smtp_email, role, is_active, created_at, updated_at FROM users WHERE id = ?",
    [$targetId]
);

successResponse($updated, 'User updated successfully');
