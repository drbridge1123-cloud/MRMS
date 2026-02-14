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
            DATEDIFF(CURDATE(), cp.deadline) AS days_overdue
     FROM case_providers cp
     JOIN cases c ON cp.case_id = c.id
     JOIN providers p ON cp.provider_id = p.id
     LEFT JOIN users u ON cp.assigned_to = u.id
     WHERE cp.deadline < CURDATE()
       AND cp.overall_status NOT IN ('received_complete', 'verified')
     ORDER BY cp.deadline ASC"
);

successResponse($rows);
