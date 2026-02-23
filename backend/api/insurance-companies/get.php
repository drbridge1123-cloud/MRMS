<?php
// GET /api/insurance-companies/{id} - Get insurance company detail

$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('Insurance company ID is required', 400);
}

$company = dbFetchOne("SELECT * FROM insurance_companies WHERE id = ?", [$id]);
if (!$company) {
    errorResponse('Insurance company not found', 404);
}

// Fetch linked adjusters
$company['adjusters'] = dbFetchAll(
    "SELECT id, first_name, last_name, title, phone, email, is_active
     FROM adjusters
     WHERE insurance_company_id = ?
     ORDER BY last_name, first_name",
    [$id]
);

// Usage count in case_negotiations (by name match)
$usage = dbFetchOne(
    "SELECT COUNT(DISTINCT case_id) as cnt FROM case_negotiations WHERE insurance_company = ?",
    [$company['name']]
);
$company['usage_count'] = (int)($usage['cnt'] ?? 0);

successResponse($company);
