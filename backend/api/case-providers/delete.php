<?php
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$cpId = (int)($_GET['id'] ?? 0);
if (!$cpId) {
    errorResponse('Case provider ID is required');
}

// Verify the case_provider exists
$cp = dbFetchOne(
    "SELECT cp.*, c.case_number, p.name AS provider_name
     FROM case_providers cp
     JOIN cases c ON cp.case_id = c.id
     JOIN providers p ON cp.provider_id = p.id
     WHERE cp.id = ?",
    [$cpId]
);
if (!$cp) {
    errorResponse('Case provider not found', 404);
}

dbDelete('case_providers', 'id = ?', [$cpId]);

logActivity($userId, 'deleted', 'case_provider', $cpId, [
    'case_id' => $cp['case_id'],
    'provider_name' => $cp['provider_name'],
    'case_number' => $cp['case_number']
]);

successResponse(null, 'Provider removed from case successfully');
