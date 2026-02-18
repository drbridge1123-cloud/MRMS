<?php
// POST /api/mbds/{id}/approve - Jimi approves the report
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

if ($report['status'] !== 'completed') {
    errorResponse('Report must be in completed status to approve');
}

// Mark report as approved
dbUpdate('mbds_reports', [
    'status' => 'approved',
    'approved_by' => $userId,
    'approved_at' => date('Y-m-d H:i:s')
], 'id = ?', [$reportId]);

// Move case to closed
$newOwner = STATUS_OWNER_MAP['closed'] ?? null;
$caseUpdate = ['status' => 'closed'];
if ($newOwner) {
    $caseUpdate['assigned_to'] = $newOwner;
}
dbUpdate('cases', $caseUpdate, 'id = ?', [$report['case_id']]);

logActivity($userId, 'mbds_approved', 'mbds_report', $reportId, [
    'case_number' => $report['case_number']
]);

successResponse(null, 'Report approved and case closed');
