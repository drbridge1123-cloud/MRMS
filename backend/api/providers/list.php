<?php
// GET /api/providers - List all providers with pagination and filters

$userId = requireAuth();

[$page, $perPage, $offset] = getPaginationParams();

// Build filter conditions
$where = '1=1';
$params = [];

// Filter by type
if (!empty($_GET['type'])) {
    $allowedTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'other'];
    if (validateEnum($_GET['type'], $allowedTypes)) {
        $where .= ' AND p.type = ?';
        $params[] = $_GET['type'];
    }
}

// Filter by difficulty_level
if (!empty($_GET['difficulty_level'])) {
    $allowedLevels = ['easy', 'medium', 'hard'];
    if (validateEnum($_GET['difficulty_level'], $allowedLevels)) {
        $where .= ' AND p.difficulty_level = ?';
        $params[] = $_GET['difficulty_level'];
    }
}

// Search by name
if (!empty($_GET['search'])) {
    $where .= ' AND p.name LIKE ?';
    $params[] = '%' . $_GET['search'] . '%';
}

// Get total count
$total = dbFetchOne("SELECT COUNT(*) as cnt FROM providers p WHERE {$where}", $params);
$totalCount = (int)$total['cnt'];

// Fetch paginated results
$countParams = $params;
$params[] = $perPage;
$params[] = $offset;

$providers = dbFetchAll(
    "SELECT p.id, p.name, p.type, p.address, p.phone, p.fax, p.email,
            p.portal_url, p.preferred_method, p.uses_third_party,
            p.third_party_name, p.difficulty_level, p.avg_response_days,
            p.created_at, p.updated_at
     FROM providers p
     WHERE {$where}
     ORDER BY p.name ASC
     LIMIT ? OFFSET ?",
    $params
);

paginatedResponse($providers, $totalCount, $page, $perPage);
