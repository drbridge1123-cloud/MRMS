<?php
// GET /api/insurance-companies - List all insurance companies with filters

$userId = requireAuth();

$where = '1=1';
$params = [];

// Filter by type
if (!empty($_GET['type'])) {
    $allowedTypes = ['auto', 'health', 'workers_comp', 'liability', 'um_uim', 'other'];
    if (validateEnum($_GET['type'], $allowedTypes)) {
        $where .= ' AND ic.type = ?';
        $params[] = $_GET['type'];
    }
}

// Search
if (!empty($_GET['search'])) {
    $s = '%' . $_GET['search'] . '%';
    $where .= ' AND (ic.name LIKE ? OR ic.phone LIKE ? OR ic.fax LIKE ? OR ic.email LIKE ?)';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

// Sorting
$sortColumns = [
    'name' => 'ic.name',
    'type' => 'ic.type',
    'phone' => 'ic.phone',
    'city' => 'ic.city',
    'state' => 'ic.state',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'ic.name';
$sortDir = ($_GET['sort_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$total = dbFetchOne("SELECT COUNT(*) as cnt FROM insurance_companies ic WHERE {$where}", $params);
$totalCount = (int)$total['cnt'];

$companies = dbFetchAll(
    "SELECT ic.*,
            (SELECT COUNT(*) FROM adjusters WHERE insurance_company_id = ic.id) AS adjuster_count
     FROM insurance_companies ic
     WHERE {$where}
     ORDER BY {$sortBy} {$sortDir}",
    $params
);

jsonResponse([
    'success' => true,
    'data' => $companies,
    'total' => $totalCount
]);
