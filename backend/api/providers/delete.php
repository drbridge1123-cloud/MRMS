<?php
// DELETE /api/providers/{id} - Delete a provider

requireAdminOrManager();
$userId = requireAuth();

$providerId = (int)($_GET['id'] ?? 0);
if (!$providerId) {
    errorResponse('Provider ID is required', 400);
}

// Check provider exists
$existing = dbFetchOne("SELECT id, name FROM providers WHERE id = ?", [$providerId]);
if (!$existing) {
    errorResponse('Provider not found', 404);
}

// Check if provider is used in any cases
$usageCount = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM case_providers WHERE provider_id = ?",
    [$providerId]
);

if ($usageCount && (int)$usageCount['cnt'] > 0) {
    errorResponse('Cannot delete provider — it is linked to ' . $usageCount['cnt'] . ' case(s). Remove it from all cases first.', 409);
}

dbDelete('provider_contacts', 'provider_id = ?', [$providerId]);
dbDelete('providers', 'id = ?', [$providerId]);

logActivity($userId, 'deleted', 'provider', $providerId, [
    'name' => $existing['name']
]);

successResponse(null, 'Provider deleted successfully');
