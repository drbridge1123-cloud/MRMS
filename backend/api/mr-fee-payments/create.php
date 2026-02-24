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

// Common field values
$description = isset($input['description']) ? sanitizeString($input['description']) : null;
$billedAmount = round((float)($input['billed_amount'] ?? 0), 2);
$paidAmount = round((float)($input['paid_amount'] ?? 0), 2);
$checkNumber = isset($input['check_number']) ? sanitizeString($input['check_number']) : null;
$paymentDate = !empty($input['payment_date']) ? $input['payment_date'] : null;
$paidDate = !empty($input['paid_date']) ? $input['paid_date'] : null;
$paidBy = !empty($input['paid_by']) ? (int)$input['paid_by'] : null;
$receiptDocId = !empty($input['receipt_document_id']) ? (int)$input['receipt_document_id'] : null;
$notes = isset($input['notes']) ? sanitizeString($input['notes']) : null;

// ── Split mode: create one row per case ──
$splitCaseIds = $input['split_case_ids'] ?? null;
$isSplit = !empty($splitCaseIds) && is_array($splitCaseIds) && count($splitCaseIds) > 1
           && $category === 'litigation';

if ($isSplit) {
    $splitCaseIds = array_map('intval', $splitCaseIds);
    $splitCount = count($splitCaseIds);

    // Validate all case_ids share the same case_number
    $placeholders = implode(',', array_fill(0, $splitCount, '?'));
    $siblingCases = dbFetchAll(
        "SELECT id, case_number FROM cases WHERE id IN ($placeholders)",
        $splitCaseIds
    );
    $uniqueNumbers = array_unique(array_column($siblingCases, 'case_number'));
    if (count($uniqueNumbers) !== 1 || $uniqueNumbers[0] !== $case['case_number']) {
        errorResponse('All split cases must share the same case number');
    }

    $splitGroupId = bin2hex(random_bytes(16));
    $perBilled = round($billedAmount / $splitCount, 2);
    $perPaid = round($paidAmount / $splitCount, 2);
    $remainBilled = round($billedAmount - ($perBilled * $splitCount), 2);
    $remainPaid = round($paidAmount - ($perPaid * $splitCount), 2);

    $pdo = getDBConnection();
    $pdo->beginTransaction();

    try {
        $createdIds = [];
        foreach ($splitCaseIds as $idx => $splitCaseId) {
            $rowData = [
                'case_id' => $splitCaseId,
                'case_provider_id' => null,
                'expense_category' => 'litigation',
                'provider_name' => $providerName,
                'description' => $description,
                'billed_amount' => $perBilled + ($idx === 0 ? $remainBilled : 0),
                'paid_amount' => $perPaid + ($idx === 0 ? $remainPaid : 0),
                'payment_type' => $paymentType,
                'check_number' => $checkNumber,
                'payment_date' => $paymentDate,
                'paid_date' => $paidDate,
                'paid_by' => $paidBy,
                'receipt_document_id' => $receiptDocId,
                'notes' => $notes,
                'split_group_id' => $splitGroupId,
                'split_total' => $billedAmount,
                'split_count' => $splitCount,
                'created_by' => $userId
            ];
            $createdIds[] = dbInsert('mr_fee_payments', $rowData);
        }
        $pdo->commit();

        logActivity($userId, 'payment_split_created', 'mr_fee_payment', $createdIds[0], [
            'case_number' => $case['case_number'],
            'split_count' => $splitCount,
            'total_amount' => $billedAmount,
            'split_group_id' => $splitGroupId
        ]);

        successResponse([
            'split_group_id' => $splitGroupId,
            'created_count' => count($createdIds),
            'per_person_amount' => $perBilled
        ], "Cost split across {$splitCount} cases");
    } catch (Exception $e) {
        $pdo->rollBack();
        errorResponse('Failed to create split payments: ' . $e->getMessage(), 500);
    }
    return;
}

// ── Single insert (existing flow) ──
$data = [
    'case_id' => $caseId,
    'case_provider_id' => $cpId,
    'expense_category' => $category,
    'provider_name' => $providerName,
    'description' => $description,
    'billed_amount' => $billedAmount,
    'paid_amount' => $paidAmount,
    'payment_type' => $paymentType,
    'check_number' => $checkNumber,
    'payment_date' => $paymentDate,
    'paid_date' => $paidDate,
    'paid_by' => $paidBy,
    'receipt_document_id' => $receiptDocId,
    'notes' => $notes,
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
