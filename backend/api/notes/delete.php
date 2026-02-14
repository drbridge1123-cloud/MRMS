<?php
// DELETE /api/notes/:id - Delete a note
$userId = requireAuth();

$noteId = (int)($_GET['id'] ?? 0);
if (!$noteId) {
    errorResponse('Note ID is required');
}

$note = dbFetchOne("SELECT id, case_id, user_id FROM case_notes WHERE id = ?", [$noteId]);
if (!$note) {
    errorResponse('Note not found', 404);
}

// Only the author or an admin can delete
$user = getCurrentUser();
if ((int)$note['user_id'] !== $userId && $user['role'] !== 'admin') {
    errorResponse('You can only delete your own notes', 403);
}

dbDelete('case_notes', 'id = ?', [$noteId]);

logActivity($userId, 'deleted', 'note', $noteId, ['case_id' => $note['case_id']]);

successResponse(null, 'Note deleted successfully');
