<?php
// PUT /api/mr-fee-payments/{id}
if ($method !== 'PUT') {
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

$input = getInput();

// Validate expense_category if provided
if (isset($input['expense_category'])) {
    if (!validateEnum($input['expense_category'], ['mr_cost', 'litigation', 'other'])) {
        errorResponse('Invalid expense_category');
    }
}

// Validate payment_type if provided
if (isset($input['payment_type']) && $input['payment_type'] !== '') {
    if (!validateEnum($input['payment_type'], ['check', 'card', 'cash', 'wire', 'other'])) {
        errorResponse('Invalid payment_type');
    }
}

// Validate payment_date if provided
if (!empty($input['payment_date']) && !validateDate($input['payment_date'])) {
    errorResponse('Invalid payment_date format');
}

$data = [];

// Handle case_provider_id change
if (array_key_exists('case_provider_id', $input)) {
    $newCpId = !empty($input['case_provider_id']) ? (int)$input['case_provider_id'] : null;
    if ($newCpId) {
        $cp = dbFetchOne(
            "SELECT cp.id, p.name AS provider_name FROM case_providers cp JOIN providers p ON cp.provider_id = p.id WHERE cp.id = ? AND cp.case_id = ?",
            [$newCpId, $existing['case_id']]
        );
        if (!$cp) {
            errorResponse('Case provider not found', 404);
        }
        if (empty($input['provider_name'])) {
            $data['provider_name'] = $cp['provider_name'];
        }
    }
    $data['case_provider_id'] = $newCpId;
}

$allowedFields = [
    'expense_category', 'provider_name', 'description',
    'billed_amount', 'paid_amount', 'payment_type',
    'check_number', 'payment_date', 'paid_date', 'paid_by',
    'receipt_document_id', 'notes'
];

foreach ($allowedFields as $field) {
    if (array_key_exists($field, $input)) {
        if (in_array($field, ['billed_amount', 'paid_amount'])) {
            $data[$field] = round((float)$input[$field], 2);
        } elseif (in_array($field, ['paid_by', 'receipt_document_id'])) {
            $data[$field] = !empty($input[$field]) ? (int)$input[$field] : null;
        } elseif (in_array($field, ['payment_date', 'paid_date'])) {
            $data[$field] = !empty($input[$field]) ? $input[$field] : null;
        } elseif ($field === 'payment_type') {
            $data[$field] = !empty($input[$field]) ? $input[$field] : null;
        } elseif (in_array($field, ['provider_name', 'description', 'check_number', 'notes'])) {
            $data[$field] = isset($input[$field]) ? sanitizeString($input[$field]) : null;
        } else {
            $data[$field] = $input[$field];
        }
    }
}

if (empty($data)) {
    errorResponse('No fields to update');
}

dbUpdate('mr_fee_payments', $data, 'id = ?', [$paymentId]);

// Sync MBDS for old and new case_provider_id
$oldCpId = $existing['case_provider_id'];
if ($oldCpId) {
    syncMbdsOfficePaid((int)$oldCpId);
}
$newCpId = $data['case_provider_id'] ?? $oldCpId;
if ($newCpId && $newCpId != $oldCpId) {
    syncMbdsOfficePaid((int)$newCpId);
}

logActivity($userId, 'payment_updated', 'mr_fee_payment', $paymentId, [
    'case_id' => $existing['case_id']
]);

$record = dbFetchOne("SELECT * FROM mr_fee_payments WHERE id = ?", [$paymentId]);
successResponse($record, 'Payment updated successfully');

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
