<?php
// PUT /api/mbds/lines/{id} - Update a single MBDS line
$userId = requireAuth();

$lineId = (int)($_GET['id'] ?? 0);
if (!$lineId) {
    errorResponse('Line ID is required', 400);
}

$line = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);
if (!$line) {
    errorResponse('Line not found', 404);
}

$input = getInput();
$updateData = [];

// Monetary fields
$moneyFields = ['charges', 'pip1_amount', 'pip2_amount', 'health1_amount', 'health2_amount', 'health3_amount', 'discount', 'office_paid', 'client_paid'];
foreach ($moneyFields as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = round((float)($input[$field] ?? 0), 2);
    }
}

// Text fields
foreach (['provider_name', 'treatment_dates', 'visits', 'note'] as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = $input[$field] ? sanitizeString($input[$field]) : null;
    }
}

if (array_key_exists('sort_order', $input)) {
    $updateData['sort_order'] = (int)$input['sort_order'];
}

// Auto-calculate balance
$charges = $updateData['charges'] ?? (float)$line['charges'];
$pip1 = $updateData['pip1_amount'] ?? (float)$line['pip1_amount'];
$pip2 = $updateData['pip2_amount'] ?? (float)$line['pip2_amount'];
$h1 = $updateData['health1_amount'] ?? (float)$line['health1_amount'];
$h2 = $updateData['health2_amount'] ?? (float)$line['health2_amount'];
$h3 = $updateData['health3_amount'] ?? (float)$line['health3_amount'];
$disc = $updateData['discount'] ?? (float)$line['discount'];
$office = $updateData['office_paid'] ?? (float)$line['office_paid'];
$client = $updateData['client_paid'] ?? (float)$line['client_paid'];

$updateData['balance'] = round($charges - $pip1 - $pip2 - $h1 - $h2 - $h3 - $disc - $office - $client, 2);

if (!empty($updateData)) {
    dbUpdate('mbds_lines', $updateData, 'id = ?', [$lineId]);
}

$updated = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);
$updated['charges'] = (float)$updated['charges'];
$updated['pip1_amount'] = (float)$updated['pip1_amount'];
$updated['pip2_amount'] = (float)$updated['pip2_amount'];
$updated['health1_amount'] = (float)$updated['health1_amount'];
$updated['health2_amount'] = (float)$updated['health2_amount'];
$updated['health3_amount'] = (float)$updated['health3_amount'];
$updated['discount'] = (float)$updated['discount'];
$updated['office_paid'] = (float)$updated['office_paid'];
$updated['client_paid'] = (float)$updated['client_paid'];
$updated['balance'] = (float)$updated['balance'];

successResponse($updated);
