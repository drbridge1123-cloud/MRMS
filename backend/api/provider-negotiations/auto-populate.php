<?php
// POST /api/provider-negotiations/{case_id}/populate - Auto-populate from MBDS lines
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

// Get MBDS report for this case
$report = dbFetchOne("SELECT id FROM mbds_reports WHERE case_id = ?", [$caseId]);
if (!$report) {
    jsonResponse([
        'success' => true,
        'created' => 0,
        'skipped' => 0,
        'message' => 'No Medical Balance report found. Create Medical Balance first.',
    ]);
}

// Get provider lines with balance > 0
$lines = dbFetchAll(
    "SELECT ml.id, ml.provider_name, ml.case_provider_id, ml.balance
     FROM mbds_lines ml
     WHERE ml.report_id = ? AND ml.line_type = 'provider' AND ml.balance > 0
     ORDER BY ml.sort_order",
    [$report['id']]
);

if (empty($lines)) {
    jsonResponse([
        'success' => true,
        'created' => 0,
        'skipped' => 0,
        'message' => 'No provider lines with balance found in Medical Balance',
    ]);
}

// Get existing provider negotiations to avoid duplicates
$existing = dbFetchAll(
    "SELECT mbds_line_id FROM provider_negotiations WHERE case_id = ?",
    [$caseId]
);
$existingLineIds = array_column($existing, 'mbds_line_id');

$created = 0;
$skipped = 0;
foreach ($lines as $line) {
    if (in_array($line['id'], $existingLineIds)) {
        $skipped++;
        continue;
    }

    dbInsert('provider_negotiations', [
        'case_id' => $caseId,
        'case_provider_id' => $line['case_provider_id'],
        'mbds_line_id' => $line['id'],
        'provider_name' => $line['provider_name'],
        'original_balance' => (float)$line['balance'],
        'accepted_amount' => (float)$line['balance'],
        'status' => 'pending',
        'created_by' => $userId,
    ]);
    $created++;
}

logActivity($userId, 'provider_negotiation_populate', 'case', $caseId, [
    'created' => $created,
    'skipped' => $skipped,
]);

jsonResponse([
    'success' => true,
    'created' => $created,
    'skipped' => $skipped,
    'message' => "Created {$created} provider negotiations" . ($skipped ? ", skipped {$skipped} existing" : ''),
]);
