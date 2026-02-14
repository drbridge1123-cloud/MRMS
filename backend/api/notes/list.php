<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

if (empty($_GET['case_id'])) {
    errorResponse('case_id is required');
}

$caseId = (int)$_GET['case_id'];
$conditions = ['n.case_id = ?'];
$params = [$caseId];

if (!empty($_GET['case_provider_id'])) {
    $conditions[] = 'n.case_provider_id = ?';
    $params[] = (int)$_GET['case_provider_id'];
}

$whereClause = implode(' AND ', $conditions);

$rows = dbFetchAll(
    "SELECT n.*, u.full_name AS author_name
     FROM case_notes n
     LEFT JOIN users u ON n.user_id = u.id
     WHERE {$whereClause}
     ORDER BY n.created_at DESC",
    $params
);

successResponse($rows);
