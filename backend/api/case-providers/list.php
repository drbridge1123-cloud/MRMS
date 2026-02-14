<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

if (empty($_GET['case_id'])) {
    errorResponse('case_id is required');
}

$caseId = (int)$_GET['case_id'];

$sql = "SELECT cp.*,
            p.name AS provider_name,
            p.type AS provider_type,
            p.phone AS provider_phone,
            p.fax AS provider_fax,
            u.full_name AS assigned_name,
            (SELECT MIN(r.request_date) FROM record_requests r WHERE r.case_provider_id = cp.id) AS first_request_date,
            (SELECT MAX(r.request_date) FROM record_requests r WHERE r.case_provider_id = cp.id) AS last_request_date,
            (SELECT COUNT(*) FROM record_requests r WHERE r.case_provider_id = cp.id) AS request_count
        FROM case_providers cp
        JOIN providers p ON cp.provider_id = p.id
        LEFT JOIN users u ON cp.assigned_to = u.id
        WHERE cp.case_id = ?
        ORDER BY cp.created_at ASC";

$rows = dbFetchAll($sql, [$caseId]);

foreach ($rows as &$row) {
    $row['days_since_request'] = $row['last_request_date'] ? daysElapsed($row['last_request_date']) : null;
    $row['is_overdue'] = isOverdue($row['deadline']);
    $row['days_until_deadline'] = daysUntil($row['deadline']);
}
unset($row);

successResponse($rows);
