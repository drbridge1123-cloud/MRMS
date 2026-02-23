<?php
// DELETE /api/provider-negotiations/{id} - Delete a provider negotiation
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('id is required');
}

$neg = dbFetchOne("SELECT * FROM provider_negotiations WHERE id = ?", [$id]);
if (!$neg) {
    errorResponse('Provider negotiation not found', 404);
}

dbDelete('provider_negotiations', 'id = ?', [$id]);

logActivity($userId, 'provider_negotiation_delete', 'provider_negotiation', $id, [
    'case_id' => $neg['case_id'],
    'provider_name' => $neg['provider_name'],
]);

jsonResponse([
    'success' => true,
    'message' => 'Provider negotiation deleted',
]);
