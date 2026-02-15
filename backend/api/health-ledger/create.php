<?php
// POST /api/health-ledger - Create a new health ledger item
$userId = requireAuth();
$input = getInput();

$errors = validateRequired($input, ['client_name', 'insurance_carrier']);
if ($errors) errorResponse(implode(', ', $errors));

$caseId = null;
$caseNumber = sanitizeString($input['case_number'] ?? '');
if ($caseNumber) {
    $case = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$caseNumber]);
    if ($case) $caseId = (int)$case['id'];
}

$id = dbInsert('health_ledger_items', [
    'case_id' => $caseId,
    'case_number' => $caseNumber ?: null,
    'client_name' => sanitizeString($input['client_name']),
    'insurance_carrier' => sanitizeString($input['insurance_carrier']),
    'carrier_contact_email' => sanitizeString($input['carrier_contact_email'] ?? ''),
    'carrier_contact_fax' => sanitizeString($input['carrier_contact_fax'] ?? ''),
    'overall_status' => 'not_started',
    'assigned_to' => !empty($input['assigned_to']) ? (int)$input['assigned_to'] : null,
    'note' => sanitizeString($input['note'] ?? ''),
]);

logActivity($userId, 'hl_item_created', 'health_ledger_item', $id, [
    'client_name' => $input['client_name'],
    'carrier' => $input['insurance_carrier']
]);

$record = dbFetchOne("SELECT * FROM health_ledger_items WHERE id = ?", [$id]);
successResponse($record, 'Item created');
