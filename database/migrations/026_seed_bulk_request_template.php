<?php
/**
 * Migration 026: Seed Bulk Request Template
 *
 * Inserts a default bulk_request template matching the same format
 * as the existing Medical Records Request template.
 *
 * Usage: php database/migrations/026_seed_bulk_request_template.php
 *   Or:  http://localhost/MRMS/database/migrations/026_seed_bulk_request_template.php
 */

$isCli = php_sapi_name() === 'cli';
if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/db.php';

$pdo = getDBConnection();

// Check if bulk_request template already exists
$existing = dbFetchOne("SELECT id FROM letter_templates WHERE template_type = 'bulk_request'", []);
if ($existing) {
    echo "SKIP: bulk_request template already exists (id={$existing['id']})\n";
    if (!$isCli) echo '</pre>';
    exit;
}

$body = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
</head>
<body>
<div class="letter">
    <!-- LETTERHEAD -->
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

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

$id = dbInsert('letter_templates', [
    'name' => 'Bulk Medical Records Request',
    'description' => 'Template for bulk medical records requests. Same format as initial request, used when sending multiple requests at once.',
    'template_type' => 'bulk_request',
    'subject_template' => 'Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
    'body_template' => $body,
    'is_default' => 1,
    'is_active' => 1,
]);

// Create version 1
dbInsert('letter_template_versions', [
    'template_id' => $id,
    'version_number' => 1,
    'body_template' => $body,
    'subject_template' => 'Medical Records Request - {{client_name}} (DOI: {{doi|date:m/d/Y}})',
    'changed_by' => null,
    'change_notes' => 'Initial version',
]);

echo "INSERT: 'Bulk Medical Records Request' (id={$id}, type=bulk_request, DEFAULT)\n";
echo "\n=== Done ===\n";

if (!$isCli) echo '</pre>';
