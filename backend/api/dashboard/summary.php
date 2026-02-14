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

// Escalation counts
$escRows = dbFetchAll("
    SELECT cp.id,
           DATEDIFF(CURDATE(), MIN(rr.request_date)) AS days_since
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id
    LEFT JOIN record_requests rr ON rr.case_provider_id = cp.id
    WHERE cp.overall_status NOT IN ('received_complete', 'verified')
      AND c.status = 'active'
    GROUP BY cp.id
    HAVING MIN(rr.request_date) IS NOT NULL
");

$escCounts = ['action_needed' => 0, 'manager' => 0, 'admin' => 0];
foreach ($escRows as $er) {
    $tier = getEscalationTier((int)$er['days_since']);
    if ($tier === 'action_needed') $escCounts['action_needed']++;
    elseif ($tier === 'manager') { $escCounts['action_needed']++; $escCounts['manager']++; }
    elseif ($tier === 'admin') { $escCounts['action_needed']++; $escCounts['manager']++; $escCounts['admin']++; }
}

successResponse([
    'active_cases' => $activeCases,
    'requesting_count' => $requestingCount,
    'followup_due' => (int)($followupDue['cnt'] ?? 0),
    'overdue_count' => $overdueCount,
    'escalation_action_needed' => $escCounts['action_needed'],
    'escalation_manager' => $escCounts['manager'],
    'escalation_admin' => $escCounts['admin']
]);
