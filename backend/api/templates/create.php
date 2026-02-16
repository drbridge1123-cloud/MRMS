<?php
// POST /api/templates - Create new template (Admin only)
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

// Check if user is admin
$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    errorResponse('Only administrators can create templates', 403);
}

$input = getInput();

// Validate required fields
$errors = validateRequired($input, ['name', 'template_type', 'body_template']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors), 400);
}

// Validate template_type
$allowedTypes = ['medical_records', 'health_ledger', 'bulk_request', 'custom'];
if (!in_array($input['template_type'], $allowedTypes)) {
    errorResponse('Invalid template_type. Allowed: ' . implode(', ', $allowedTypes), 422);
}

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    // If is_default is set, unset other defaults of same type
    if (!empty($input['is_default'])) {
        dbUpdate(
            'letter_templates',
            ['is_default' => 0],
            'template_type = ? AND is_default = 1',
            [$input['template_type']]
        );
    }

    // Create template
    $templateData = [
        'name' => sanitizeString($input['name']),
        'description' => !empty($input['description']) ? sanitizeString($input['description']) : null,
        'template_type' => $input['template_type'],
        'subject_template' => !empty($input['subject_template']) ? sanitizeString($input['subject_template']) : null,
        'body_template' => $input['body_template'], // Keep HTML as-is
        'is_default' => !empty($input['is_default']) ? 1 : 0,
        'is_active' => 1,
        'created_by' => $userId,
    ];

    $templateId = dbInsert('letter_templates', $templateData);

    // Create version 1
    dbInsert('letter_template_versions', [
        'template_id' => $templateId,
        'version_number' => 1,
        'body_template' => $input['body_template'],
        'subject_template' => $templateData['subject_template'],
        'changed_by' => $userId,
        'change_notes' => 'Initial version'
    ]);

    // Log activity
    logActivity($userId, 'template_created', 'letter_template', $templateId, [
        'name' => $templateData['name'],
        'type' => $templateData['template_type']
    ]);

    $pdo->commit();

    successResponse(['id' => $templateId], 'Template created successfully');

} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to create template: ' . $e->getMessage(), 500);
}
