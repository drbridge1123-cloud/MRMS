<?php
// DELETE /api/insurance-companies/{id} - Delete an insurance company

$userId = requireAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    errorResponse('Insurance company ID is required', 400);
}

$existing = dbFetchOne("SELECT id, name FROM insurance_companies WHERE id = ?", [$id]);
if (!$existing) {
    errorResponse('Insurance company not found', 404);
}

// Check if any adjusters are linked
$adjCount = dbFetchOne("SELECT COUNT(*) as cnt FROM adjusters WHERE insurance_company_id = ?", [$id]);
if ($adjCount && (int)$adjCount['cnt'] > 0) {
    errorResponse('Cannot delete — has ' . $adjCount['cnt'] . ' linked adjuster(s). Remove or reassign them first.', 409);
}

dbDelete('insurance_companies', 'id = ?', [$id]);

logActivity($userId, 'deleted', 'insurance_company', $id, [
    'name' => $existing['name']
]);

successResponse(null, 'Insurance company deleted successfully');
