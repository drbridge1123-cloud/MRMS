<?php
// GET /api/insurance-companies/export - Export insurance companies as CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$headers = ['name', 'type', 'phone', 'fax', 'email', 'address', 'city', 'state', 'zip', 'website', 'notes'];

// Template only (empty CSV with headers)
if (!empty($_GET['template'])) {
    outputCSV('insurance_companies_template.csv', $headers, []);
}

$typeLabels = [
    'auto' => 'Auto',
    'health' => 'Health',
    'workers_comp' => 'Workers Comp',
    'liability' => 'Liability',
    'um_uim' => 'UM/UIM',
    'government' => 'Government',
    'other' => 'Other',
];

$where = '1=1';
$params = [];

if (!empty($_GET['type'])) {
    if (validateEnum($_GET['type'], array_keys($typeLabels))) {
        $where .= ' AND type = ?';
        $params[] = $_GET['type'];
    }
}

if (!empty($_GET['search'])) {
    $where .= ' AND name LIKE ?';
    $params[] = '%' . $_GET['search'] . '%';
}

$rows = dbFetchAll(
    "SELECT name, type, phone, fax, email, address, city, state, zip, website, notes
     FROM insurance_companies
     WHERE {$where}
     ORDER BY name ASC",
    $params
);

foreach ($rows as &$r) {
    $r['type'] = $typeLabels[$r['type']] ?? $r['type'];
}
unset($r);

$filename = 'insurance_companies_' . date('Ymd') . '.csv';
outputCSV($filename, $headers, $rows);
