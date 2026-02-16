<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

// If only requesting active users (for dropdowns), allow all authenticated users
// Otherwise require admin access for full user management
if (isset($_GET['active_only']) && $_GET['active_only'] == '1') {
    requireAuth();
} else {
    requireAdmin();
}

[$page, $perPage, $offset] = getPaginationParams();

$where = ['1=1'];
$params = [];

if (!empty($_GET['search'])) {
    $term = '%' . sanitizeString($_GET['search']) . '%';
    $where[] = '(username LIKE ? OR full_name LIKE ?)';
    $params[] = $term;
    $params[] = $term;
}

if (isset($_GET['role']) && $_GET['role'] !== '') {
    $where[] = 'role = ?';
    $params[] = $_GET['role'];
}

if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
    $where[] = 'is_active = ?';
    $params[] = (int)$_GET['is_active'];
}

$whereClause = implode(' AND ', $where);

// Sorting
$sortColumns = [
    'id' => 'id',
    'username' => 'username',
    'full_name' => 'full_name',
    'role' => 'role',
    'is_active' => 'is_active',
    'created_at' => 'created_at',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'id';
$sortDir = ($_GET['sort_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$total = dbFetchOne("SELECT COUNT(*) as cnt FROM users WHERE {$whereClause}", $params)['cnt'];

// Select only basic fields for dropdowns (active_only), full fields for admin
$selectFields = (isset($_GET['active_only']) && $_GET['active_only'] == '1')
    ? "id, username, full_name, title, role"
    : "id, username, full_name, title, email, smtp_email, role, is_active, created_at, updated_at";

$users = dbFetchAll(
    "SELECT {$selectFields}
     FROM users WHERE {$whereClause} ORDER BY {$sortBy} {$sortDir} LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
);

paginatedResponse($users, $total, $page, $perPage);
