<?php
// GET /api/mbds - List all MBDS reports
requireAuth();

$where = ['1=1'];
$params = [];

// Search
if (!empty($_GET['search'])) {
    $term = '%' . sanitizeString($_GET['search']) . '%';
    $where[] = '(c.case_number LIKE ? OR c.client_name LIKE ?)';
    $params[] = $term;
    $params[] = $term;
}

// Status filter
if (!empty($_GET['status'])) {
    $allowed = ['draft', 'completed', 'approved'];
    if (in_array($_GET['status'], $allowed)) {
        $where[] = 'r.status = ?';
        $params[] = $_GET['status'];
    }
}

$whereClause = implode(' AND ', $where);

// Sorting
$sortColumns = [
    'case_number' => 'c.case_number',
    'client_name' => 'c.client_name',
    'status' => 'r.status',
    'updated_at' => 'r.updated_at',
    'created_at' => 'r.created_at',
    'total_charges' => 'total_charges',
    'total_balance' => 'total_balance',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'r.updated_at';
$sortDir = ($_GET['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$sql = "SELECT r.id, r.case_id, r.status, r.created_at, r.updated_at,
               r.pip1_name, r.pip2_name, r.health1_name, r.health2_name,
               r.completed_at, r.approved_at,
               c.case_number, c.client_name, c.doi, c.status AS case_status,
               u1.full_name AS completed_by_name,
               u2.full_name AS approved_by_name,
               COALESCE(ls.total_charges, 0) AS total_charges,
               COALESCE(ls.total_balance, 0) AS total_balance,
               COALESCE(ls.line_count, 0) AS line_count
        FROM mbds_reports r
        JOIN cases c ON c.id = r.case_id
        LEFT JOIN users u1 ON r.completed_by = u1.id
        LEFT JOIN users u2 ON r.approved_by = u2.id
        LEFT JOIN (
            SELECT report_id,
                   SUM(charges) AS total_charges,
                   SUM(balance) AS total_balance,
                   COUNT(*) AS line_count
            FROM mbds_lines
            GROUP BY report_id
        ) ls ON ls.report_id = r.id
        WHERE {$whereClause}
        ORDER BY {$sortBy} {$sortDir}";

$rows = dbFetchAll($sql, $params);

foreach ($rows as &$row) {
    $row['total_charges'] = (float)$row['total_charges'];
    $row['total_balance'] = (float)$row['total_balance'];
    $row['line_count'] = (int)$row['line_count'];
}
unset($row);

// Summary
$summary = dbFetchOne("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN r.status = 'draft' THEN 1 ELSE 0 END) AS draft_count,
        SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
        SUM(CASE WHEN r.status = 'approved' THEN 1 ELSE 0 END) AS approved_count
    FROM mbds_reports r
", []);

jsonResponse([
    'success' => true,
    'data' => $rows,
    'summary' => [
        'total' => (int)$summary['total'],
        'draft' => (int)$summary['draft_count'],
        'completed' => (int)$summary['completed_count'],
        'approved' => (int)$summary['approved_count'],
    ]
]);
