<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$activeCases = dbCount('cases', "status NOT IN ('closed')");

$requestingCount = dbCount(
    'case_providers',
    "overall_status IN ('requesting', 'follow_up', 'action_needed')"
);

$followupDue = dbFetchOne(
    "SELECT COUNT(DISTINCT cp.id) AS cnt
     FROM case_providers cp
     INNER JOIN record_requests r ON r.case_provider_id = cp.id
     WHERE cp.overall_status IN ('requesting', 'follow_up', 'action_needed')
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

// Escalation counts (deadline-based)
$escRows = dbFetchAll("
    SELECT cp.id, DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id
    WHERE cp.overall_status NOT IN ('received_complete', 'verified')
      AND c.status NOT IN ('closed')
      AND cp.deadline IS NOT NULL AND cp.deadline <= CURDATE()
");

$escCounts = ['action_needed' => 0, 'admin' => 0];
foreach ($escRows as $er) {
    $daysPast = (int)$er['days_past_deadline'];
    $escCounts['action_needed']++;
    if ($daysPast >= ADMIN_ESCALATION_DAYS_AFTER_DEADLINE) $escCounts['admin']++;
}

successResponse([
    'active_cases' => $activeCases,
    'requesting_count' => $requestingCount,
    'followup_due' => (int)($followupDue['cnt'] ?? 0),
    'overdue_count' => $overdueCount,
    'escalation_action_needed' => $escCounts['action_needed'],
    'escalation_admin' => $escCounts['admin']
]);
