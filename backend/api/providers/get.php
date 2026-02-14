<?php
// GET /api/providers/{id} - Get provider detail with contacts and usage count

$userId = requireAuth();

$providerId = (int)($_GET['id'] ?? 0);
if (!$providerId) {
    errorResponse('Provider ID is required', 400);
}

// Fetch provider
$provider = dbFetchOne(
    "SELECT p.*
     FROM providers p
     WHERE p.id = ?",
    [$providerId]
);

if (!$provider) {
    errorResponse('Provider not found', 404);
}

// Fetch provider contacts
$contacts = dbFetchAll(
    "SELECT id, department, contact_type, contact_value, is_primary, verified_at, notes, created_at
     FROM provider_contacts
     WHERE provider_id = ?
     ORDER BY is_primary DESC, department ASC",
    [$providerId]
);

$provider['contacts'] = $contacts;

// Get usage count - how many cases use this provider
$usageCount = dbFetchOne(
    "SELECT COUNT(*) as cnt FROM case_providers WHERE provider_id = ?",
    [$providerId]
);

$provider['usage_count'] = (int)$usageCount['cnt'];

successResponse($provider);
