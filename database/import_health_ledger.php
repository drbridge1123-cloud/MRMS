<?php
/**
 * Import "Medical Records Request from Health.csv" into V2 health ledger tables.
 * Inserts into health_ledger_items (parent) + hl_requests (child).
 * Usage: php database/import_health_ledger.php [--dry-run]
 * Or browser: http://localhost/MRMS/database/import_health_ledger.php?dry_run=1
 */

$isCli = php_sapi_name() === 'cli';
$dryRun = $isCli ? in_array('--dry-run', $argv ?? []) : isset($_GET['dry_run']);

if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/db.php';

$pdo = getDBConnection();
$csvFile = __DIR__ . '/../Medical Records Request from Health.csv';

if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile\nPlace the CSV in the MRMS root directory.\n");
}

echo "=== Health Ledger CSV Import (V2) ===\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no changes)\n" : "LIVE\n");
echo "File: $csvFile\n\n";

// ─── Pre-load lookup data ───
// Users
$users = dbFetchAll("SELECT id, full_name FROM users", []);
$userMap = [];
foreach ($users as $u) {
    $userMap[strtolower(trim($u['full_name']))] = (int)$u['id'];
}
echo "Loaded " . count($userMap) . " users\n";

// Cases
$cases = dbFetchAll("SELECT id, case_number FROM cases", []);
$caseMap = [];
foreach ($cases as $c) {
    $caseMap[trim($c['case_number'])] = (int)$c['id'];
}
echo "Loaded " . count($caseMap) . " cases\n\n";

// ─── Method mapping ───
$methodMap = [
    'fax' => 'fax',
    'email' => 'email',
    'e-mail' => 'email',
    'portal' => 'portal',
    'phone' => 'phone',
    'mail' => 'mail',
];

// ─── Status mapping (CSV → V2 overall_status) ───
$statusMap = [
    'sent' => 'requesting',
    'follow up' => 'follow_up',
    'received' => 'received',
    'done' => 'done',
];

// ─── Parse CSV ───
$handle = fopen($csvFile, 'r');
if (!$handle) die("Could not open CSV file.\n");

// Read header
$header = fgetcsv($handle);
$header = array_map(function($h) {
    return strtolower(trim(preg_replace('/[\x{FEFF}]/u', '', $h)));
}, $header);

echo "CSV Headers: " . implode(' | ', array_slice($header, 0, 11)) . "\n\n";

// Column indices (based on CSV structure)
// Req Date,NAME,CASE NO,INSURANCE CARRIER, (VIA),REQ ,BY,1st FOLLOW UP,2nd FOLLOW UP,NOTE,,Order,Status
$COL_REQ_DATE = 0;
$COL_NAME = 1;
$COL_CASE_NO = 2;
$COL_CARRIER = 3;
$COL_VIA = 4;
$COL_SENT_DATE = 5;
$COL_BY = 6;
$COL_FOLLOWUP1 = 7;
$COL_FOLLOWUP2 = 8;
$COL_NOTE = 9;
// col 10 is empty
$COL_ORDER = 11;
$COL_STATUS = 12;

// Stats
$stats = [
    'items_created' => 0,
    'requests_created' => 0,
    'skipped' => 0,
    'unmatched_cases' => [],
    'unmatched_users' => [],
];

// Legend/header rows to skip (rows 2-5 contain legend: OC=Open Claim, Fax, E-mail, etc.)
$legendRows = [2, 3, 4, 5];

$currentClient = '';
$currentCaseNumber = '';
$rowNum = 0;

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;

    // Skip header legend rows
    if (in_array($rowNum, $legendRows)) {
        echo "  [skip] Row $rowNum: legend row\n";
        continue;
    }

    // Check if row is empty
    $nonEmpty = array_filter($row, function($v) { return trim($v ?? '') !== ''; });
    if (empty($nonEmpty)) continue;

    $get = function($col) use ($row) {
        return isset($row[$col]) ? trim($row[$col] ?? '') : '';
    };

    $clientName = $get($COL_NAME);
    $caseNumber = $get($COL_CASE_NO);
    $carrier = $get($COL_CARRIER);

    // Update parent context
    if ($clientName) $currentClient = $clientName;
    if ($caseNumber) $currentCaseNumber = $caseNumber;

    // Skip rows without carrier
    if (!$carrier) continue;

    $finalClient = $clientName ?: $currentClient;
    $finalCaseNumber = $caseNumber ?: $currentCaseNumber;

    if (!$finalClient) {
        echo "  [skip] Row $rowNum: no client name\n";
        $stats['skipped']++;
        continue;
    }

    // Parse method
    $methodRaw = strtolower($get($COL_VIA));
    $method = $methodMap[$methodRaw] ?? null;

    // Parse dates
    $sentDate = parseDate($get($COL_SENT_DATE));
    $followup1 = parseDate($get($COL_FOLLOWUP1));
    $followup2 = parseDate($get($COL_FOLLOWUP2));

    // Parse assigned user
    $assignedRaw = strtolower(trim($get($COL_BY)));
    $assignedId = null;
    if ($assignedRaw) {
        $assignedId = $userMap[$assignedRaw] ?? null;
        if (!$assignedId && $assignedRaw) {
            // Try partial match
            foreach ($userMap as $name => $uid) {
                if (strpos($name, $assignedRaw) !== false || strpos($assignedRaw, $name) !== false) {
                    $assignedId = $uid;
                    break;
                }
            }
            if (!$assignedId) {
                $stats['unmatched_users'][$assignedRaw] = true;
            }
        }
    }

    // Parse status → V2 overall_status
    $statusRaw = strtolower($get($COL_STATUS));
    $overallStatus = $statusMap[$statusRaw] ?? ($sentDate ? 'requesting' : 'not_started');

    // Match case
    $caseId = $caseMap[$finalCaseNumber] ?? null;
    if (!$caseId && $finalCaseNumber) {
        $stats['unmatched_cases'][$finalCaseNumber] = $finalClient;
    }

    // Note
    $note = $get($COL_NOTE);

    echo sprintf("  [%s] Row %d: %s | %s | %s | %s | %s\n",
        $dryRun ? 'dry' : 'add',
        $rowNum,
        substr($finalClient, 0, 25),
        $finalCaseNumber,
        substr($carrier, 0, 25),
        $method ?? '-',
        $overallStatus
    );

    if (!$dryRun) {
        try {
            // 1. Insert parent: health_ledger_items
            $itemId = dbInsert('health_ledger_items', [
                'case_id' => $caseId,
                'case_number' => $finalCaseNumber ?: null,
                'client_name' => $finalClient,
                'insurance_carrier' => $carrier,
                'overall_status' => $overallStatus,
                'assigned_to' => $assignedId,
                'note' => $note ?: null,
            ]);
            $stats['items_created']++;

            // 2. Insert child requests if there was a sent date + method
            if ($sentDate && $method) {
                dbInsert('hl_requests', [
                    'item_id' => $itemId,
                    'request_type' => 'initial',
                    'request_date' => $sentDate,
                    'request_method' => $method,
                    'send_status' => 'sent',
                    'sent_at' => $sentDate . ' 00:00:00',
                    'created_by' => $assignedId,
                ]);
                $stats['requests_created']++;

                // Follow-up 1
                if ($followup1) {
                    dbInsert('hl_requests', [
                        'item_id' => $itemId,
                        'request_type' => 'follow_up',
                        'request_date' => $followup1,
                        'request_method' => $method,
                        'send_status' => 'sent',
                        'sent_at' => $followup1 . ' 00:00:00',
                        'created_by' => $assignedId,
                    ]);
                    $stats['requests_created']++;
                }

                // Follow-up 2
                if ($followup2) {
                    dbInsert('hl_requests', [
                        'item_id' => $itemId,
                        'request_type' => 'follow_up',
                        'request_date' => $followup2,
                        'request_method' => $method,
                        'send_status' => 'sent',
                        'sent_at' => $followup2 . ' 00:00:00',
                        'created_by' => $assignedId,
                    ]);
                    $stats['requests_created']++;
                }
            }
        } catch (Exception $e) {
            echo "    ERROR: " . $e->getMessage() . "\n";
            $stats['skipped']++;
        }
    } else {
        $stats['items_created']++;
        if ($sentDate && $method) {
            $stats['requests_created']++;
            if ($followup1) $stats['requests_created']++;
            if ($followup2) $stats['requests_created']++;
        }
    }
}

fclose($handle);

// ─── Report ───
echo "\n=== Import Results ===\n";
echo "Items created:    {$stats['items_created']}\n";
echo "Requests created: {$stats['requests_created']}\n";
echo "Records skipped:  {$stats['skipped']}\n";

if ($stats['unmatched_cases']) {
    echo "\nUnmatched cases (" . count($stats['unmatched_cases']) . "):\n";
    foreach ($stats['unmatched_cases'] as $caseNum => $client) {
        echo "  - Case #{$caseNum}: {$client}\n";
    }
}

if ($stats['unmatched_users']) {
    echo "\nUnmatched users (" . count($stats['unmatched_users']) . "):\n";
    foreach (array_keys($stats['unmatched_users']) as $name) {
        echo "  - '{$name}'\n";
    }
}

if ($dryRun) {
    echo "\n** DRY RUN - no changes were made **\n";
}

if (!$isCli) echo '</pre>';

// ─── Helper ───
function parseDate($dateStr) {
    if (!$dateStr) return null;
    $dateStr = trim($dateStr);

    // Invalid date patterns
    if (preg_match('/^0\/0\/0/', $dateStr)) return null;
    if (preg_match('/^Cosed/i', $dateStr)) return null;
    if (!preg_match('/\d/', $dateStr)) return null;

    // m/d/Y
    $d = DateTime::createFromFormat('n/j/Y', $dateStr);
    if ($d && $d->format('n/j/Y') === $dateStr) return $d->format('Y-m-d');

    $d = DateTime::createFromFormat('m/d/Y', $dateStr);
    if ($d) return $d->format('Y-m-d');

    // Y-m-d
    $d = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($d && $d->format('Y-m-d') === $dateStr) return $d->format('Y-m-d');

    return null;
}
