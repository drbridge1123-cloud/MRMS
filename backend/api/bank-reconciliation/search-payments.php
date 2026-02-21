<?php
// GET /api/bank-reconciliation/search-payments?q=&amount=&check_number=
// Search unmatched payments for manual matching
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAdmin();

$conditions = [
    'p.id NOT IN (SELECT matched_payment_id FROM bank_statement_entries WHERE matched_payment_id IS NOT NULL)'
];
$params = [];

if (!empty($_GET['q'])) {
    $q = '%' . sanitizeString($_GET['q']) . '%';
    $conditions[] = '(p.provider_name LIKE ? OR p.description LIKE ? OR p.check_number LIKE ? OR c.case_number LIKE ?)';
    $params = array_merge($params, [$q, $q, $q, $q]);
}

if (!empty($_GET['amount'])) {
    $amt = round((float)$_GET['amount'], 2);
    // Allow a small tolerance (exact or within $0.01)
    $conditions[] = 'ABS(p.paid_amount - ?) <= 0.01';
    $params[] = $amt;
}

if (!empty($_GET['check_number'])) {
    $conditions[] = 'p.check_number = ?';
    $params[] = sanitizeString($_GET['check_number']);
}

$whereClause = 'WHERE ' . implode(' AND ', $conditions);

$rows = dbFetchAll(
    "SELECT p.id, p.case_id, p.provider_name, p.description, p.paid_amount,
            p.payment_type, p.check_number, p.payment_date,
            c.case_number,
            c.client_name,
            u.full_name AS paid_by_name
     FROM mr_fee_payments p
     LEFT JOIN cases c ON p.case_id = c.id
     LEFT JOIN users u ON p.paid_by = u.id
     {$whereClause}
     ORDER BY p.payment_date DESC
     LIMIT 20",
    $params
);

successResponse($rows);
