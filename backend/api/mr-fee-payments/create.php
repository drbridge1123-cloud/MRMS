<?php
// POST /api/mr-fee-payments
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$input = getInput();
$errors = validateRequired($input, ['case_id']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$caseId = (int)$input['case_id'];

// Verify case exists
$case = dbFetchOne("SELECT id, case_number FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Validate case_provider_id if provided
$cpId = null;
$providerName = isset($input['provider_name']) ? sanitizeString($input['provider_name']) : null;
if (!empty($input['case_provider_id'])) {
    $cpId = (int)$input['case_provider_id'];
    $cp = dbFetchOne(
        "SELECT cp.id, p.name AS provider_name
         FROM case_providers cp
         JOIN providers p ON cp.provider_id = p.id
         WHERE cp.id = ? AND cp.case_id = ?",
        [$cpId, $caseId]
    );
    if (!$cp) {
        errorResponse('Case provider not found', 404);
    }
    // Auto-fill provider name from linked provider
    if (!$providerName) {
        $providerName = $cp['provider_name'];
    }
}

// Validate expense_category
$category = $input['expense_category'] ?? 'mr_cost';
if (!validateEnum($category, ['mr_cost', 'litigation', 'other'])) {
    errorResponse('Invalid expense_category');
}

// Validate payment_type if provided
$paymentType = null;
if (!empty($input['payment_type'])) {
    $paymentType = $input['payment_type'];
    if (!validateEnum($paymentType, ['check', 'card', 'cash', 'wire', 'other'])) {
        errorResponse('Invalid payment_type');
    }
}

// Validate payment_date if provided
if (!empty($input['payment_date']) && !validateDate($input['payment_date'])) {
    errorResponse('Invalid payment_date format');
}

$data = [
    'case_id' => $caseId,
    'case_provider_id' => $cpId,
    'expense_category' => $category,
    'provider_name' => $providerName,
    'description' => isset($input['description']) ? sanitizeString($input['description']) : null,
    'billed_amount' => round((float)($input['billed_amount'] ?? 0), 2),
    'paid_amount' => round((float)($input['paid_amount'] ?? 0), 2),
    'payment_type' => $paymentType,
    'check_number' => isset($input['check_number']) ? sanitizeString($input['check_number']) : null,
    'payment_date' => !empty($input['payment_date']) ? $input['payment_date'] : null,
    'paid_by' => !empty($input['paid_by']) ? (int)$input['paid_by'] : null,
    'receipt_document_id' => !empty($input['receipt_document_id']) ? (int)$input['receipt_document_id'] : null,
    'notes' => isset($input['notes']) ? sanitizeString($input['notes']) : null,
    'created_by' => $userId
];

$newId = dbInsert('mr_fee_payments', $data);

// Sync MBDS office_paid if linked to a provider
if ($cpId) {
    syncMbdsOfficePaid($cpId);
}

logActivity($userId, 'payment_created', 'mr_fee_payment', $newId, [
    'case_id' => $caseId,
    'case_number' => $case['case_number'],
    'amount' => $data['paid_amount'],
    'provider_name' => $providerName
]);

$record = dbFetchOne("SELECT * FROM mr_fee_payments WHERE id = ?", [$newId]);
successResponse($record, 'Payment logged successfully');

/**
 * Sync MBDS office_paid total for a case_provider_id
 */
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
