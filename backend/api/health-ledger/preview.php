<?php
// GET /api/health-ledger/preview/{id} - Preview letter for a request
$userId = requireAuth();
$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) errorResponse('Request ID is required');

$data = getHealthLedgerLetterData($requestId);
if (!$data) errorResponse('Request not found', 404);

$html = renderHealthLedgerLetter($data);

successResponse([
    'request_id' => (int)$data['request_id'],
    'method' => $data['request_method'],
    'recipient' => $data['sent_to'] ?: ($data['request_method'] === 'email'
        ? $data['carrier_contact_email']
        : $data['carrier_contact_fax']),
    'carrier' => $data['insurance_carrier'],
    'client_name' => $data['client_name'],
    'letter_html' => $html,
]);
