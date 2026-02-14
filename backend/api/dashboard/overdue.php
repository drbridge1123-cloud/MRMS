<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$rows = dbFetchAll(
    "SELECT cp.id, cp.case_id, cp.deadline, cp.overall_status,
            c.case_number, c.client_name,
            p.name AS provider_name, p.type AS provider_type,
            u.full_name AS assigned_name,
            DATEDIFF(CURDATE(), cp.deadline) AS days_overdue,
            MIN(rr.request_date) AS first_request_date,
            DATEDIFF(CURDATE(), MIN(rr.request_date)) AS days_since_first_request
     FROM case_providers cp
     JOIN cases c ON cp.case_id = c.id
     JOIN providers p ON cp.provider_id = p.id
     LEFT JOIN users u ON cp.assigned_to = u.id
     LEFT JOIN record_requests rr ON rr.case_provider_id = cp.id
     WHERE cp.deadline < CURDATE()
       AND cp.overall_status NOT IN ('received_complete', 'verified')
     GROUP BY cp.id
     ORDER BY cp.deadline ASC"
);

foreach ($rows as &$row) {
    $esc = getEscalationInfo($row['days_since_first_request'] !== null ? (int)$row['days_since_first_request'] : null);
    $row['escalation_tier'] = $esc['tier'];
    $row['escalation_label'] = $esc['label'];
    $row['escalation_css'] = $esc['css'];
}
unset($row);

successResponse($rows);
