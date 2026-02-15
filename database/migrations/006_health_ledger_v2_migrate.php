<?php
/**
 * Migrate data from old health_ledger_requests (v1 flat) to new v2 structure.
 * Usage: php database/migrations/006_health_ledger_v2_migrate.php [--dry-run]
 */

$isCli = php_sapi_name() === 'cli';
$dryRun = $isCli ? in_array('--dry-run', $argv ?? []) : isset($_GET['dry_run']);

if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/db.php';

$pdo = getDBConnection();

echo "=== Health Ledger v2 Migration ===\n";
echo "Mode: " . ($dryRun ? "DRY RUN\n" : "LIVE\n") . "\n";

// Step 1: Create new tables
echo "Step 1: Creating new tables...\n";
$sql = file_get_contents(__DIR__ . '/006_health_ledger_v2.sql');
if (!$dryRun) {
    // Split and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }
}
echo "  Tables created.\n\n";

// Step 2: Check if old table exists
$oldTableExists = $pdo->query("SHOW TABLES LIKE 'health_ledger_requests'")->rowCount() > 0;
if (!$oldTableExists) {
    echo "Old table 'health_ledger_requests' not found. Nothing to migrate.\n";
    if (!$isCli) echo '</pre>';
    exit;
}

// Step 3: Read all old data
$oldRows = dbFetchAll("SELECT * FROM health_ledger_requests ORDER BY id", []);
echo "Step 2: Found " . count($oldRows) . " records to migrate.\n\n";

$itemsCreated = 0;
$requestsCreated = 0;

foreach ($oldRows as $old) {
    echo sprintf("  [%s] %s | %s | %s | %s\n",
        $dryRun ? 'dry' : 'migrate',
        substr($old['client_name'], 0, 25),
        $old['case_number'] ?? '-',
        substr($old['insurance_carrier'], 0, 25),
        $old['status']
    );

    // Map old status to new overall_status
    $statusMap = [
        'pending' => 'not_started',
        'sent' => 'requesting',
        'follow_up' => 'follow_up',
        'received' => 'received',
        'done' => 'done',
    ];
    $overallStatus = $statusMap[$old['status']] ?? 'not_started';

    if (!$dryRun) {
        // Insert into health_ledger_items
        $itemId = dbInsert('health_ledger_items', [
            'case_id' => $old['case_id'],
            'case_number' => $old['case_number'],
            'client_name' => $old['client_name'],
            'insurance_carrier' => $old['insurance_carrier'],
            'overall_status' => $overallStatus,
            'assigned_to' => $old['assigned_to'],
            'note' => $old['note'],
        ]);
        $itemsCreated++;

        // If there was a sent_date, create a request entry
        if (!empty($old['sent_date']) && !empty($old['request_method'])) {
            dbInsert('hl_requests', [
                'item_id' => $itemId,
                'request_type' => 'initial',
                'request_date' => $old['sent_date'],
                'request_method' => $old['request_method'],
                'send_status' => 'sent',
                'sent_at' => $old['sent_date'] . ' 00:00:00',
                'created_by' => $old['assigned_to'],
            ]);
            $requestsCreated++;
        }
    } else {
        $itemsCreated++;
        if (!empty($old['sent_date']) && !empty($old['request_method'])) {
            $requestsCreated++;
        }
    }
}

echo "\n=== Migration Results ===\n";
echo "Items created: {$itemsCreated}\n";
echo "Requests created: {$requestsCreated}\n";

// Step 4: Drop old table
if (!$dryRun) {
    echo "\nStep 3: Dropping old table...\n";
    $pdo->exec("DROP TABLE IF EXISTS health_ledger_requests");
    echo "  Old table 'health_ledger_requests' dropped.\n";
} else {
    echo "\n** DRY RUN - no changes made **\n";
}

if (!$isCli) echo '</pre>';
