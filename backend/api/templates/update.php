<?php
// PUT /api/templates/{id} - Update template (Admin only, creates new version)
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

// Check if user is admin
$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    errorResponse('Only administrators can update templates', 403);
}

$templateId = (int)($_GET['id'] ?? 0);
if (!$templateId) {
    errorResponse('Template ID is required', 400);
}

$input = getInput();

// Load existing template
$existing = dbFetchOne("SELECT * FROM letter_templates WHERE id = ?", [$templateId]);
if (!$existing) {
    errorResponse('Template not found', 404);
}

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    // If is_default is being set, unset other defaults of same type
    if (!empty($input['is_default'])) {
        dbUpdate(
            'letter_templates',
            ['is_default' => 0],
            'template_type = ? AND is_default = 1 AND id != ?',
            [$existing['template_type'], $templateId]
        );
    }

    // Build update data
    $updateData = [];
    if (isset($input['name'])) {
        $updateData['name'] = sanitizeString($input['name']);
    }
    if (isset($input['description'])) {
        $updateData['description'] = sanitizeString($input['description']);
    }
    if (isset($input['subject_template'])) {
        $updateData['subject_template'] = sanitizeString($input['subject_template']);
    }
    if (isset($input['body_template'])) {
        $updateData['body_template'] = $input['body_template'];
    }
    if (isset($input['is_default'])) {
        $updateData['is_default'] = !empty($input['is_default']) ? 1 : 0;
    }
    if (isset($input['is_active'])) {
        $updateData['is_active'] = !empty($input['is_active']) ? 1 : 0;
    }

    // Update template
    dbUpdate('letter_templates', $updateData, 'id = ?', [$templateId]);

    // Get latest version number
    $latestVersion = dbFetchOne(
        "SELECT MAX(version_number) as max_version FROM letter_template_versions WHERE template_id = ?",
        [$templateId]
    );
    $nextVersion = ($latestVersion['max_version'] ?? 0) + 1;

    // Create new version if body or subject changed
    if (isset($input['body_template']) || isset($input['subject_template'])) {
        dbInsert('letter_template_versions', [
            'template_id' => $templateId,
            'version_number' => $nextVersion,
            'body_template' => $updateData['body_template'] ?? $existing['body_template'],
            'subject_template' => $updateData['subject_template'] ?? $existing['subject_template'],
            'changed_by' => $userId,
            'change_notes' => $input['change_notes'] ?? 'Updated template'
        ]);
    }

    // Log activity
    logActivity($userId, 'template_updated', 'letter_template', $templateId, [
        'name' => $updateData['name'] ?? $existing['name'],
        'version' => $nextVersion
    ]);

    $pdo->commit();

    successResponse(['id' => $templateId, 'version' => $nextVersion], 'Template updated successfully');

} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to update template: ' . $e->getMessage(), 500);
}
