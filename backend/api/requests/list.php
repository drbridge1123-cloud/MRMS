<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

if (empty($_GET['case_provider_id'])) {
    errorResponse('case_provider_id is required');
}

$cpId = (int)$_GET['case_provider_id'];

$rows = dbFetchAll(
    "SELECT r.*, u.full_name AS requested_by_name
     FROM record_requests r
     LEFT JOIN users u ON r.requested_by = u.id
     WHERE r.case_provider_id = ?
     ORDER BY r.request_date DESC, r.created_at DESC",
    [$cpId]
);

successResponse($rows);
