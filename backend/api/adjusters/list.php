<?php
// GET /api/adjusters - List all adjusters with filters

$userId = requireAuth();

$where = '1=1';
$params = [];

// Filter by insurance company
if (!empty($_GET['insurance_company_id'])) {
    $where .= ' AND a.insurance_company_id = ?';
    $params[] = (int)$_GET['insurance_company_id'];
}

// Filter by adjuster type
if (!empty($_GET['adjuster_type'])) {
    $where .= ' AND a.adjuster_type = ?';
    $params[] = $_GET['adjuster_type'];
}

// Filter by active status
if (isset($_GET['is_active']) && $_GET['is_active'] !== '') {
    $where .= ' AND a.is_active = ?';
    $params[] = (int)$_GET['is_active'];
}

// Search
if (!empty($_GET['search'])) {
    $s = '%' . $_GET['search'] . '%';
    $where .= ' AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ? OR ic.name LIKE ? OR CONCAT(a.first_name, " ", a.last_name) LIKE ?)';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

// Sorting
$sortColumns = [
    'last_name' => 'a.last_name',
    'first_name' => 'a.first_name',
    'insurance_company_name' => 'ic.name',
    'email' => 'a.email',
    'title' => 'a.title',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'a.last_name';
$sortDir = ($_GET['sort_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$total = dbFetchOne(
    "SELECT COUNT(*) as cnt
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON a.insurance_company_id = ic.id
     WHERE {$where}",
    $params
);
$totalCount = (int)$total['cnt'];

$adjusters = dbFetchAll(
    "SELECT a.*, ic.name AS insurance_company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON a.insurance_company_id = ic.id
     WHERE {$where}
     ORDER BY {$sortBy} {$sortDir}",
    $params
);

jsonResponse([
    'success' => true,
    'data' => $adjusters,
    'total' => $totalCount
]);
