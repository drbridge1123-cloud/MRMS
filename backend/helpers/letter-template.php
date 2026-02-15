<?php
/**
 * Render a Medical Records Request Letter as HTML.
 *
 * @param array $data Letter data from getRequestLetterData()
 * @return string Full HTML document
 */
function renderRequestLetter($data) {
    $requestDate = !empty($data['request_date'])
        ? date('F j, Y', strtotime($data['request_date']))
        : date('F j, Y');
    $clientDob = !empty($data['client_dob'])
        ? date('m/d/Y', strtotime($data['client_dob']))
        : 'N/A';
    $doi = !empty($data['doi'])
        ? date('m/d/Y', strtotime($data['doi']))
        : 'N/A';
    $treatmentStart = !empty($data['treatment_start_date'])
        ? date('m/d/Y', strtotime($data['treatment_start_date']))
        : 'Date of Injury';
    $treatmentEnd = !empty($data['treatment_end_date'])
        ? date('m/d/Y', strtotime($data['treatment_end_date']))
        : 'Present';

    // Parse record types
    $recordTypeLabels = [
        'medical_records' => 'Complete Medical Records (including office/chart notes, diagnostic studies, and test results)',
        'billing'         => 'Itemized Billing Statements',
        'chart'           => 'Chart/Progress Notes',
        'imaging'         => 'Imaging Studies (X-rays, MRI, CT scans) and Radiology Reports',
        'op_report'       => 'Operative Reports',
    ];
    $requestedTypes = [];
    if (!empty($data['record_types'])) {
        foreach (explode(',', $data['record_types']) as $type) {
            $type = trim($type);
            if (isset($recordTypeLabels[$type])) {
                $requestedTypes[] = $recordTypeLabels[$type];
            }
        }
    }
    if (empty($requestedTypes)) {
        $requestedTypes[] = 'Complete Medical Records and Billing';
    }

    // Subject line based on request type
    $subjectLine = 'MEDICAL RECORDS REQUEST';
    if (($data['request_type'] ?? '') === 'follow_up') {
        $subjectLine = 'FOLLOW-UP: MEDICAL RECORDS REQUEST';
    } elseif (($data['request_type'] ?? '') === 're_request') {
        $subjectLine = 'SECOND REQUEST: MEDICAL RECORDS';
    } elseif (($data['request_type'] ?? '') === 'rfd') {
        $subjectLine = 'REQUEST FOR DOCUMENTS';
    }

    $authLine = !empty($data['authorization_sent'])
        ? 'A signed HIPAA-compliant authorization is enclosed/attached herewith.'
        : 'A signed authorization will be forwarded under separate cover.';

    $firmName    = htmlspecialchars(FIRM_NAME);
    $firmAddress = htmlspecialchars(FIRM_ADDRESS);
    $firmCSZ     = htmlspecialchars(FIRM_CITY_STATE_ZIP);
    $firmPhone   = htmlspecialchars(FIRM_PHONE);
    $firmFax     = htmlspecialchars(FIRM_FAX);
    $firmEmail   = htmlspecialchars(FIRM_EMAIL);
    $provName    = htmlspecialchars($data['provider_name'] ?? '');
    $provAddress = htmlspecialchars($data['provider_address'] ?? '');
    $clientName  = htmlspecialchars($data['client_name'] ?? '');
    $attorneyName = htmlspecialchars($data['attorney_name'] ?? '');
    $caseNumber  = htmlspecialchars($data['case_number'] ?? '');

    $recordsList = '';
    foreach ($requestedTypes as $i => $rt) {
        $num = $i + 1;
        $recordsList .= "<tr><td style=\"padding:2px 8px;vertical-align:top;\">{$num}.</td><td style=\"padding:2px 0;\">" . htmlspecialchars($rt) . "</td></tr>";
    }

    $notesSection = '';
    if (!empty($data['notes'])) {
        $notesSection = '<p style="margin-top:15px;"><strong>Additional Instructions:</strong> '
            . htmlspecialchars($data['notes']) . '</p>';
    }

    $doiFormatted = !empty($data['doi']) ? date('m/d/Y', strtotime($data['doi'])) : 'N/A';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 1in; }
        .letterhead { text-align: center; border-bottom: 3px double #1a365d; padding-bottom: 15px; margin-bottom: 30px; }
        .firm-name { font-size: 18pt; font-weight: bold; color: #1a365d; letter-spacing: 2px; margin-bottom: 4px; }
        .firm-info { font-size: 9pt; color: #4a5568; }
        .date { margin-bottom: 25px; }
        .recipient { margin-bottom: 20px; line-height: 1.4; }
        .re-line { margin-bottom: 20px; }
        .re-line strong { text-decoration: underline; }
        .body-text { margin-bottom: 12px; text-align: justify; }
        .records-table { margin: 10px 0 10px 20px; }
        .signature { margin-top: 40px; }
        .footer { margin-top: 40px; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
<div class="letter">
    <div class="letterhead">
        <div class="firm-name">{$firmName}</div>
        <div class="firm-info">{$firmAddress} | {$firmCSZ}</div>
        <div class="firm-info">Tel: {$firmPhone} | Fax: {$firmFax} | {$firmEmail}</div>
    </div>

    <div class="date">{$requestDate}</div>

    <div class="recipient">
        Records Department<br>
        {$provName}<br>
        {$provAddress}
    </div>

    <div class="re-line">
        <strong>RE: {$subjectLine}</strong><br>
        <strong>Patient:</strong> {$clientName}<br>
        <strong>Date of Birth:</strong> {$clientDob}<br>
        <strong>Date of Injury:</strong> {$doi}<br>
        <strong>Treatment Dates:</strong> {$treatmentStart} through {$treatmentEnd}<br>
        <strong>Our File No.:</strong> {$caseNumber}
    </div>

    <p class="body-text">Dear Records Department:</p>

    <p class="body-text">
        This firm represents the above-referenced patient in connection with a personal injury matter.
        We are writing to request copies of the following records pertaining to treatment arising
        from the incident on {$doiFormatted}:
    </p>

    <table class="records-table">
        {$recordsList}
    </table>

    <p class="body-text">
        Please provide records for the treatment period of <strong>{$treatmentStart}</strong>
        through <strong>{$treatmentEnd}</strong>.
    </p>

    <p class="body-text">{$authLine}</p>

    <p class="body-text">
        Please forward the requested records to our office at your earliest convenience.
        Records may be sent via fax to <strong>{$firmFax}</strong> or via email to
        <strong>{$firmEmail}</strong>. If there are any copying or processing fees,
        please contact our office so that we may arrange prompt payment.
    </p>

    <p class="body-text">
        Should you have any questions or require additional information, please do not
        hesitate to contact our Records Department at {$firmPhone}.
    </p>

    {$notesSection}

    <p class="body-text">Thank you for your prompt attention to this matter.</p>

    <div class="signature">
        <p>Respectfully,</p>
        <br>
        <p><strong>{$firmName}</strong></p>
        <p>Records Department</p>
        <p>On behalf of {$attorneyName}</p>
    </div>

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Gather all data needed to render a request letter.
 *
 * @param int $requestId record_requests.id
 * @return array|null
 */
function getRequestLetterData($requestId) {
    return dbFetchOne(
        "SELECT
            rr.id AS request_id,
            rr.request_date,
            rr.request_method,
            rr.request_type,
            rr.sent_to,
            rr.authorization_sent,
            rr.notes,
            rr.send_status,
            cp.treatment_start_date,
            cp.treatment_end_date,
            cp.record_types_needed AS record_types,
            c.case_number,
            c.client_name,
            c.client_dob,
            c.doi,
            c.attorney_name,
            p.name AS provider_name,
            p.address AS provider_address,
            p.fax AS provider_fax,
            p.email AS provider_email,
            p.preferred_method AS provider_preferred_method
        FROM record_requests rr
        JOIN case_providers cp ON rr.case_provider_id = cp.id
        JOIN cases c ON cp.case_id = c.id
        JOIN providers p ON cp.provider_id = p.id
        WHERE rr.id = ?",
        [$requestId]
    );
}

/**
 * Render a Health Insurance Ledger Request Letter.
 */
function renderHealthLedgerLetter($data) {
    $requestDate = !empty($data['request_date'])
        ? date('F j, Y', strtotime($data['request_date']))
        : date('F j, Y');
    $clientDob = !empty($data['client_dob'])
        ? date('m/d/Y', strtotime($data['client_dob']))
        : 'N/A';
    $doi = !empty($data['doi'])
        ? date('m/d/Y', strtotime($data['doi']))
        : 'N/A';

    $subjectLine = 'HEALTH INSURANCE LEDGER REQUEST';
    if (($data['request_type'] ?? '') === 'follow_up') {
        $subjectLine = 'FOLLOW-UP: HEALTH INSURANCE LEDGER REQUEST';
    } elseif (($data['request_type'] ?? '') === 're_request') {
        $subjectLine = 'SECOND REQUEST: HEALTH INSURANCE LEDGER';
    }

    $firmName    = htmlspecialchars(FIRM_NAME);
    $firmAddress = htmlspecialchars(FIRM_ADDRESS);
    $firmCSZ     = htmlspecialchars(FIRM_CITY_STATE_ZIP);
    $firmPhone   = htmlspecialchars(FIRM_PHONE);
    $firmFax     = htmlspecialchars(FIRM_FAX);
    $firmEmail   = htmlspecialchars(FIRM_EMAIL);
    $clientName  = htmlspecialchars($data['client_name'] ?? '');
    $carrier     = htmlspecialchars($data['insurance_carrier'] ?? '');
    $caseNumber  = htmlspecialchars($data['case_number'] ?? '');
    $attorneyName = htmlspecialchars($data['attorney_name'] ?? '');

    $notesSection = '';
    if (!empty($data['notes'])) {
        $notesSection = '<p style="margin-top:15px;"><strong>Additional Instructions:</strong> '
            . htmlspecialchars($data['notes']) . '</p>';
    }

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 1in; }
        .letterhead { text-align: center; border-bottom: 3px double #1a365d; padding-bottom: 15px; margin-bottom: 30px; }
        .firm-name { font-size: 18pt; font-weight: bold; color: #1a365d; letter-spacing: 2px; margin-bottom: 4px; }
        .firm-info { font-size: 9pt; color: #4a5568; }
        .date { margin-bottom: 25px; }
        .recipient { margin-bottom: 20px; line-height: 1.4; }
        .re-line { margin-bottom: 20px; }
        .re-line strong { text-decoration: underline; }
        .body-text { margin-bottom: 12px; text-align: justify; }
        .records-table { margin: 10px 0 10px 20px; }
        .signature { margin-top: 40px; }
        .footer { margin-top: 40px; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
<div class="letter">
    <div class="letterhead">
        <div class="firm-name">{$firmName}</div>
        <div class="firm-info">{$firmAddress} | {$firmCSZ}</div>
        <div class="firm-info">Tel: {$firmPhone} | Fax: {$firmFax} | {$firmEmail}</div>
    </div>

    <div class="date">{$requestDate}</div>

    <div class="recipient">
        Claims / Ledger Department<br>
        {$carrier}
    </div>

    <div class="re-line">
        <strong>RE: {$subjectLine}</strong><br>
        <strong>Insured / Patient:</strong> {$clientName}<br>
        <strong>Date of Birth:</strong> {$clientDob}<br>
        <strong>Date of Loss:</strong> {$doi}<br>
        <strong>Insurance Carrier:</strong> {$carrier}<br>
        <strong>Our File No.:</strong> {$caseNumber}
    </div>

    <p class="body-text">Dear Claims Department:</p>

    <p class="body-text">
        This firm represents the above-referenced insured in connection with a personal injury matter.
        We are writing to request the following health insurance records and payment information:
    </p>

    <table class="records-table">
        <tr><td style="padding:2px 8px;vertical-align:top;">1.</td><td style="padding:2px 0;">Complete Payment Ledger / Explanation of Benefits (EOB) statements</td></tr>
        <tr><td style="padding:2px 8px;vertical-align:top;">2.</td><td style="padding:2px 0;">Claim Payment History (all payments made to providers)</td></tr>
        <tr><td style="padding:2px 8px;vertical-align:top;">3.</td><td style="padding:2px 0;">Outstanding Balance / Subrogation / Lien Information</td></tr>
        <tr><td style="padding:2px 8px;vertical-align:top;">4.</td><td style="padding:2px 0;">Coverage Verification and Policy Limits</td></tr>
    </table>

    <p class="body-text">
        Please provide all records related to claims arising from the incident on <strong>{$doi}</strong>.
    </p>

    <p class="body-text">
        Please forward the requested records to our office at your earliest convenience.
        Records may be sent via fax to <strong>{$firmFax}</strong> or via email to
        <strong>{$firmEmail}</strong>.
    </p>

    <p class="body-text">
        Should you have any questions or require additional information, please do not
        hesitate to contact our Records Department at {$firmPhone}.
    </p>

    {$notesSection}

    <p class="body-text">Thank you for your prompt attention to this matter.</p>

    <div class="signature">
        <p>Respectfully,</p>
        <br>
        <p><strong>{$firmName}</strong></p>
        <p>Records Department</p>
        <p>On behalf of {$attorneyName}</p>
    </div>

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Gather data needed to render a health ledger request letter.
 */
function getHealthLedgerLetterData($requestId) {
    return dbFetchOne(
        "SELECT
            hlr.id AS request_id,
            hlr.request_date,
            hlr.request_method,
            hlr.request_type,
            hlr.sent_to,
            hlr.send_status,
            hlr.notes,
            hli.case_number,
            hli.client_name,
            hli.insurance_carrier,
            hli.carrier_contact_email,
            hli.carrier_contact_fax,
            c.client_dob,
            c.doi,
            c.attorney_name
        FROM hl_requests hlr
        JOIN health_ledger_items hli ON hlr.item_id = hli.id
        LEFT JOIN cases c ON hli.case_id = c.id
        WHERE hlr.id = ?",
        [$requestId]
    );
}
