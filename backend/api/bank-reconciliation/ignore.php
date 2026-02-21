<?php
// PUT /api/bank-reconciliation/{id}/ignore — mark entry as ignored (not a payment)
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

$input = getInput();
$notes = isset($input['notes']) ? sanitizeString($input['notes']) : null;

dbUpdate('bank_statement_entries', [
    'reconciliation_status' => 'ignored',
    'matched_payment_id' => null,
    'matched_by' => $userId,
    'matched_at' => date('Y-m-d H:i:s'),
    'notes' => $notes,
], 'id = ?', [$entryId]);

logActivity($userId, 'bank_entry_ignored', 'bank_reconciliation', $entryId, [
    'amount' => $entry['amount'],
]);

successResponse(null, 'Entry marked as ignored');
