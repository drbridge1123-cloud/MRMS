<?php
// GET /api/providers/export - Export providers as CSV
$userId = requireAuth();
require_once __DIR__ . '/../../helpers/csv.php';

$headers = ['name', 'type', 'address', 'phone', 'fax', 'email', 'portal_url',
            'preferred_method', 'difficulty_level', 'uses_third_party',
            'third_party_name', 'third_party_contact', 'notes'];

// Template only (empty CSV with headers)
if (!empty($_GET['template'])) {
    outputCSV('providers_template.csv', $headers, []);
}

// Build WHERE (same filters as list.php)
$where = '1=1';
$params = [];

if (!empty($_GET['type'])) {
    $allowedTypes = ['hospital', 'er', 'chiro', 'imaging', 'physician', 'surgery_center', 'pharmacy', 'other'];
    if (validateEnum($_GET['type'], $allowedTypes)) {
        $where .= ' AND p.type = ?';
        $params[] = $_GET['type'];
    }
}

if (!empty($_GET['difficulty_level'])) {
    $allowedLevels = ['easy', 'medium', 'hard'];
    if (validateEnum($_GET['difficulty_level'], $allowedLevels)) {
        $where .= ' AND p.difficulty_level = ?';
        $params[] = $_GET['difficulty_level'];
    }
}

if (!empty($_GET['search'])) {
    $where .= ' AND p.name LIKE ?';
    $params[] = '%' . $_GET['search'] . '%';
}

$providers = dbFetchAll(
    "SELECT p.name, p.type, p.address, p.phone, p.fax, p.email, p.portal_url,
            p.preferred_method, p.difficulty_level, p.uses_third_party,
            p.third_party_name, p.third_party_contact, p.notes
     FROM providers p
     WHERE {$where}
     ORDER BY p.name ASC",
    $params
);

// Convert uses_third_party to yes/no for readability
foreach ($providers as &$p) {
    $p['uses_third_party'] = $p['uses_third_party'] ? 'yes' : 'no';
}
unset($p);

$filename = 'providers_' . date('Ymd') . '.csv';
outputCSV($filename, $headers, $providers);
