<?php
/**
 * Send a fax using the configured fax service.
 *
 * @param string $toNumber  Fax number
 * @param string $htmlBody  HTML content to fax
 * @param array  $options   Optional: 'caller_id', 'header_text'
 * @return array ['success' => bool, 'fax_id' => string|null, 'error' => string|null]
 */
function sendFax($toNumber, $htmlBody, $options = []) {
    // Check fax configuration
    if (empty(FAX_API_KEY) || empty(FAX_API_SECRET)) {
        return [
            'success' => false,
            'fax_id'  => null,
            'error'   => 'Fax not configured. Please set FAX_API_KEY and FAX_API_SECRET in backend/config/email.php'
        ];
    }

    $toNumber = normalizePhoneNumber($toNumber);

    switch (FAX_SERVICE) {
        case 'phaxio':
            return sendFaxViaPhaxio($toNumber, $htmlBody, $options);
        case 'srfax':
            return sendFaxViaSRFax($toNumber, $htmlBody, $options);
        default:
            return ['success' => false, 'fax_id' => null, 'error' => 'Unknown fax service: ' . FAX_SERVICE];
    }
}

/**
 * Phaxio API implementation.
 */
function sendFaxViaPhaxio($toNumber, $htmlBody, $options = []) {
    $postFields = [
        'to'               => $toNumber,
        'string_data'      => $htmlBody,
        'string_data_type' => 'html',
        'caller_id'        => $options['caller_id'] ?? FAX_CALLER_ID,
    ];

    if (!empty(FAX_CALLBACK_URL)) {
        $postFields['callback_url'] = FAX_CALLBACK_URL;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => FAX_API_URL,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => SEND_TIMEOUT,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . base64_encode(FAX_API_KEY . ':' . FAX_API_SECRET)
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'fax_id' => null, 'error' => 'cURL error: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['success']) && $data['success']) {
        return [
            'success' => true,
            'fax_id'  => $data['data']['id'] ?? null,
            'error'   => null
        ];
    }

    return [
        'success' => false,
        'fax_id'  => null,
        'error'   => $data['message'] ?? ('Phaxio API error (HTTP ' . $httpCode . ')')
    ];
}

/**
 * SRFax placeholder - implement if switching services.
 */
function sendFaxViaSRFax($toNumber, $htmlBody, $options = []) {
    return ['success' => false, 'fax_id' => null, 'error' => 'SRFax adapter not implemented'];
}

/**
 * Normalize a phone/fax number to E.164 format for US numbers.
 */
function normalizePhoneNumber($number) {
    $digits = preg_replace('/[^0-9]/', '', $number);
    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }
    if (strlen($digits) === 11 && $digits[0] === '1') {
        return '+' . $digits;
    }
    return '+' . $digits;
}
