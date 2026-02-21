<?php
// PUT /api/bank-reconciliation/{id}/match — manually match a bank entry to a payment
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

$paymentId = (int)($input['payment_id'] ?? 0);
if (!$paymentId) {
    errorResponse('payment_id is required');
}

$payment = dbFetchOne("SELECT * FROM mr_fee_payments WHERE id = ?", [$paymentId]);
if (!$payment) {
    errorResponse('Payment not found', 404);
}

// Check if payment is already matched to another entry
$existingMatch = dbFetchOne(
    "SELECT id FROM bank_statement_entries WHERE matched_payment_id = ? AND id != ?",
    [$paymentId, $entryId]
);
if ($existingMatch) {
    errorResponse('This payment is already matched to another bank entry');
}

dbUpdate('bank_statement_entries', [
    'reconciliation_status' => 'matched',
    'matched_payment_id' => $paymentId,
    'matched_by' => $userId,
    'matched_at' => date('Y-m-d H:i:s'),
], 'id = ?', [$entryId]);

logActivity($userId, 'bank_entry_matched', 'bank_reconciliation', $entryId, [
    'payment_id' => $paymentId,
    'amount' => $entry['amount'],
    'check_number' => $entry['check_number'],
]);

$updated = dbFetchOne(
    "SELECT b.*, p.provider_name AS matched_provider_name, c.case_number AS matched_case_number
     FROM bank_statement_entries b
     LEFT JOIN mr_fee_payments p ON b.matched_payment_id = p.id
     LEFT JOIN cases c ON p.case_id = c.id
     WHERE b.id = ?",
    [$entryId]
);

successResponse($updated, 'Entry matched successfully');
