<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    errorResponse('Request ID is required');
}

$letterData = getRequestLetterData($requestId);
if (!$letterData) {
    errorResponse('Request not found', 404);
}

if (!in_array($letterData['request_method'], ['email', 'fax'])) {
    errorResponse('Only email and fax requests can be previewed');
}

$html = renderRequestLetter($letterData);

$recipient = $letterData['sent_to']
    ?: ($letterData['request_method'] === 'email'
        ? $letterData['provider_email']
        : $letterData['provider_fax']);

successResponse([
    'request_id'    => (int)$requestId,
    'method'        => $letterData['request_method'],
    'recipient'     => $recipient ?: '',
    'provider_name' => $letterData['provider_name'],
    'client_name'   => $letterData['client_name'],
    'send_status'   => $letterData['send_status'],
    'letter_html'   => $html
]);
