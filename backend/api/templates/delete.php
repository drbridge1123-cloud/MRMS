<?php
// DELETE /api/templates/{id} - Soft delete template (Admin only)
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

// Check if user is admin
$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    errorResponse('Only administrators can delete templates', 403);
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

// Check if template is in use by pending requests
$inUse = dbFetchOne(
    "SELECT COUNT(*) as count FROM record_requests WHERE template_id = ? AND send_status IN ('draft', 'sending')",
    [$templateId]
);

if ($inUse['count'] > 0) {
    errorResponse('Cannot delete template that is in use by ' . $inUse['count'] . ' pending request(s)', 422);
}

// Soft delete (set is_active = 0)
dbUpdate('letter_templates', ['is_active' => 0], 'id = ?', [$templateId]);

// Log activity
logActivity($userId, 'template_deleted', 'letter_template', $templateId, [
    'name' => $template['name']
]);

successResponse(['deleted' => true], 'Template deleted successfully');
