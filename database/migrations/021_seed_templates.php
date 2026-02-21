<?php
/**
 * Migration 021: Seed 7 Default Letter Templates
 *
 * Inserts the 7 standard letter templates used by the firm:
 *   1. Medical Records Request (Initial)
 *   2. Follow-Up Medical Records Request
 *   3. Urgent Medical Records Request
 *   4. Health Ledger Request
 *   5. Final Health Lien Request
 *   6. Rep Letter - Health Subrogation
 *   7. Provider Balance Verification
 *
 * Usage: php database/migrations/021_seed_templates.php
 *   Or:  http://localhost/MRMS/database/migrations/021_seed_templates.php
 */

$isCli = php_sapi_name() === 'cli';
if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/db.php';

$pdo = getDBConnection();

// ─── Letterhead HTML snippets ───────────────────────────────────────

$simpleLetterhead = <<<'HTML'
    <!-- LETTERHEAD (no attorney list) -->
    <table class="header-table">
        <tr>
            <td style="width: 40%;" rowspan="4">
                <img src="{{firm_logo_base64}}" style="width: 2.48in; height: auto;" alt="{{firm_name}}">
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <table class="header-table" style="margin-top: 0;">
        <tr>
            <td style="width: 50%;" class="firm-info">{{firm_address}} | {{firm_city_state_zip}}</td>
            <td class="sender-info">{{sender_name}}, Paralegal</td>
        </tr>
        <tr>
            <td class="firm-info">Phone: {{firm_phone}}&nbsp;&nbsp;|&nbsp;&nbsp;Fax: {{firm_fax}}</td>
            <td class="sender-info">{{sender_email}}</td>
        </tr>
    </table>
    <hr style="border: none; border-top: 1px solid #000; margin: 8px 0 15px 0;">
HTML;

$attorneyLetterhead = <<<'HTML'
    <!-- LETTERHEAD (no attorney list) -->
    <table class="header-table">
        <tr>
            <td style="width: 40%;" rowspan="4">
                <img src="{{firm_logo_base64}}" style="width: 2.48in; height: auto;" alt="{{firm_name}}">
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <table class="header-table" style="margin-top: 0;">
        <tr>
            <td style="width: 50%;" class="firm-info">{{firm_address}} | {{firm_city_state_zip}}</td>
            <td class="sender-info">{{sender_name}}, Paralegal</td>
        </tr>
        <tr>
            <td class="firm-info">Phone: {{firm_phone}}&nbsp;&nbsp;|&nbsp;&nbsp;Fax: {{firm_fax}}</td>
            <td class="sender-info">{{sender_email}}</td>
        </tr>
    </table>
    <hr style="border: none; border-top: 1px solid #000; margin: 8px 0 15px 0;">
HTML;

// ─── Common styles ──────────────────────────────────────────────────

$commonStyles = <<<'HTML'
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 0.75in 1in; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { padding: 2px 0; vertical-align: top; }
        .attorney-list { font-size: 8pt; text-align: right; line-height: 1.6; }
        .firm-info { font-size: 8pt; font-weight: bold; }
        .sender-info { font-size: 8pt; text-align: right; }
        .body-text { margin-bottom: 10px; text-align: justify; }
        .signature { margin-top: 40px; line-height: 1.4; }
        .footer { margin-top: 40px; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
        ul { margin: 10px 0; padding-left: 25px; list-style-type: disc; }
        ul li { margin-bottom: 4px; }
    </style>
HTML;

// ─── Signature blocks ───────────────────────────────────────────────

$mikiSignature = <<<'HTML'
    <div class="signature">
        <p>Best regards,</p>
        <br><br>
        <p><strong>{{sender_name}}</strong><br>
        Billing Assistant<br>
        {{firm_name}}<br>
        {{firm_address}}, {{firm_city_state_zip}}<br>
        Telephone: {{firm_phone}}<br>
        Fax: {{firm_fax}}</p>
    </div>
HTML;

$ellaSignature = <<<'HTML'
    <div class="signature">
        <p>Very truly yours,</p>
        <br><br>
        <p><strong>{{sender_name}}</strong>, Paralegal<br>
        {{sender_email}}</p>
    </div>
HTML;

$ellaSignatureLegalAssistant = <<<'HTML'
    <div class="signature">
        <p>Very truly yours,</p>
        <br><br>
        <p><strong>{{sender_name}}</strong><br>
        Legal Assistant<br>
        {{sender_email}}</p>
    </div>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 1: Medical Records Request (Initial)
// ═══════════════════════════════════════════════════════════════════

$template1_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$simpleLetterhead}

    <p style="margin-bottom:20px;">{{request_date|date:F j, Y}}</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{provider_name}}<br>
        Attn: Release of Information
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Patient/Client:</strong> {{client_name}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear {{provider_name}}:</p>

    <p class="body-text">Please be advised that our office represents {{client_name}}. We are requesting copies of all medical records and itemized billing statements for treatment provided by your office.</p>

    <p class="body-text">Please provide this office the following item(s) which have been checked, furthermore, we request only those items from the date of loss and thereafter.</p>

    {{record_types_checkbox}}

    <p class="body-text">You may forward the requested documents via email, fax, or mail.</p>

    {{#if authorization_sent}}<p class="body-text">Enclosed is a signed authorization executed by our client(s) permitting release of the above records. If your office requires payment prior to releasing the records, please provide your record fees via email or fax.</p>{{else}}<p class="body-text">A signed authorization will be forwarded under separate cover. If your office requires payment prior to releasing the records, please provide your record fees via email or fax.</p>{{/if}}

    <p class="body-text">Thank you for your cooperation. We appreciate your prompt attention to this request.</p>

    {$mikiSignature}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 2: Follow-Up Medical Records Request
// ═══════════════════════════════════════════════════════════════════

$template2_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$simpleLetterhead}

    <p style="margin-bottom:20px;">{{request_date|date:F j, Y}}</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{provider_name}}<br>
        Attn: Release of Information
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Patient/Client:</strong> {{client_name}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear {{provider_name}}:</p>

    <p class="body-text">This correspondence serves as a follow-up to our previous medical records request(s) dated {{previous_request_dates}}, regarding the above-referenced patient. To date, our office has not received the requested records.</p>

    <p class="body-text">We are requesting copies of all medical records and itemized billing statements for treatment provided by your office.</p>

    <p class="body-text">Please provide this office the following item(s) which have been checked, furthermore, we request only those items from the date of loss and thereafter.</p>

    {{record_types_checkbox}}

    <p class="body-text">Please forward the requested documents via email, fax, or mail as soon as possible.</p>

    <p class="body-text">Thank you for your prompt attention to this matter.</p>

    {$mikiSignature}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 3: Urgent Medical Records Request
// ═══════════════════════════════════════════════════════════════════

$template3_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$simpleLetterhead}

    <p style="margin-bottom:20px;">{{request_date|date:F j, Y}}</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{provider_name}}<br>
        Attn: Release of Information
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Patient/Client:</strong> {{client_name}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear {{provider_name}}:</p>

    <p class="body-text">We are writing to urgently follow up on our medical records request(s). Our office has previously requested records on {{previous_request_dates}}; however, to date, we have not received the requested records or any response from your office.</p>

    <p class="body-text">This matter requires immediate attention, as the continued delay is directly impacting on the progress of our client's case. We are requesting copies of all medical records and itemized billing statements for treatment provided by your office, from the date of loss and thereafter.</p>

    <p class="body-text">Please provide the following without delay:</p>

    {{record_types_checkbox}}

    <p class="body-text">Please forward the requested documents via email, fax, or mail as soon as possible.</p>

    <p class="body-text">Your prompt and immediate attention to this matter is appreciated.</p>

    {$mikiSignature}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 4: Health Ledger Request
// ═══════════════════════════════════════════════════════════════════

$template4_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$attorneyLetterhead}

    <p style="margin-bottom:5px;">{{request_date|date:F j, Y}}</p>
    <p style="margin-bottom:20px; font-size:10pt; color:#555;">Via {{request_method}} ({{recipient_contact}})</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{insurance_carrier}}<br>
        Attn: Subrogation Department
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Our Client/Your Insured:</strong> {{client_name}}<br>
        <strong>Claim Number:</strong> {{claim_number}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear Case Handler:</p>

    <p class="body-text">Our office respectfully requests the current lien amount and payment ledger for the above-referenced individual(s) arising from the above-dated loss.</p>

    <p class="body-text">Thank you for your anticipated courtesy and cooperation in this matter. Should you have any questions, please contact the undersigned.</p>

    {$ellaSignature}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 5: Final Health Lien Request
// ═══════════════════════════════════════════════════════════════════

$template5_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$attorneyLetterhead}

    <p style="margin-bottom:5px;">{{request_date|date:F j, Y}}</p>
    <p style="margin-bottom:20px; font-size:10pt; color:#555;">Via {{request_method}} ({{recipient_contact}})</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{insurance_carrier}}<br>
        Attn: Subrogation Department
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Our Client/Your Insured:</strong> {{client_name}}<br>
        <strong>Claim Number:</strong> {{claim_number}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear Case Handler:</p>

    <p class="body-text">Our office requests the final lien amount and payment ledger for the above-referenced individual(s) arising from the above-dated loss.</p>

    <p class="body-text">The settlement information is as follows:</p>

    <table style="margin: 10px 0 20px 20px; border-collapse:collapse;">
        <tr><td style="padding:3px 15px 3px 0; font-weight:bold;">Date of Settlement:</td><td style="padding:3px 0;">{{settlement_date|date:m/d/Y}}</td></tr>
        <tr><td style="padding:3px 15px 3px 0; font-weight:bold;">Settlement Amount:</td><td style="padding:3px 0;">{{settlement_amount|currency}}</td></tr>
        <tr><td style="padding:3px 15px 3px 0; font-weight:bold;">Attorney's Fees:</td><td style="padding:3px 0;">{{attorney_fees|currency}}</td></tr>
        <tr><td style="padding:3px 15px 3px 0; font-weight:bold;">Costs:</td><td style="padding:3px 0;">{{costs|currency}}</td></tr>
        <tr><td style="padding:3px 15px 3px 0; font-weight:bold;">Last Date of Treatment:</td><td style="padding:3px 0;">{{treatment_end_date|date:m/d/Y}}</td></tr>
    </table>

    <p class="body-text">Please apply the Mahler reduction in calculating the final lien amount.</p>

    <p class="body-text">Thank you for your anticipated courtesy and cooperation in this matter. Should you have any questions, please contact the undersigned.</p>

    {$ellaSignature}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 6: Rep Letter - Health Subrogation
// ═══════════════════════════════════════════════════════════════════

$template6_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$attorneyLetterhead}

    <p style="margin-bottom:5px;">{{request_date|date:F j, Y}}</p>
    <p style="margin-bottom:20px; font-size:10pt; color:#555;">Via {{request_method}} ({{recipient_contact}})</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{insurance_carrier}}<br>
        Attn: Subrogation Department
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Our Client/Your Insured:</strong> {{client_name}}<br>
        <strong>Member ID:</strong> {{member_id}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear Claims Department,</p>

    <p class="body-text">Please be advised that this office has been retained to represent the above-referenced individual(s) arising from the above-dated automobile accident. Upon receipt of this correspondence, please contact our office to discuss this matter and confirm, in writing, the coverage available to our client in connection with this loss.</p>

    <p class="body-text">We respectfully request the following:</p>

    <ul>
        <li>That the applicable insurance benefits be opened immediately; and</li>
        <li>If any payments have been made to date, please provide a complete payment ledger and the current lien amount.</li>
    </ul>

    <p class="body-text">Thank you for your anticipated courtesy and cooperation in this matter. Should you have any questions, please contact the undersigned.</p>

    {$ellaSignatureLegalAssistant}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template 7: Provider Balance Verification
// ═══════════════════════════════════════════════════════════════════

$template7_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    {$commonStyles}
</head>
<body>
<div class="letter">
    {$attorneyLetterhead}

    <p style="margin-bottom:5px;">{{request_date|date:F j, Y}}</p>
    <p style="margin-bottom:20px; font-size:10pt; color:#555;">Via {{request_method}} ({{recipient_contact}})</p>

    <div style="margin-bottom:20px; line-height:1.4;">
        {{provider_name}}<br>
        {{#if provider_address}}{{provider_address}}<br>{{/if}}
        Attn: Billing Department
    </div>

    <div style="margin-bottom:20px;">
        <strong>RE:</strong><br>
        <strong>Your Patient/Our Client:</strong> {{client_name}}<br>
        <strong>Date of Birth:</strong> {{client_dob|date:m/d/Y}}<br>
        <strong>Date of Loss:</strong> {{doi|date:m/d/Y}}
    </div>

    <p class="body-text">Dear Billing Services:</p>

    <p class="body-text">Please be advised that this office has been retained to represent the above-referenced individual(s) in connection with the motor vehicle accident referenced above. It is our understanding that you provided medical services to our client(s) for injuries sustained as a result of this incident.</p>

    <p class="body-text">Enclosed is a Balance Verification Form. Please complete the form and return it to our office via email or fax at {{firm_fax}}. If there is any outstanding balance, please clearly indicate the reason for such balance.</p>

    <p class="body-text"><strong>Important Notice:</strong> The balance stated on the completed verification form will be deemed the final amount due. Any changes, corrections, or discrepancies must be reported to our office in writing within thirty (30) days of the date of certification. Failure to do so will result in acceptance of the stated balance as final.</p>

    <p class="body-text">Please include itemized billing statements with your response.</p>

    <p class="body-text">Thank you for your prompt cooperation in this matter.</p>

    {$ellaSignature}

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// INSERT TEMPLATES
// ═══════════════════════════════════════════════════════════════════

$templates = [
    [
        'name' => 'Medical Records Request',
        'description' => 'Initial request for medical records and billing statements from a treatment provider.',
        'template_type' => 'medical_records',
        'subject_template' => 'Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
        'body_template' => $template1_body,
        'is_default' => 1,
    ],
    [
        'name' => 'Follow-Up Medical Records Request',
        'description' => 'Second request / follow-up for medical records not yet received. References initial request date.',
        'template_type' => 'medical_records',
        'subject_template' => 'Follow-Up: Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
        'body_template' => $template2_body,
        'is_default' => 0,
    ],
    [
        'name' => 'Urgent Medical Records Request',
        'description' => 'Urgent/third request for medical records. References initial and all follow-up dates.',
        'template_type' => 'medical_records',
        'subject_template' => 'URGENT: Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
        'body_template' => $template3_body,
        'is_default' => 0,
    ],
    [
        'name' => 'Health Ledger Request',
        'description' => 'Request for current lien amount and payment ledger from health insurance subrogation department.',
        'template_type' => 'health_ledger',
        'subject_template' => 'Health Insurance Ledger Request - {{client_name}} (DOL: {{doi|date:m/d/Y}})',
        'body_template' => $template4_body,
        'is_default' => 1,
    ],
    [
        'name' => 'Final Health Lien Request',
        'description' => 'Request for final lien amount with settlement information. Includes Mahler reduction request.',
        'template_type' => 'health_ledger',
        'subject_template' => 'Final Health Lien Resolution - {{client_name}} (DOL: {{doi|date:m/d/Y}})',
        'body_template' => $template5_body,
        'is_default' => 0,
    ],
    [
        'name' => 'Rep Letter - Health Subrogation',
        'description' => 'Letter of representation to health insurance company for subrogation matters. Uses Member ID.',
        'template_type' => 'health_ledger',
        'subject_template' => 'Letter of Representation - {{client_name}} (DOL: {{doi|date:m/d/Y}})',
        'body_template' => $template6_body,
        'is_default' => 0,
    ],
    [
        'name' => 'Provider Balance Verification',
        'description' => 'Request to provider for balance verification. Auto-attaches Balance Verification Form PDF.',
        'template_type' => 'balance_verification',
        'subject_template' => 'Balance Verification Request - {{client_name}} (DOL: {{doi|date:m/d/Y}})',
        'body_template' => $template7_body,
        'is_default' => 1,
    ],
];

$inserted = 0;
$skipped = 0;

foreach ($templates as $t) {
    // Check if template with this name already exists
    $existing = dbFetchOne("SELECT id FROM letter_templates WHERE name = ?", [$t['name']]);
    if ($existing) {
        echo "SKIP: '{$t['name']}' already exists (id={$existing['id']})\n";
        $skipped++;
        continue;
    }

    // If setting as default, clear existing defaults for this type
    if ($t['is_default']) {
        $pdo->prepare("UPDATE letter_templates SET is_default = 0 WHERE template_type = ? AND is_default = 1")
            ->execute([$t['template_type']]);
    }

    $id = dbInsert('letter_templates', [
        'name' => $t['name'],
        'description' => $t['description'],
        'template_type' => $t['template_type'],
        'subject_template' => $t['subject_template'],
        'body_template' => $t['body_template'],
        'is_default' => $t['is_default'],
        'is_active' => 1,
    ]);

    echo "INSERT: '{$t['name']}' (id={$id}, type={$t['template_type']}" . ($t['is_default'] ? ', DEFAULT' : '') . ")\n";
    $inserted++;
}

echo "\n=== Done: {$inserted} inserted, {$skipped} skipped ===\n";

if (!$isCli) echo '</pre>';
