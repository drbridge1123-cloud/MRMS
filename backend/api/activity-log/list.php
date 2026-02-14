<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAdmin();

[$page, $perPage, $offset] = getPaginationParams();

$where = ['1=1'];
$params = [];

if (!empty($_GET['user_id'])) {
    $where[] = 'al.user_id = ?';
    $params[] = (int)$_GET['user_id'];
}

if (!empty($_GET['action'])) {
    $where[] = 'al.action LIKE ?';
    $params[] = '%' . sanitizeString($_GET['action']) . '%';
}

if (!empty($_GET['entity_type'])) {
    $where[] = 'al.entity_type = ?';
    $params[] = sanitizeString($_GET['entity_type']);
}

if (!empty($_GET['date_from'])) {
    $where[] = 'DATE(al.created_at) >= ?';
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[] = 'DATE(al.created_at) <= ?';
    $params[] = $_GET['date_to'];
}

$whereClause = implode(' AND ', $where);

// Sorting
$sortColumns = [
    'created_at' => 'al.created_at',
    'user_name' => 'u.full_name',
    'action' => 'al.action',
    'entity_type' => 'al.entity_type',
    'entity_id' => 'al.entity_id',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'al.created_at';
$sortDir = ($_GET['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$total = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM activity_log al WHERE {$whereClause}",
    $params
)['cnt'];

$rows = dbFetchAll(
    "SELECT al.*, u.full_name AS user_name, u.username
     FROM activity_log al
     LEFT JOIN users u ON al.user_id = u.id
     WHERE {$whereClause}
     ORDER BY {$sortBy} {$sortDir}
     LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
);

paginatedResponse($rows, $total, $page, $perPage);
