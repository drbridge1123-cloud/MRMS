<?php
// GET /api/templates - List letter templates
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

// Query parameters
$type = $_GET['type'] ?? null; // Filter by template_type
$activeOnly = isset($_GET['active_only']) && $_GET['active_only'] == '1';

// Build query
$conditions = [];
$params = [];

if ($type) {
    $conditions[] = "template_type = ?";
    $params[] = $type;
}

if ($activeOnly) {
    $conditions[] = "is_active = 1";
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Fetch templates
$templates = dbFetchAll(
    "SELECT
        id,
        name,
        description,
        template_type,
        subject_template,
        is_default,
        is_active,
        created_by,
        created_at,
        updated_at,
        (SELECT full_name FROM users WHERE id = letter_templates.created_by) AS created_by_name
     FROM letter_templates
     $whereClause
     ORDER BY
        is_default DESC,
        template_type ASC,
        name ASC",
    $params
);

successResponse($templates, 'Retrieved ' . count($templates) . ' template(s)');
