<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAdmin();

$input = getInput();
$errors = validateRequired($input, ['username', 'password', 'full_name']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$username = sanitizeString($input['username']);
$existing = dbFetchOne("SELECT id FROM users WHERE username = ?", [$username]);
if ($existing) {
    errorResponse('Username already exists');
}

if (strlen($input['password']) < 6) {
    errorResponse('Password must be at least 6 characters');
}

$data = [
    'username' => $username,
    'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
    'full_name' => sanitizeString($input['full_name']),
    'title' => isset($input['title']) && $input['title'] ? sanitizeString($input['title']) : null,
    'role' => isset($input['role']) && validateEnum($input['role'], ['admin', 'manager', 'staff']) ? $input['role'] : 'staff',
    'is_active' => 1
];

$newId = dbInsert('users', $data);

logActivity($userId, 'user_created', 'user', $newId, [
    'username' => $data['username'],
    'role' => $data['role']
]);

$user = dbFetchOne(
    "SELECT id, username, full_name, title, role, is_active, created_at FROM users WHERE id = ?",
    [$newId]
);

successResponse($user, 'User created successfully');
