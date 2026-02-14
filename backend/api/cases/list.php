<?php
// GET /api/cases - List cases with filtering and pagination
$userId = requireAuth();

// Pagination
[$page, $perPage, $offset] = getPaginationParams();

// Build WHERE clauses
$where = ['1=1'];
$params = [];

// Filter: status
if (!empty($_GET['status'])) {
    $allowedStatuses = ['active', 'pending_review', 'completed', 'on_hold'];
    if (validateEnum($_GET['status'], $allowedStatuses)) {
        $where[] = 'c.status = ?';
        $params[] = $_GET['status'];
    }
}

// Filter: assigned_to
if (!empty($_GET['assigned_to'])) {
    $where[] = 'c.assigned_to = ?';
    $params[] = (int)$_GET['assigned_to'];
}

// Filter: search (client_name or case_number)
if (!empty($_GET['search'])) {
    $searchTerm = '%' . sanitizeString($_GET['search']) . '%';
    $where[] = '(c.client_name LIKE ? OR c.case_number LIKE ?)';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = implode(' AND ', $where);

// Sorting
$sortColumns = [
    'case_number' => 'c.case_number',
    'client_name' => 'c.client_name',
    'client_dob' => 'c.client_dob',
    'doi' => 'c.doi',
    'attorney_name' => 'c.attorney_name',
    'assigned_name' => 'u.full_name',
    'status' => 'c.status',
    'created_at' => 'c.created_at',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'c.updated_at';
$sortDir = ($_GET['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

// Get total count
$countSql = "SELECT COUNT(*) as cnt FROM cases c WHERE {$whereClause}";
$countResult = dbFetchOne($countSql, $params);
$total = (int)$countResult['cnt'];

// Fetch cases with assigned user name
$sql = "SELECT c.*, u.full_name AS assigned_name
        FROM cases c
        LEFT JOIN users u ON c.assigned_to = u.id
        WHERE {$whereClause}
        ORDER BY {$sortBy} {$sortDir}
        LIMIT ? OFFSET ?";

$queryParams = array_merge($params, [$perPage, $offset]);
$cases = dbFetchAll($sql, $queryParams);

paginatedResponse($cases, $total, $page, $perPage);
