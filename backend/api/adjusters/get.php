<?php
// GET /api/adjusters/{id} - Get adjuster detail

$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('Adjuster ID is required', 400);
}

$adjuster = dbFetchOne(
    "SELECT a.*, ic.name AS insurance_company_name
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON a.insurance_company_id = ic.id
     WHERE a.id = ?",
    [$id]
);

if (!$adjuster) {
    errorResponse('Adjuster not found', 404);
}

successResponse($adjuster);
