<?php
// DELETE /api/negotiations/{id} - Delete a negotiation round
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('id is required');
}

$round = dbFetchOne("SELECT * FROM case_negotiations WHERE id = ?", [$id]);
if (!$round) {
    errorResponse('Negotiation round not found', 404);
}

dbDelete('case_negotiations', 'id = ?', [$id]);

logActivity($userId, 'negotiation_delete', 'case_negotiation', $id, [
    'case_id' => $round['case_id'],
    'coverage_type' => $round['coverage_type'],
    'round_number' => $round['round_number'],
]);

jsonResponse([
    'success' => true,
    'message' => 'Negotiation round deleted',
]);
