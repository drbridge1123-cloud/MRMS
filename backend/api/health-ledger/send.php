<?php
// POST /api/health-ledger/send/{id} - Send a request via email or fax
if ($method !== 'POST') errorResponse('Method not allowed', 405);

$userId = requireAuth();
$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) errorResponse('Request ID is required');

$input = getInput();

$letterData = getHealthLedgerLetterData($requestId);
if (!$letterData) errorResponse('Request not found', 404);

if (!in_array($letterData['request_method'], ['email', 'fax'])) {
    errorResponse('Only email and fax requests can be sent through the system');
}

if ($letterData['send_status'] === 'sent') {
    errorResponse('This request has already been sent');
}

// Determine recipient
$recipient = !empty($input['recipient'])
    ? sanitizeString($input['recipient'])
    : ($letterData['sent_to'] ?: ($letterData['request_method'] === 'email'
        ? $letterData['carrier_contact_email']
        : $letterData['carrier_contact_fax']));

if (empty($recipient)) {
    $label = $letterData['request_method'] === 'email' ? 'email address' : 'fax number';
    errorResponse("No recipient {$label} specified");
}

// Add sender info for template placeholders
$senderInfo = dbFetchOne("SELECT full_name, smtp_email FROM users WHERE id = ?", [$userId]);
if ($senderInfo) {
    $letterData['sender_name'] = $senderInfo['full_name'] ?? '';
    $letterData['sender_email'] = $senderInfo['smtp_email'] ?? '';
}

// Render letter via template or fallback
$subject = '';
if (!empty($letterData['template_id'])) {
    $result = renderLetterFromTemplate($letterData['template_id'], $letterData);
    if (is_array($result)) {
        $html = $result['html'];
        $subject = $result['subject'];
    } else {
        $html = $result;
    }
} else {
    $html = renderHealthLedgerLetter($letterData);
}

// Generate PDF version of the letter
require_once __DIR__ . '/../../helpers/pdf-generator.php';
$letterPdfPath = saveLetterPDF($html, $letterData['case_number'] ?? '', $letterData['insurance_carrier'] ?? '');

// Load attachments
$attachments = [];
if ($letterPdfPath) {
    $attachments[] = [
        'path' => $letterPdfPath,
        'name' => 'Health_Ledger_Request.pdf'
    ];
}

// Load additional document attachments
$attachmentRecords = dbFetchAll(
    "SELECT cd.file_path, cd.original_file_name
     FROM hl_request_attachments hra
     JOIN case_documents cd ON hra.case_document_id = cd.id
     WHERE hra.hl_request_id = ?",
    [$requestId]
);
foreach ($attachmentRecords as $att) {
    $fullPath = __DIR__ . '/../../../storage/' . $att['file_path'];
    if (file_exists($fullPath)) {
        $attachments[] = [
            'path' => $fullPath,
            'name' => $att['original_file_name']
        ];
    }
}

// Mark as sending
dbUpdate('hl_requests', [
    'send_status' => 'sending',
    'sent_to' => $recipient
], 'id = ?', [$requestId]);

// Send
$result = ['success' => false, 'error' => 'Unknown method'];

if ($letterData['request_method'] === 'email') {
    if (empty($subject)) {
        $subject = 'Health Insurance Ledger Request - ' . $letterData['client_name'];
        if (!empty($letterData['doi'])) {
            $subject .= ' (DOI: ' . date('m/d/Y', strtotime($letterData['doi'])) . ')';
        }
    }
    $emailOptions = [];
    $sender = dbFetchOne("SELECT full_name, smtp_email, smtp_app_password FROM users WHERE id = ?", [$userId]);
    if ($sender && !empty($sender['smtp_email']) && !empty($sender['smtp_app_password'])) {
        $emailOptions['smtp_email'] = $sender['smtp_email'];
        $emailOptions['smtp_password'] = $sender['smtp_app_password'];
        $emailOptions['from_name'] = $sender['full_name'];
    }
    if (!empty($attachments)) {
        $emailOptions['attachments'] = $attachments;
    }
    $result = sendEmail($recipient, $subject, $html, $emailOptions);
} elseif ($letterData['request_method'] === 'fax') {
    $result = sendFax($recipient, $html, ['attachments' => $attachments]);
}

// Log
dbInsert('send_log', [
    'record_request_id' => $requestId,
    'send_method'       => $letterData['request_method'],
    'recipient'         => $recipient,
    'status'            => $result['success'] ? 'success' : 'failed',
    'external_id'       => $result['message_id'] ?? $result['fax_id'] ?? null,
    'error_message'     => $result['error'] ?? null,
    'sent_by'           => $userId
]);

$current = dbFetchOne("SELECT send_attempts FROM hl_requests WHERE id = ?", [$requestId]);
$attempts = ($current['send_attempts'] ?? 0) + 1;

if ($result['success']) {
    dbUpdate('hl_requests', [
        'send_status'   => 'sent',
        'sent_at'       => date('Y-m-d H:i:s'),
        'send_error'    => null,
        'send_attempts' => $attempts,
        'letter_html'   => $html
    ], 'id = ?', [$requestId]);

    logActivity($userId, 'hl_request_sent', 'hl_request', $requestId, [
        'method' => $letterData['request_method'],
        'recipient' => $recipient
    ]);

    successResponse([
        'send_status' => 'sent',
        'sent_at' => date('Y-m-d H:i:s')
    ], 'Request sent via ' . $letterData['request_method']);
} else {
    dbUpdate('hl_requests', [
        'send_status'   => 'failed',
        'send_error'    => $result['error'],
        'send_attempts' => $attempts,
        'letter_html'   => $html
    ], 'id = ?', [$requestId]);

    logActivity($userId, 'hl_request_send_failed', 'hl_request', $requestId, [
        'method' => $letterData['request_method'],
        'error' => $result['error']
    ]);

    errorResponse('Failed to send: ' . $result['error'], 422);
}
