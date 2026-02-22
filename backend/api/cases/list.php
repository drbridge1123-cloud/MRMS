<?php
// GET /api/cases - List cases with filtering and pagination
$userId = requireAuth();

// Pagination
[$page, $perPage, $offset] = getPaginationParams();

// Build WHERE clauses
$where = ['1=1'];
$params = [];

// Filter: status (supports comma-separated for multi-status)
if (!empty($_GET['status'])) {
    $allowedStatuses = ['collecting','verification','completed','rfd','final_verification','disbursement','accounting','closed'];
    $statuses = array_filter(explode(',', $_GET['status']), fn($s) => in_array($s, $allowedStatuses));
    if (count($statuses) === 1) {
        $where[] = 'c.status = ?';
        $params[] = $statuses[0];
    } elseif (count($statuses) > 1) {
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $where[] = "c.status IN ({$placeholders})";
        $params = array_merge($params, $statuses);
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

// Fetch cases with assigned user name + provider progress
$sql = "SELECT c.*, u.full_name AS assigned_name,
        COALESCE(ps.provider_total, 0) AS provider_total,
        COALESCE(ps.provider_done, 0) AS provider_done,
        COALESCE(ps.provider_overdue, 0) AS provider_overdue,
        COALESCE(ps.provider_followup, 0) AS provider_followup
        FROM cases c
        LEFT JOIN users u ON c.assigned_to = u.id
        LEFT JOIN (
            SELECT cp.case_id,
                COUNT(*) AS provider_total,
                SUM(CASE WHEN cp.overall_status IN ('received_complete','verified') THEN 1 ELSE 0 END) AS provider_done,
                SUM(CASE WHEN cp.deadline < CURDATE() AND cp.overall_status NOT IN ('received_complete','verified') THEN 1 ELSE 0 END) AS provider_overdue,
                SUM(CASE WHEN cp.overall_status IN ('requesting','follow_up') AND EXISTS (
                    SELECT 1 FROM record_requests rr
                    WHERE rr.case_provider_id = cp.id
                    AND rr.next_followup_date <= CURDATE()
                    ORDER BY rr.id DESC LIMIT 1
                ) THEN 1 ELSE 0 END) AS provider_followup
            FROM case_providers cp
            GROUP BY cp.case_id
        ) ps ON ps.case_id = c.id
        WHERE {$whereClause}
        ORDER BY {$sortBy} {$sortDir}
        LIMIT ? OFFSET ?";

$queryParams = array_merge($params, [$perPage, $offset]);
$cases = dbFetchAll($sql, $queryParams);

foreach ($cases as &$case) {
    $case['provider_total'] = (int)$case['provider_total'];
    $case['provider_done'] = (int)$case['provider_done'];
    $case['provider_overdue'] = (int)$case['provider_overdue'];
    $case['provider_followup'] = (int)$case['provider_followup'];
}
unset($case);

// Summary counts (always unfiltered)
$summary = dbFetchOne("
    SELECT
        COUNT(*) as total,
        COUNT(CASE WHEN status != 'closed' THEN 1 END) as active,
        COUNT(CASE WHEN status = 'collecting' THEN 1 END) as collecting,
        COUNT(CASE WHEN status = 'verification' THEN 1 END) as verification,
        COUNT(CASE WHEN status IN ('completed','rfd') THEN 1 END) as attorney,
        COUNT(CASE WHEN status IN ('final_verification','accounting') THEN 1 END) as closing,
        COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed
    FROM cases
");

$providerStats = dbFetchOne("
    SELECT
        COUNT(CASE WHEN cp.deadline < CURDATE() AND cp.overall_status NOT IN ('received_complete','verified') THEN 1 END) as overdue_providers,
        COUNT(CASE WHEN cp.overall_status IN ('not_started') THEN 1 END) as not_started_providers
    FROM case_providers cp
    JOIN cases c ON cp.case_id = c.id
    WHERE c.status NOT IN ('closed')
");

$extra = [
    'summary' => [
        'total' => (int)$summary['total'],
        'active' => (int)$summary['active'],
        'collecting' => (int)$summary['collecting'],
        'verification' => (int)$summary['verification'],
        'attorney' => (int)$summary['attorney'],
        'closing' => (int)$summary['closing'],
        'closed' => (int)$summary['closed'],
        'overdue_providers' => (int)($providerStats['overdue_providers'] ?? 0),
        'not_started_providers' => (int)($providerStats['not_started_providers'] ?? 0),
    ]
];

paginatedResponse($cases, $total, $page, $perPage, $extra);
