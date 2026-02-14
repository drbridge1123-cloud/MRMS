<?php
// DELETE /api/cases/{id} - Delete a case (admin only)
$userId = requireAuth();
requireAdmin();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

// Check case exists
$caseData = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$caseId]);
if (!$caseData) {
    errorResponse('Case not found', 404);
}

// Delete the case (cascading deletes will handle case_providers, requests, receipts, notes)
$deleted = dbDelete('cases', 'id = ?', [$caseId]);

if (!$deleted) {
    errorResponse('Failed to delete case', 500);
}

logActivity($userId, 'delete', 'case', $caseId, [
    'case_number' => $caseData['case_number'],
    'client_name' => $caseData['client_name']
]);

successResponse(null, 'Case deleted successfully');
