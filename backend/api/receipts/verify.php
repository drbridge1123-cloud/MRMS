<?php
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$receiptId = (int)($_GET['id'] ?? 0);
if (!$receiptId) {
    errorResponse('Receipt ID is required');
}

$receipt = dbFetchOne("SELECT * FROM record_receipts WHERE id = ?", [$receiptId]);
if (!$receipt) {
    errorResponse('Receipt not found', 404);
}

dbUpdate('record_receipts', ['verified_by' => $userId], 'id = ?', [$receiptId]);
dbUpdate('case_providers', ['overall_status' => 'verified'], 'id = ?', [$receipt['case_provider_id']]);

logActivity($userId, 'verified', 'record_receipt', $receiptId);

$updated = dbFetchOne("SELECT * FROM record_receipts WHERE id = ?", [$receiptId]);
successResponse($updated, 'Receipt verified successfully');
