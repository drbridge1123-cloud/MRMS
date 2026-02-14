<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$conditions = ['user_id = ?'];
$params = [$userId];

if (isset($_GET['unread_only']) && ($_GET['unread_only'] === '1' || $_GET['unread_only'] === 'true')) {
    $conditions[] = 'is_read = 0';
}

$whereClause = implode(' AND ', $conditions);

$rows = dbFetchAll(
    "SELECT * FROM notifications WHERE {$whereClause} ORDER BY created_at DESC",
    $params
);

successResponse($rows);
