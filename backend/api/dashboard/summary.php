<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$activeCases = dbCount('cases', "status = 'active'");

$requestingCount = dbCount(
    'case_providers',
    "overall_status IN ('requesting', 'follow_up')"
);

$followupDue = dbFetchOne(
    "SELECT COUNT(DISTINCT cp.id) AS cnt
     FROM case_providers cp
     INNER JOIN record_requests r ON r.case_provider_id = cp.id
     WHERE cp.overall_status IN ('requesting', 'follow_up')
       AND r.next_followup_date <= CURDATE()
       AND r.id = (
           SELECT r2.id FROM record_requests r2
           WHERE r2.case_provider_id = cp.id
           ORDER BY r2.request_date DESC, r2.created_at DESC
           LIMIT 1
       )"
);

$overdueCount = dbCount(
    'case_providers',
    "deadline < CURDATE() AND overall_status NOT IN ('received_complete', 'verified')"
);

successResponse([
    'active_cases' => $activeCases,
    'requesting_count' => $requestingCount,
    'followup_due' => (int)($followupDue['cnt'] ?? 0),
    'overdue_count' => $overdueCount
]);
