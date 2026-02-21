<?php
// POST /api/requests/bulk-send - Send multiple draft requests
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$input = getInput();

// Validate required fields
if (empty($input['request_ids']) || !is_array($input['request_ids'])) {
    errorResponse('request_ids must be a non-empty array', 400);
}

$requestIds = array_map('intval', $input['request_ids']);

// Load all requests
$placeholders = implode(',', array_fill(0, count($requestIds), '?'));
$requests = dbFetchAll(
    "SELECT id, send_status, request_method, sent_to FROM record_requests WHERE id IN ({$placeholders})",
    $requestIds
);

if (count($requests) !== count($requestIds)) {
    errorResponse('One or more requests not found', 404);
}

// Check none are already sent
$alreadySent = array_filter($requests, function($r) {
    return $r['send_status'] === 'sent';
});

if (!empty($alreadySent)) {
    $sentIds = implode(', ', array_column($alreadySent, 'id'));
    errorResponse("Some requests already sent: {$sentIds}. Skipping those.", 422);
}

// Filter to only email/fax requests with recipients
$sendableRequests = array_filter($requests, function($r) {
    return in_array($r['request_method'], ['email', 'fax']) && !empty($r['sent_to']);
});

if (empty($sendableRequests)) {
    errorResponse('No sendable requests (must be email/fax with recipient)', 400);
}

// Send each request
$results = [];
$sentCount = 0;
$failedCount = 0;

foreach ($sendableRequests as $request) {
    $requestId = (int)$request['id'];
    $recipient = $request['sent_to'];

    // Load letter data
    $letterData = getRequestLetterData($requestId);
    if (!$letterData) {
        $results[] = [
            'request_id' => $requestId,
            'status' => 'failed',
            'error' => 'Letter data not found'
        ];
        $failedCount++;
        continue;
    }

    // Render letter
    $html = renderRequestLetter($letterData);

    // Load attachments (HIPAA, releases, etc.)
    $attachments = [];

    $attachmentRecords = dbFetchAll(
        "SELECT cd.file_path, cd.original_file_name
         FROM request_attachments ra
         JOIN case_documents cd ON ra.case_document_id = cd.id
         WHERE ra.record_request_id = ?",
        [$requestId]
    );

    foreach ($attachmentRecords as $att) {
        $fullPath = __DIR__ . '/../../storage/' . $att['file_path'];
        if (file_exists($fullPath)) {
            $attachments[] = [
                'path' => $fullPath,
                'name' => $att['original_file_name']
            ];
        }
    }

    // Mark as sending
    dbUpdate('record_requests', ['send_status' => 'sending'], 'id = ?', [$requestId]);

    // Send
    $result = ['success' => false, 'error' => 'Unknown method'];

    if ($request['request_method'] === 'email') {
        $doiFormatted = !empty($letterData['doi']) ? date('m/d/Y', strtotime($letterData['doi'])) : '';
        $subject = 'Medical Records Request - ' . $letterData['client_name'];
        if ($doiFormatted) {
            $subject .= ' (DOI: ' . $doiFormatted . ')';
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
    } elseif ($request['request_method'] === 'fax') {
        // Generate PDF version of the letter for fax (fax needs PDF, not HTML)
        require_once __DIR__ . '/../../helpers/pdf-generator.php';
        $letterPdfPath = saveLetterPDF($html, $letterData['case_number'] ?? '', $letterData['provider_name'] ?? '');
        $faxAttachments = [];
        if ($letterPdfPath) {
            $faxAttachments[] = ['path' => $letterPdfPath, 'name' => 'Medical_Records_Request.pdf'];
        }
        $faxAttachments = array_merge($faxAttachments, $attachments);
        $result = sendFax($recipient, $html, ['attachments' => $faxAttachments]);
    }

    // Log the attempt
    dbInsert('send_log', [
        'record_request_id' => $requestId,
        'send_method' => $request['request_method'],
        'recipient' => $recipient,
        'status' => $result['success'] ? 'success' : 'failed',
        'external_id' => $result['message_id'] ?? $result['fax_id'] ?? null,
        'error_message' => $result['error'] ?? null,
        'sent_by' => $userId
    ]);

    // Get current attempts
    $current = dbFetchOne("SELECT send_attempts FROM record_requests WHERE id = ?", [$requestId]);
    $attempts = (($current['send_attempts'] ?? 0) + 1);

    if ($result['success']) {
        dbUpdate('record_requests', [
            'send_status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'send_error' => null,
            'send_attempts' => $attempts,
            'letter_html' => $html
        ], 'id = ?', [$requestId]);

        logActivity($userId, 'request_delivered', 'record_request', $requestId, [
            'method' => $request['request_method'],
            'recipient' => $recipient,
            'bulk' => true
        ]);

        // Auto-update provider status based on request type
        if (!empty($letterData['case_provider_id'])) {
            $autoStatus = match($letterData['request_type'] ?? '') {
                'initial' => 'requesting',
                'follow_up', 're_request', 'rfd' => 'follow_up',
                default => null,
            };
            if ($autoStatus) {
                dbUpdate('case_providers', ['overall_status' => $autoStatus], 'id = ?', [$letterData['case_provider_id']]);
            }
        }

        $results[] = [
            'request_id' => $requestId,
            'status' => 'sent'
        ];
        $sentCount++;
    } else {
        dbUpdate('record_requests', [
            'send_status' => 'failed',
            'send_error' => $result['error'],
            'send_attempts' => $attempts,
            'letter_html' => $html
        ], 'id = ?', [$requestId]);

        logActivity($userId, 'request_send_failed', 'record_request', $requestId, [
            'method' => $request['request_method'],
            'recipient' => $recipient,
            'error' => $result['error'],
            'bulk' => true
        ]);

        $results[] = [
            'request_id' => $requestId,
            'status' => 'failed',
            'error' => $result['error']
        ];
        $failedCount++;
    }
}

$message = "Sent {$sentCount} of " . count($sendableRequests) . " requests";
if ($failedCount > 0) {
    $message .= ". {$failedCount} failed";
}

successResponse([
    'sent' => $sentCount,
    'failed' => $failedCount,
    'results' => $results
], $message);
