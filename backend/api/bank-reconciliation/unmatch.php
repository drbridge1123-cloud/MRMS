<?php
// PUT /api/bank-reconciliation/{id}/unmatch — remove match from a bank entry
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAdmin();

$entryId = (int)($_GET['id'] ?? 0);
if (!$entryId) {
    errorResponse('Entry ID is required');
}

$entry = dbFetchOne("SELECT * FROM bank_statement_entries WHERE id = ?", [$entryId]);
if (!$entry) {
    errorResponse('Bank entry not found', 404);
}

if ($entry['reconciliation_status'] === 'unmatched') {
    errorResponse('Entry is already unmatched');
}

dbUpdate('bank_statement_entries', [
    'reconciliation_status' => 'unmatched',
    'matched_payment_id' => null,
    'matched_by' => null,
    'matched_at' => null,
], 'id = ?', [$entryId]);

logActivity($userId, 'bank_entry_unmatched', 'bank_reconciliation', $entryId, [
    'previous_payment_id' => $entry['matched_payment_id'],
]);

successResponse(null, 'Match removed successfully');
