<?php
// POST /api/mbds/{id}/activate-providers - Activate providers (INI Complete)
// Sets case_providers to requesting, assigns staff, sets 30-day deadline, creates notifications
$userId = requireAuth();

$reportId = (int)($_GET['id'] ?? 0);
if (!$reportId) {
    errorResponse('Report ID is required', 400);
}

$report = dbFetchOne("SELECT r.*, c.case_number, c.client_name FROM mbds_reports r JOIN cases c ON c.id = r.case_id WHERE r.id = ?", [$reportId]);
if (!$report) {
    errorResponse('Report not found', 404);
}

$input = getInput();
$assignedTo = (int)($input['assigned_to'] ?? 0);
if (!$assignedTo) {
    errorResponse('Staff assignment is required', 400);
}

// Verify staff exists
$staff = dbFetchOne("SELECT id, full_name FROM users WHERE id = ? AND is_active = 1", [$assignedTo]);
if (!$staff) {
    errorResponse('Staff member not found', 404);
}

// Get lines to activate
$lineIds = $input['line_ids'] ?? [];
if (!empty($lineIds)) {
    // Specific lines
    $placeholders = implode(',', array_fill(0, count($lineIds), '?'));
    $lines = dbFetchAll(
        "SELECT * FROM mbds_lines WHERE report_id = ? AND id IN ($placeholders) AND line_type = 'provider' AND case_provider_id IS NOT NULL AND ini_status = 'pending'",
        array_merge([$reportId], array_map('intval', $lineIds))
    );
} else {
    // All pending provider lines
    $lines = dbFetchAll(
        "SELECT * FROM mbds_lines WHERE report_id = ? AND line_type = 'provider' AND case_provider_id IS NOT NULL AND ini_status = 'pending'",
        [$reportId]
    );
}

if (empty($lines)) {
    errorResponse('No pending providers to activate', 400);
}

$deadline = date('Y-m-d', strtotime('+30 days'));
$activated = 0;

foreach ($lines as $line) {
    $cpId = (int)$line['case_provider_id'];

    // Check case_provider exists and is treating (waiting for activation)
    $cp = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$cpId]);
    if (!$cp || $cp['overall_status'] !== 'treating') {
        // Already activated or missing — just mark ini_status
        dbUpdate('mbds_lines', ['ini_status' => 'complete'], 'id = ?', [$line['id']]);
        continue;
    }

    // Activate: set status to not_started (ready for requesting), deadline, assigned_to
    dbUpdate('case_providers', [
        'overall_status' => 'not_started',
        'deadline' => $deadline,
        'assigned_to' => $assignedTo,
        'assignment_status' => 'pending',
        'activated_by' => $userId
    ], 'id = ?', [$cpId]);

    // Mark mbds_line as INI complete
    dbUpdate('mbds_lines', ['ini_status' => 'complete'], 'id = ?', [$line['id']]);

    // Send assignment message
    $activator = dbFetchOne("SELECT full_name FROM users WHERE id = ?", [$userId]);
    $activatorName = $activator['full_name'] ?? 'System';
    dbInsert('messages', [
        'from_user_id' => $userId,
        'to_user_id' => $assignedTo,
        'subject' => "[Assignment] {$line['provider_name']} — Case #{$report['case_number']}",
        'message' => "{$activatorName} assigned you to request records from {$line['provider_name']} for case {$report['case_number']} ({$report['client_name']}).\n\nDeadline: " . date('M j, Y', strtotime($deadline))
    ]);

    $activated++;
}

logActivity($userId, 'activated_providers', 'mbds_report', $reportId, [
    'activated_count' => $activated,
    'assigned_to' => $staff['full_name']
]);

successResponse([
    'activated' => $activated,
    'deadline' => $deadline,
    'assigned_to_name' => $staff['full_name']
], $activated . ' provider(s) activated');
