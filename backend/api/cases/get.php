<?php
$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

$caseData = dbFetchOne(
    "SELECT c.*, u.full_name AS assigned_name
     FROM cases c
     LEFT JOIN users u ON c.assigned_to = u.id
     WHERE c.id = ?",
    [$caseId]
);

if (!$caseData) {
    errorResponse('Case not found', 404);
}

$providerCount = dbCount('case_providers', 'case_id = ?', [$caseId]);
$pendingCount = dbCount(
    'case_providers',
    "case_id = ? AND overall_status NOT IN ('received_complete', 'verified')",
    [$caseId]
);

$caseData['provider_count'] = $providerCount;
$caseData['pending_count'] = $pendingCount;

// Auto-calculate ini_completed: false if any provider is still treating
$treatingCount = dbCount('case_providers', "case_id = ? AND overall_status = 'treating'", [$caseId]);
if ($treatingCount > 0) {
    $caseData['ini_completed'] = 0;
}

successResponse($caseData);
