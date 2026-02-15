<?php
// GET /api/health-ledger/{id}/requests - Get request history for an item
$userId = requireAuth();
$itemId = (int)($_GET['id'] ?? 0);
if (!$itemId) errorResponse('Item ID is required');

$item = dbFetchOne("SELECT id FROM health_ledger_items WHERE id = ?", [$itemId]);
if (!$item) errorResponse('Item not found', 404);

$requests = dbFetchAll("
    SELECT hlr.*, u.full_name AS created_by_name
    FROM hl_requests hlr
    LEFT JOIN users u ON hlr.created_by = u.id
    WHERE hlr.item_id = ?
    ORDER BY hlr.id DESC
", [$itemId]);

successResponse($requests);
