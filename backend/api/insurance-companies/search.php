<?php
// GET /api/insurance-companies/search?q=xxx - Autocomplete search

$userId = requireAuth();

$query = trim($_GET['q'] ?? '');
if ($query === '') {
    successResponse([]);
}

$results = dbFetchAll(
    "SELECT id, name, type, email, fax, phone
     FROM insurance_companies
     WHERE name LIKE ?
     ORDER BY name ASC
     LIMIT 10",
    ['%' . $query . '%']
);

successResponse($results);
