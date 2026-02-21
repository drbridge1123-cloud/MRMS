<?php
// GET /api/mr-fee-payments/summary?case_id=X
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

if (empty($_GET['case_id'])) {
    errorResponse('case_id is required');
}

$caseId = (int)$_GET['case_id'];

// Per-provider totals
$providerTotals = dbFetchAll(
    "SELECT p.case_provider_id,
            COALESCE(p.provider_name, prov.name, 'Unknown') AS provider_name,
            SUM(p.billed_amount) AS total_billed,
            SUM(p.paid_amount) AS total_paid,
            COUNT(*) AS payment_count
     FROM mr_fee_payments p
     LEFT JOIN case_providers cp ON p.case_provider_id = cp.id
     LEFT JOIN providers prov ON cp.provider_id = prov.id
     WHERE p.case_id = ? AND p.case_provider_id IS NOT NULL
     GROUP BY p.case_provider_id, COALESCE(p.provider_name, prov.name, 'Unknown')
     ORDER BY provider_name",
    [$caseId]
);

// Per-category totals
$categoryTotals = dbFetchAll(
    "SELECT expense_category,
            SUM(billed_amount) AS total_billed,
            SUM(paid_amount) AS total_paid,
            COUNT(*) AS payment_count
     FROM mr_fee_payments
     WHERE case_id = ?
     GROUP BY expense_category",
    [$caseId]
);

// Grand totals
$grandTotal = dbFetchOne(
    "SELECT COALESCE(SUM(billed_amount), 0) AS total_billed,
            COALESCE(SUM(paid_amount), 0) AS total_paid,
            COUNT(*) AS payment_count
     FROM mr_fee_payments
     WHERE case_id = ?",
    [$caseId]
);

// Per-staff totals
$staffTotals = dbFetchAll(
    "SELECT p.paid_by,
            u.full_name AS staff_name,
            SUM(p.paid_amount) AS total_paid,
            COUNT(*) AS payment_count
     FROM mr_fee_payments p
     LEFT JOIN users u ON p.paid_by = u.id
     WHERE p.case_id = ? AND p.paid_by IS NOT NULL
     GROUP BY p.paid_by, u.full_name
     ORDER BY total_paid DESC",
    [$caseId]
);

successResponse([
    'provider_totals' => $providerTotals,
    'category_totals' => $categoryTotals,
    'staff_totals' => $staffTotals,
    'grand_total' => [
        'billed' => round((float)$grandTotal['total_billed'], 2),
        'paid' => round((float)$grandTotal['total_paid'], 2),
        'count' => (int)$grandTotal['payment_count']
    ]
]);
