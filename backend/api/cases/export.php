<?php
// GET /api/cases/export - Export cases as CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$headers = ['case_number', 'client_name', 'client_dob', 'doi', 'attorney_name', 'status', 'notes'];

// Template only (empty CSV with headers)
if (!empty($_GET['template'])) {
    outputCSV('cases_template.csv', $headers, []);
}

// Build WHERE (same filters as list.php)
$where = ['1=1'];
$params = [];

if (!empty($_GET['status'])) {
    $allowedStatuses = ['collecting','verification','completed','rfd','final_verification','disbursement','accounting','closed'];
    if (validateEnum($_GET['status'], $allowedStatuses)) {
        $where[] = 'c.status = ?';
        $params[] = $_GET['status'];
    }
}

if (!empty($_GET['search'])) {
    $searchTerm = '%' . sanitizeString($_GET['search']) . '%';
    $where[] = '(c.client_name LIKE ? OR c.case_number LIKE ?)';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = implode(' AND ', $where);

$cases = dbFetchAll(
    "SELECT c.case_number, c.client_name, c.client_dob, c.doi,
            c.attorney_name, c.status, c.notes
     FROM cases c
     WHERE {$whereClause}
     ORDER BY c.case_number ASC",
    $params
);

$filename = 'cases_' . date('Ymd') . '.csv';
outputCSV($filename, $headers, $cases);
