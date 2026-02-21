<?php
// GET /api/bank-reconciliation?batch_id=&status=&search=&sort_by=&sort_dir=
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAdmin();

$conditions = [];
$params = [];

if (!empty($_GET['batch_id'])) {
    $conditions[] = 'b.batch_id = ?';
    $params[] = sanitizeString($_GET['batch_id']);
}

if (!empty($_GET['status'])) {
    $conditions[] = 'b.reconciliation_status = ?';
    $params[] = sanitizeString($_GET['status']);
}

if (!empty($_GET['date_from'])) {
    $conditions[] = 'b.transaction_date >= ?';
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $conditions[] = 'b.transaction_date <= ?';
    $params[] = $_GET['date_to'];
}

if (!empty($_GET['search'])) {
    $search = '%' . sanitizeString($_GET['search']) . '%';
    $conditions[] = '(b.description LIKE ? OR b.check_number LIKE ? OR b.reference_number LIKE ?)';
    $params = array_merge($params, [$search, $search, $search]);
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$allowedSorts = ['transaction_date', 'amount', 'check_number', 'reconciliation_status', 'imported_at'];
$sortBy = in_array($_GET['sort_by'] ?? '', $allowedSorts) ? $_GET['sort_by'] : 'transaction_date';
$sortDir = ($_GET['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(100, max(10, (int)($_GET['per_page'] ?? 50)));
$offset = ($page - 1) * $perPage;

$countRow = dbFetchOne(
    "SELECT COUNT(*) AS total FROM bank_statement_entries b {$whereClause}",
    $params
);
$total = (int)$countRow['total'];

$rows = dbFetchAll(
    "SELECT b.*,
            u_imported.full_name AS imported_by_name,
            u_matched.full_name AS matched_by_name,
            p.case_id AS matched_case_id,
            p.provider_name AS matched_provider_name,
            p.paid_amount AS matched_paid_amount,
            p.payment_date AS matched_payment_date,
            p.description AS matched_description,
            c.case_number AS matched_case_number
     FROM bank_statement_entries b
     LEFT JOIN users u_imported ON b.imported_by = u_imported.id
     LEFT JOIN users u_matched ON b.matched_by = u_matched.id
     LEFT JOIN mr_fee_payments p ON b.matched_payment_id = p.id
     LEFT JOIN cases c ON p.case_id = c.id
     {$whereClause}
     ORDER BY b.{$sortBy} {$sortDir}, b.id DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Summary
$summaryRow = dbFetchOne(
    "SELECT COUNT(*) AS total_count,
            COALESCE(SUM(b.amount), 0) AS total_amount,
            SUM(CASE WHEN b.reconciliation_status = 'matched' THEN 1 ELSE 0 END) AS matched_count,
            SUM(CASE WHEN b.reconciliation_status = 'unmatched' THEN 1 ELSE 0 END) AS unmatched_count,
            SUM(CASE WHEN b.reconciliation_status = 'ignored' THEN 1 ELSE 0 END) AS ignored_count,
            COALESCE(SUM(CASE WHEN b.reconciliation_status = 'matched' THEN b.amount ELSE 0 END), 0) AS matched_amount,
            COALESCE(SUM(CASE WHEN b.reconciliation_status = 'unmatched' THEN b.amount ELSE 0 END), 0) AS unmatched_amount
     FROM bank_statement_entries b
     {$whereClause}",
    $params
);

// Batch list for filter
$batches = dbFetchAll(
    "SELECT batch_id,
            MIN(imported_at) AS imported_at,
            COUNT(*) AS entry_count,
            u.full_name AS imported_by_name
     FROM bank_statement_entries b
     LEFT JOIN users u ON b.imported_by = u.id
     GROUP BY batch_id, u.full_name
     ORDER BY imported_at DESC"
);

jsonResponse([
    'success' => true,
    'data' => $rows,
    'pagination' => [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => (int)ceil($total / $perPage)
    ],
    'summary' => [
        'total_count' => (int)$summaryRow['total_count'],
        'total_amount' => round((float)$summaryRow['total_amount'], 2),
        'matched_count' => (int)$summaryRow['matched_count'],
        'unmatched_count' => (int)$summaryRow['unmatched_count'],
        'ignored_count' => (int)$summaryRow['ignored_count'],
        'matched_amount' => round((float)$summaryRow['matched_amount'], 2),
        'unmatched_amount' => round((float)$summaryRow['unmatched_amount'], 2),
    ],
    'batches' => $batches,
]);
