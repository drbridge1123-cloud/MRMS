<?php
// GET /api/dashboard/provider-analytics - Provider difficulty and volume metrics
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

// Top difficult providers with active requests
$topDifficult = dbFetchAll("
    SELECT
        p.id,
        p.name,
        p.type,
        p.difficulty_level,
        p.avg_response_days,
        COUNT(DISTINCT cp.id) as active_requests,
        COUNT(DISTINCT CASE
            WHEN cp.deadline < CURDATE()
            AND cp.overall_status NOT IN ('received_complete', 'verified')
            THEN cp.id
        END) as overdue_count
    FROM providers p
    INNER JOIN case_providers cp ON cp.provider_id = p.id
    INNER JOIN cases c ON c.id = cp.case_id
    WHERE cp.overall_status NOT IN ('received_complete', 'verified')
      AND c.status NOT IN ('closed')
    GROUP BY p.id, p.name, p.type, p.difficulty_level, p.avg_response_days
    HAVING active_requests > 0
    ORDER BY
        FIELD(p.difficulty_level, 'hard', 'medium', 'easy'),
        active_requests DESC,
        overdue_count DESC
    LIMIT 10
");

foreach ($topDifficult as &$provider) {
    $provider['active_requests'] = (int)$provider['active_requests'];
    $provider['overdue_count'] = (int)$provider['overdue_count'];
    $provider['avg_response_days'] = $provider['avg_response_days'] ? (int)$provider['avg_response_days'] : null;
}
unset($provider);

// Difficulty level distribution (all providers with active requests)
$difficultyDist = dbFetchAll("
    SELECT
        p.difficulty_level,
        COUNT(DISTINCT p.id) as provider_count,
        COUNT(DISTINCT cp.id) as request_count,
        AVG(p.avg_response_days) as avg_response
    FROM providers p
    INNER JOIN case_providers cp ON cp.provider_id = p.id
    INNER JOIN cases c ON c.id = cp.case_id
    WHERE cp.overall_status NOT IN ('received_complete', 'verified')
      AND c.status NOT IN ('closed')
    GROUP BY p.difficulty_level
    ORDER BY FIELD(p.difficulty_level, 'hard', 'medium', 'easy')
");

foreach ($difficultyDist as &$diff) {
    $diff['provider_count'] = (int)$diff['provider_count'];
    $diff['request_count'] = (int)$diff['request_count'];
    $diff['avg_response'] = $diff['avg_response'] ? round((float)$diff['avg_response'], 1) : null;
}
unset($diff);

// Most requested providers (last 30 days)
$mostRequested = dbFetchAll("
    SELECT
        p.id,
        p.name,
        p.type,
        p.difficulty_level,
        COUNT(DISTINCT rr.id) as request_count
    FROM providers p
    INNER JOIN case_providers cp ON cp.provider_id = p.id
    INNER JOIN record_requests rr ON rr.case_provider_id = cp.id
    WHERE rr.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id, p.name, p.type, p.difficulty_level
    ORDER BY request_count DESC
    LIMIT 10
");

foreach ($mostRequested as &$provider) {
    $provider['request_count'] = (int)$provider['request_count'];
}
unset($provider);

successResponse([
    'top_difficult' => $topDifficult,
    'difficulty_distribution' => $difficultyDist,
    'most_requested_30d' => $mostRequested
]);
