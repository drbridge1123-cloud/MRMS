<?php
// DELETE /api/templates/{id} - Soft delete template (Admin only)
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

// Check if user is admin or manager
$user = getCurrentUser();
if (!in_array($user['role'], ['admin', 'manager'])) {
    errorResponse('Only administrators or managers can delete templates', 403);
}

$templateId = (int)($_GET['id'] ?? 0);
if (!$templateId) {
    errorResponse('Template ID is required', 400);
}

// Load template
$template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ?", [$templateId]);
if (!$template) {
    errorResponse('Template not found', 404);
}

// Soft delete (set is_active = 0)
dbUpdate('letter_templates', ['is_active' => 0], 'id = ?', [$templateId]);

// Log activity
logActivity($userId, 'template_deleted', 'letter_template', $templateId, [
    'name' => $template['name']
]);

successResponse(['deleted' => true], 'Template deleted successfully');
