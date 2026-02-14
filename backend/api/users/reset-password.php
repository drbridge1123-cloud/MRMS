<?php
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$currentUserId = requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);
if (!$targetId) {
    errorResponse('User ID is required');
}

$user = dbFetchOne("SELECT id, username FROM users WHERE id = ?", [$targetId]);
if (!$user) {
    errorResponse('User not found', 404);
}

$input = getInput();
$errors = validateRequired($input, ['new_password']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

if (strlen($input['new_password']) < 6) {
    errorResponse('Password must be at least 6 characters');
}

$hash = password_hash($input['new_password'], PASSWORD_DEFAULT);
dbUpdate('users', ['password_hash' => $hash], 'id = ?', [$targetId]);

logActivity($currentUserId, 'password_reset', 'user', $targetId, [
    'username' => $user['username']
]);

successResponse(null, 'Password reset successfully');
