<?php
// DELETE /api/bank-reconciliation/batch/{batchId} — delete all entries from a batch
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAdmin();

$batchId = sanitizeString($_GET['batch_id'] ?? '');
if (!$batchId) {
    errorResponse('batch_id is required');
}

$count = dbFetchOne(
    "SELECT COUNT(*) AS cnt FROM bank_statement_entries WHERE batch_id = ?",
    [$batchId]
);

if ((int)$count['cnt'] === 0) {
    errorResponse('Batch not found', 404);
}

dbDelete('bank_statement_entries', 'batch_id = ?', [$batchId]);

logActivity($userId, 'bank_batch_deleted', 'bank_reconciliation', null, [
    'batch_id' => $batchId,
    'entries_deleted' => (int)$count['cnt'],
]);

successResponse(null, (int)$count['cnt'] . ' entries deleted');
