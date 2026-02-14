<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$cpId = (int)($_GET['id'] ?? 0);
if (!$cpId) {
    errorResponse('Case provider ID is required');
}

$rows = dbFetchAll(
    "SELECT dc.*, u.full_name AS changed_by_name
     FROM deadline_changes dc
     JOIN users u ON dc.changed_by = u.id
     WHERE dc.case_provider_id = ?
     ORDER BY dc.created_at DESC",
    [$cpId]
);

successResponse($rows);
