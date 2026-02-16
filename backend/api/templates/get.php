<?php
// GET /api/templates/{id} - Get single template
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

$templateId = (int)($_GET['id'] ?? 0);
if (!$templateId) {
    errorResponse('Template ID is required', 400);
}

// Fetch template
$template = dbFetchOne(
    "SELECT
        id,
        name,
        description,
        template_type,
        subject_template,
        body_template,
        is_default,
        is_active,
        created_by,
        created_at,
        updated_at,
        (SELECT full_name FROM users WHERE id = letter_templates.created_by) AS created_by_name
     FROM letter_templates
     WHERE id = ?",
    [$templateId]
);

if (!$template) {
    errorResponse('Template not found', 404);
}

successResponse($template);
