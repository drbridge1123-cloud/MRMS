<?php
// DELETE /api/mr-fee-payments/{id}
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$paymentId = (int)($_GET['id'] ?? 0);
if (!$paymentId) {
    errorResponse('Payment ID is required');
}

$existing = dbFetchOne("SELECT * FROM mr_fee_payments WHERE id = ?", [$paymentId]);
if (!$existing) {
    errorResponse('Payment not found', 404);
}

$cpId = $existing['case_provider_id'];

dbDelete('mr_fee_payments', 'id = ?', [$paymentId]);

// Sync MBDS after deletion
if ($cpId) {
    syncMbdsOfficePaid((int)$cpId);
}

logActivity($userId, 'payment_deleted', 'mr_fee_payment', $paymentId, [
    'case_id' => $existing['case_id'],
    'amount' => $existing['paid_amount'],
    'provider_name' => $existing['provider_name']
]);

successResponse(null, 'Payment deleted successfully');

function syncMbdsOfficePaid($caseProviderId) {
    $total = dbFetchOne(
        "SELECT COALESCE(SUM(paid_amount), 0) AS total FROM mr_fee_payments WHERE case_provider_id = ?",
        [$caseProviderId]
    );
    $line = dbFetchOne(
        "SELECT id FROM mbds_lines WHERE case_provider_id = ?",
        [$caseProviderId]
    );
    if ($line) {
        dbUpdate('mbds_lines', ['office_paid' => $total['total']], 'id = ?', [$line['id']]);
    }
}
