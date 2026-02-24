<?php
// GET /api/adjusters/export - Export adjusters as CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$headers = ['first_name', 'last_name', 'insurance_company', 'adjuster_type', 'title', 'phone', 'fax', 'email', 'is_active', 'notes'];

// Template only (empty CSV with headers)
if (!empty($_GET['template'])) {
    outputCSV('adjusters_template.csv', $headers, []);
}

$typeLabels = [
    'pip' => 'PIP',
    'um' => 'UM',
    'uim' => 'UIM',
    '3rd_party' => '3rd Party',
    'liability' => 'Liability',
    'pd' => 'PD',
    'bi' => 'BI',
];

$where = '1=1';
$params = [];

if (!empty($_GET['insurance_company_id'])) {
    $where .= ' AND a.insurance_company_id = ?';
    $params[] = (int)$_GET['insurance_company_id'];
}

if (!empty($_GET['adjuster_type'])) {
    if (validateEnum($_GET['adjuster_type'], array_keys($typeLabels))) {
        $where .= ' AND a.adjuster_type = ?';
        $params[] = $_GET['adjuster_type'];
    }
}

if (!empty($_GET['search'])) {
    $where .= ' AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ?)';
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$rows = dbFetchAll(
    "SELECT a.first_name, a.last_name, ic.name AS insurance_company,
            a.adjuster_type, a.title, a.phone, a.fax, a.email, a.is_active, a.notes
     FROM adjusters a
     LEFT JOIN insurance_companies ic ON a.insurance_company_id = ic.id
     WHERE {$where}
     ORDER BY a.last_name, a.first_name ASC",
    $params
);

foreach ($rows as &$r) {
    $r['adjuster_type'] = $typeLabels[$r['adjuster_type']] ?? $r['adjuster_type'];
    $r['is_active'] = $r['is_active'] ? 'yes' : 'no';
}
unset($r);

$filename = 'adjusters_' . date('Ymd') . '.csv';
outputCSV($filename, $headers, $rows);
