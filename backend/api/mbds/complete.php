<?php
// POST /api/mbds/{id}/complete - Ella marks report as completed
$userId = requireAuth();

$reportId = (int)($_GET['id'] ?? 0);
if (!$reportId) {
    errorResponse('Report ID is required', 400);
}

$report = dbFetchOne(
    "SELECT r.*, c.case_number, c.id AS case_id
     FROM mbds_reports r JOIN cases c ON c.id = r.case_id
     WHERE r.id = ?",
    [$reportId]
);
if (!$report) {
    errorResponse('Report not found', 404);
}

if ($report['status'] !== 'draft') {
    errorResponse('Report is already ' . $report['status']);
}

// Mark report as completed
dbUpdate('mbds_reports', [
    'status' => 'completed',
    'completed_by' => $userId,
    'completed_at' => date('Y-m-d H:i:s')
], 'id = ?', [$reportId]);

// Move case to completed → auto-assign to Jimi
$newOwner = STATUS_OWNER_MAP['completed'] ?? null;
$caseUpdate = ['status' => 'completed'];
if ($newOwner) {
    $caseUpdate['assigned_to'] = $newOwner;
}
dbUpdate('cases', $caseUpdate, 'id = ?', [$report['case_id']]);

// Notify Jimi
if ($newOwner) {
    dbInsert('notifications', [
        'user_id' => $newOwner,
        'type' => 'mbds_completed',
        'message' => "MBDS report for case {$report['case_number']} is ready for review — assigned to you",
        'due_date' => date('Y-m-d')
    ]);
}

logActivity($userId, 'mbds_completed', 'mbds_report', $reportId, [
    'case_number' => $report['case_number']
]);

successResponse(null, 'Report marked as completed');
