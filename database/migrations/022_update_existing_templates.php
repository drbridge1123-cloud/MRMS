<?php
/**
 * Migration 022: Update existing medical records templates (2, 3, 7)
 * with new logo-based letterhead and updated body content.
 */

$isCli = php_sapi_name() === 'cli';
if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/db.php';

// ─── Shared snippets ───

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

$footer = <<<'HTML'
    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template body: Medical Records Request (Initial)
// ═══════════════════════════════════════════════════════════════════

$body1 = <<<HTML
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

    {$footer}
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template body: Follow-Up Medical Records Request
// ═══════════════════════════════════════════════════════════════════

$body2 = <<<HTML
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

    <p class="body-text">This correspondence serves as a second request and follow-up to our medical records request dated {{initial_request_date|date:F j, Y}}, regarding the above-referenced patient. To date, our office has not received the requested records.</p>

    <p class="body-text">We are requesting copies of all medical records and itemized billing statements for treatment provided by your office.</p>

    <p class="body-text">Please provide this office the following item(s) which have been checked, furthermore, we request only those items from the date of loss and thereafter.</p>

    {{record_types_checkbox}}

    <p class="body-text">Please forward the requested documents via email, fax, or mail as soon as possible.</p>

    <p class="body-text">Thank you for your prompt attention to this matter.</p>

    {$mikiSignature}

    {$footer}
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Template body: Urgent Medical Records Request
// ═══════════════════════════════════════════════════════════════════

$body3 = <<<HTML
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

    <p class="body-text">We are writing to urgently follow up on our medical records request originally submitted on {{initial_request_date|date:F j, Y}}. {{#if followup_dates}}Our office also followed up on {{followup_dates}}; however, to date, we have not received the requested records or any response from your office.{{else}}To date, we have not received the requested records or any response from your office.{{/if}}</p>

    <p class="body-text">This matter requires immediate attention, as the continued delay is directly impacting on the progress of our client's case. We are requesting copies of all medical records and itemized billing statements for treatment provided by your office, from the date of loss and thereafter.</p>

    <p class="body-text">Please provide the following without delay:</p>

    {{record_types_checkbox}}

    <p class="body-text">Please forward the requested documents via email, fax, or mail as soon as possible.</p>

    <p class="body-text">Your prompt and immediate attention to this matter is appreciated.</p>

    {$mikiSignature}

    {$footer}
</div>
</body>
</html>
HTML;

// ═══════════════════════════════════════════════════════════════════
// Apply updates
// ═══════════════════════════════════════════════════════════════════

$updates = [
    2 => [
        'body_template' => $body1,
        'subject_template' => 'Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
        'description' => 'Initial request for medical records and billing statements from a treatment provider.',
        'is_active' => 1,
    ],
    3 => [
        'body_template' => $body2,
        'subject_template' => 'Follow-Up: Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
        'description' => 'Second request / follow-up for medical records not yet received. References initial request date.',
        'is_active' => 1,
    ],
    7 => [
        'body_template' => $body3,
        'subject_template' => 'URGENT: Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
        'description' => 'Urgent/third request for medical records. References initial and all follow-up dates.',
        'is_active' => 1,
    ],
];

foreach ($updates as $id => $data) {
    dbUpdate('letter_templates', $data, 'id = ?', [$id]);

    $check = dbFetchOne('SELECT name, is_active FROM letter_templates WHERE id = ?', [$id]);
    $hasLogo = strpos($data['body_template'], 'firm_logo_base64') !== false;
    echo "Updated #{$id} ({$check['name']}): active={$check['is_active']} logo=" . ($hasLogo ? 'YES' : 'NO') . "\n";
}

echo "\nDone! 3 templates updated with logo letterhead.\n";

if (!$isCli) echo '</pre>';
