<?php
// DELETE /api/adjusters/{id} - Delete an adjuster

$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('Adjuster ID is required', 400);
}

$existing = dbFetchOne("SELECT id, first_name, last_name FROM adjusters WHERE id = ?", [$id]);
if (!$existing) {
    errorResponse('Adjuster not found', 404);
}

dbDelete('adjusters', 'id = ?', [$id]);

logActivity($userId, 'deleted', 'adjuster', $id, [
    'name' => $existing['first_name'] . ' ' . $existing['last_name']
]);

successResponse(null, 'Adjuster deleted successfully');
