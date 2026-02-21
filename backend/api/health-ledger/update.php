<?php
// PUT /api/health-ledger/{id} - Update a health ledger item
$userId = requireAuth();
$id = (int)($_GET['id'] ?? 0);
if (!$id) errorResponse('ID is required');

$existing = dbFetchOne("SELECT * FROM health_ledger_items WHERE id = ?", [$id]);
if (!$existing) errorResponse('Item not found', 404);

$input = getInput();

$caseId = $existing['case_id'];
if (isset($input['case_number'])) {
    $caseNumber = sanitizeString($input['case_number']);
    $case = dbFetchOne("SELECT id FROM cases WHERE case_number = ?", [$caseNumber]);
    $caseId = $case ? (int)$case['id'] : null;
}

$data = [
    'case_id' => $caseId,
    'case_number' => isset($input['case_number']) ? (sanitizeString($input['case_number']) ?: null) : $existing['case_number'],
    'client_name' => sanitizeString($input['client_name'] ?? $existing['client_name']),
    'insurance_carrier' => sanitizeString($input['insurance_carrier'] ?? $existing['insurance_carrier']),
    'carrier_contact_email' => isset($input['carrier_contact_email']) ? sanitizeString($input['carrier_contact_email']) : $existing['carrier_contact_email'],
    'carrier_contact_fax' => isset($input['carrier_contact_fax']) ? sanitizeString($input['carrier_contact_fax']) : $existing['carrier_contact_fax'],
    'claim_number' => isset($input['claim_number']) ? (sanitizeString($input['claim_number']) ?: null) : $existing['claim_number'],
    'member_id' => isset($input['member_id']) ? (sanitizeString($input['member_id']) ?: null) : $existing['member_id'],
    'assigned_to' => isset($input['assigned_to']) ? ((int)$input['assigned_to'] ?: null) : $existing['assigned_to'],
    'note' => isset($input['note']) ? sanitizeString($input['note']) : $existing['note'],
];

if (!empty($input['overall_status'])) {
    $allowed = ['not_started', 'requesting', 'follow_up', 'received', 'done'];
    if (in_array($input['overall_status'], $allowed)) {
        $data['overall_status'] = $input['overall_status'];
    }
}

dbUpdate('health_ledger_items', $data, 'id = ?', [$id]);
logActivity($userId, 'hl_item_updated', 'health_ledger_item', $id, ['client_name' => $data['client_name']]);

successResponse(null, 'Item updated');
