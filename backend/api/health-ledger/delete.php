<?php
// DELETE /api/health-ledger/{id} - Delete a health ledger item (cascades requests)
$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);
if (!$id) errorResponse('ID is required');

$existing = dbFetchOne("SELECT * FROM health_ledger_items WHERE id = ?", [$id]);
if (!$existing) errorResponse('Item not found', 404);

dbDelete('health_ledger_items', 'id = ?', [$id]);
logActivity($userId, 'hl_item_deleted', 'health_ledger_item', $id, [
    'client_name' => $existing['client_name'],
    'carrier' => $existing['insurance_carrier']
]);

successResponse(null, 'Item deleted');
