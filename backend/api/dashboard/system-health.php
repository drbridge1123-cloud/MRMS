<?php
// GET /api/dashboard/system-health - System health metrics
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

// Communication success rates (email/fax)
$commStats = dbFetchAll("
    SELECT
        send_method,
        COUNT(*) as total_sends,
        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
        ROUND(SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as success_rate
    FROM send_log
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAYS)
    GROUP BY send_method
");

$communication = [
    'email' => ['total' => 0, 'success' => 0, 'rate' => 0],
    'fax' => ['total' => 0, 'success' => 0, 'rate' => 0]
];

foreach ($commStats as $stat) {
    $method = $stat['send_method'];
    $communication[$method] = [
        'total' => (int)$stat['total_sends'],
        'success' => (int)$stat['success_count'],
        'rate' => (float)$stat['success_rate']
    ];
}

// Overall communication success rate
$totalSends = $communication['email']['total'] + $communication['fax']['total'];
$totalSuccess = $communication['email']['success'] + $communication['fax']['success'];
$overallRate = $totalSends > 0 ? round(($totalSuccess / $totalSends) * 100, 1) : 0;

// Treatment status distribution (active cases only)
$treatmentStats = dbFetchAll("
    SELECT
        treatment_status,
        COUNT(*) as count
    FROM cases
    WHERE status = 'active'
    GROUP BY treatment_status
");

$treatment = [
    'in_treatment' => 0,
    'treatment_done' => 0,
    'neg' => 0,
    'rfd' => 0,
    'not_set' => 0
];

foreach ($treatmentStats as $stat) {
    $status = $stat['treatment_status'];
    if ($status === null) {
        $treatment['not_set'] = (int)$stat['count'];
    } else {
        $treatment[$status] = (int)$stat['count'];
    }
}

// Cases on hold
$onHoldData = dbFetchOne("
    SELECT
        COUNT(DISTINCT cp.id) as total_on_hold,
        COUNT(DISTINCT c.id) as cases_affected
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id
    WHERE cp.is_on_hold = 1
");

$onHold = [
    'total_providers' => (int)$onHoldData['total_on_hold'],
    'cases_affected' => (int)$onHoldData['cases_affected']
];

// Top hold reasons
$holdReasons = dbFetchAll("
    SELECT
        hold_reason,
        COUNT(*) as count
    FROM case_providers
    WHERE is_on_hold = 1 AND hold_reason IS NOT NULL
    GROUP BY hold_reason
    ORDER BY count DESC
    LIMIT 5
");

foreach ($holdReasons as &$reason) {
    $reason['count'] = (int)$reason['count'];
}
unset($reason);

// Health ledger status breakdown
$hlStats = dbFetchAll("
    SELECT
        overall_status,
        COUNT(*) as count
    FROM health_ledger_items
    GROUP BY overall_status
");

$healthLedger = [
    'not_started' => 0,
    'requesting' => 0,
    'follow_up' => 0,
    'received' => 0,
    'done' => 0
];

foreach ($hlStats as $stat) {
    $healthLedger[$stat['overall_status']] = (int)$stat['count'];
}

$healthLedger['total'] = array_sum($healthLedger);
$healthLedger['active'] = $healthLedger['requesting'] + $healthLedger['follow_up'];

successResponse([
    'communication' => [
        'overall_rate' => $overallRate,
        'email' => $communication['email'],
        'fax' => $communication['fax']
    ],
    'treatment_status' => $treatment,
    'on_hold' => array_merge($onHold, ['top_reasons' => $holdReasons]),
    'health_ledger' => $healthLedger
]);
