<?php
// GET /api/providers/search?q=xxx - Provider name autocomplete search

$userId = requireAuth();

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    successResponse([]);
}

$results = dbFetchAll(
    "SELECT id, name, type
     FROM providers
     WHERE name LIKE ?
     ORDER BY name ASC
     LIMIT 10",
    ['%' . $query . '%']
);

successResponse($results);
