<?php
/**
 * Update Statistics - Run daily via cron/task scheduler
 * Usage: php update_statistics.php
 *
 * Updates provider avg_response_days based on actual request/receipt data
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/db.php';

echo "=== MRMS Statistics Updater ===\n";
echo "Running at: " . date('Y-m-d H:i:s') . "\n\n";

// Calculate avg response days for each provider
$providers = dbFetchAll("
    SELECT p.id, p.name,
           AVG(DATEDIFF(rr2.received_date, rr1.first_request_date)) as avg_days
    FROM providers p
    JOIN case_providers cp ON cp.provider_id = p.id
    JOIN (
        SELECT case_provider_id, MIN(request_date) as first_request_date
        FROM record_requests
        WHERE request_type = 'initial'
        GROUP BY case_provider_id
    ) rr1 ON rr1.case_provider_id = cp.id
    JOIN (
        SELECT case_provider_id, MIN(received_date) as received_date
        FROM record_receipts
        GROUP BY case_provider_id
    ) rr2 ON rr2.case_provider_id = cp.id
    GROUP BY p.id, p.name
    HAVING avg_days IS NOT NULL
");

foreach ($providers as $provider) {
    $avgDays = round($provider['avg_days']);
    dbUpdate('providers', ['avg_response_days' => $avgDays], 'id = ?', [$provider['id']]);
    echo "  Updated {$provider['name']}: avg {$avgDays} days\n";
}

echo "\nDone. Updated " . count($providers) . " provider(s).\n";
