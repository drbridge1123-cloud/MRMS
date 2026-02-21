<?php
// GET /api/mr-fee-payments?case_id=X[&case_provider_id=Y][&category=Z]
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

if (empty($_GET['case_id'])) {
    errorResponse('case_id is required');
}

$caseId = (int)$_GET['case_id'];
$conditions = ['p.case_id = ?'];
$params = [$caseId];

if (!empty($_GET['case_provider_id'])) {
    $conditions[] = 'p.case_provider_id = ?';
    $params[] = (int)$_GET['case_provider_id'];
}

if (!empty($_GET['category'])) {
    $conditions[] = 'p.expense_category = ?';
    $params[] = sanitizeString($_GET['category']);
}

$whereClause = implode(' AND ', $conditions);

$rows = dbFetchAll(
    "SELECT p.*,
            u_paid.full_name AS paid_by_name,
            u_created.full_name AS created_by_name,
            prov.name AS linked_provider_name
     FROM mr_fee_payments p
     LEFT JOIN users u_paid ON p.paid_by = u_paid.id
     LEFT JOIN users u_created ON p.created_by = u_created.id
     LEFT JOIN case_providers cp ON p.case_provider_id = cp.id
     LEFT JOIN providers prov ON cp.provider_id = prov.id
     WHERE {$whereClause}
     ORDER BY p.payment_date DESC, p.created_at DESC",
    $params
);

// Compute totals
$totalBilled = 0;
$totalPaid = 0;
foreach ($rows as $row) {
    $totalBilled += (float)$row['billed_amount'];
    $totalPaid += (float)$row['paid_amount'];
}

successResponse([
    'payments' => $rows,
    'total_billed' => round($totalBilled, 2),
    'total_paid' => round($totalPaid, 2),
    'count' => count($rows)
]);
