<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    errorResponse('Request ID is required');
}

$input = getInput();

$letterData = getRequestLetterData($requestId);
if (!$letterData) {
    errorResponse('Request not found', 404);
}

if (!in_array($letterData['request_method'], ['email', 'fax'])) {
    errorResponse('Only email and fax requests can be sent through the system');
}

if ($letterData['send_status'] === 'sent') {
    errorResponse('This request has already been sent successfully');
}

// Determine recipient
$recipient = !empty($input['recipient'])
    ? sanitizeString($input['recipient'])
    : ($letterData['sent_to'] ?: ($letterData['request_method'] === 'email'
        ? $letterData['provider_email']
        : $letterData['provider_fax']));

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

// Check if user sent edited letter content
$userEditedHtml = !empty($input['letter_html']) ? $input['letter_html'] : null;
$userEditedSubject = isset($input['subject']) ? sanitizeString($input['subject']) : null;
$isEdited = false;

if ($userEditedHtml) {
    // Use user-edited HTML (sanitized)
    $html = sanitizeLetterHtml($userEditedHtml);
    $subject = $userEditedSubject ?: '';
    $isEdited = true;
} else {
    // Render letter from template (existing behavior)
    $subject = '';
    if (!empty($letterData['template_id'])) {
        $result = renderLetterFromTemplate($letterData['template_id'], $letterData);
        $html = $result['html'];
        $subject = $result['subject'];
    } else {
        $html = renderRequestLetter($letterData);
    }
    // Allow subject override even without body edit
    if ($userEditedSubject !== null) {
        $subject = $userEditedSubject;
        $isEdited = true;
    }
}

// Load attachments if this is an email request
$attachments = [];
if ($letterData['request_method'] === 'email') {
    $attachmentRecords = dbFetchAll(
        "SELECT cd.file_path, cd.original_file_name
         FROM request_attachments ra
         JOIN case_documents cd ON ra.case_document_id = cd.id
         WHERE ra.record_request_id = ?",
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
}

// Mark as sending
dbUpdate('record_requests', [
    'send_status' => 'sending',
    'sent_to' => $recipient
], 'id = ?', [$requestId]);

// Send
$result = ['success' => false, 'error' => 'Unknown method'];

if ($letterData['request_method'] === 'email') {
    // Use template subject if available, otherwise generate default
    if (empty($subject)) {
        $doiFormatted = !empty($letterData['doi']) ? date('m/d/Y', strtotime($letterData['doi'])) : '';
        $subject = 'Medical Records Request - ' . $letterData['client_name'];
        if ($doiFormatted) {
            $subject .= ' (DOI: ' . $doiFormatted . ')';
        }
    }
    // Use per-user SMTP if configured
    $emailOptions = [];
    $sender = dbFetchOne("SELECT full_name, smtp_email, smtp_app_password FROM users WHERE id = ?", [$userId]);
    if ($sender && !empty($sender['smtp_email']) && !empty($sender['smtp_app_password'])) {
        $emailOptions['smtp_email'] = $sender['smtp_email'];
        $emailOptions['smtp_password'] = $sender['smtp_app_password'];
        $emailOptions['from_name'] = $sender['full_name'];
    }
    // Add attachments if any
    if (!empty($attachments)) {
        $emailOptions['attachments'] = $attachments;
    }
    $result = sendEmail($recipient, $subject, $html, $emailOptions);
} elseif ($letterData['request_method'] === 'fax') {
    $result = sendFax($recipient, $html);
}

// Log the attempt
dbInsert('send_log', [
    'record_request_id' => $requestId,
    'send_method'       => $letterData['request_method'],
    'recipient'         => $recipient,
    'status'            => $result['success'] ? 'success' : 'failed',
    'external_id'       => $result['message_id'] ?? $result['fax_id'] ?? null,
    'error_message'     => $result['error'] ?? null,
    'sent_by'           => $userId
]);

// Get current attempts count
$current = dbFetchOne("SELECT send_attempts FROM record_requests WHERE id = ?", [$requestId]);
$attempts = (($current['send_attempts'] ?? 0) + 1);

if ($result['success']) {
    dbUpdate('record_requests', [
        'send_status'   => 'sent',
        'sent_at'       => date('Y-m-d H:i:s'),
        'send_error'    => null,
        'send_attempts' => $attempts,
        'letter_html'   => $html
    ], 'id = ?', [$requestId]);

    logActivity($userId, 'request_delivered', 'record_request', $requestId, [
        'method'    => $letterData['request_method'],
        'recipient' => $recipient,
        'edited'    => $isEdited
    ]);

    successResponse([
        'send_status' => 'sent',
        'sent_at'     => date('Y-m-d H:i:s')
    ], 'Request sent successfully via ' . $letterData['request_method']);
} else {
    dbUpdate('record_requests', [
        'send_status'   => 'failed',
        'send_error'    => $result['error'],
        'send_attempts' => $attempts,
        'letter_html'   => $html
    ], 'id = ?', [$requestId]);

    logActivity($userId, 'request_send_failed', 'record_request', $requestId, [
        'method'    => $letterData['request_method'],
        'recipient' => $recipient,
        'error'     => $result['error'],
        'edited'    => $isEdited
    ]);

    errorResponse('Failed to send: ' . $result['error'], 422);
}
