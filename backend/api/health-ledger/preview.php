<?php
// GET /api/health-ledger/preview/{id} - Preview letter for a request
$userId = requireAuth();
$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) errorResponse('Request ID is required');

$data = getHealthLedgerLetterData($requestId);
if (!$data) errorResponse('Request not found', 404);

// Add sender info for template placeholders
$senderInfo = dbFetchOne("SELECT full_name, smtp_email FROM users WHERE id = ?", [$userId]);
if ($senderInfo) {
    $data['sender_name'] = $senderInfo['full_name'] ?? '';
    $data['sender_email'] = $senderInfo['smtp_email'] ?? '';
}

// Render via template or fallback
$subject = '';
if (!empty($data['template_id'])) {
    $result = renderLetterFromTemplate($data['template_id'], $data);
    if (is_array($result)) {
        $html = $result['html'];
        $subject = $result['subject'];
    } else {
        $html = $result;
    }
} else {
    $html = renderHealthLedgerLetter($data);
}

successResponse([
    'request_id' => (int)$data['request_id'],
    'method' => $data['request_method'],
    'recipient' => $data['sent_to'] ?: ($data['request_method'] === 'email'
        ? ($data['carrier_contact_email'] ?? '')
        : ($data['carrier_contact_fax'] ?? '')),
    'carrier' => $data['insurance_carrier'] ?? '',
    'client_name' => $data['client_name'],
    'subject' => $subject,
    'letter_html' => $html,
]);
