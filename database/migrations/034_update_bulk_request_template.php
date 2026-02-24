<?php
/**
 * Migration 034: Update Bulk Request Template
 *
 * Updates the existing bulk_request template to use {{bulk_letter_body}} placeholder.
 * Template provides letterhead + signature wrapper; bulk letter body is auto-generated.
 * Matches the format from the firm's "Bulk email Request.docx" document.
 *
 * Usage: php database/migrations/034_update_bulk_request_template.php
 *   Or:  http://localhost/MRMS/database/migrations/034_update_bulk_request_template.php
 */

$isCli = php_sapi_name() === 'cli';
if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/db.php';

$pdo = getDBConnection();

$existing = dbFetchOne("SELECT id, body_template FROM letter_templates WHERE template_type = 'bulk_request' AND is_default = 1", []);
if (!$existing) {
    echo "SKIP: No default bulk_request template found. Run migration 026 first.\n";
    if (!$isCli) echo '</pre>';
    exit;
}

$newBody = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .letter { max-width: 8.5in; margin: 0 auto; padding: 0.75in 1in; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { padding: 2px 0; vertical-align: top; }
        .firm-info { font-size: 8pt; font-weight: bold; }
        .sender-info { font-size: 8pt; text-align: right; }
        .body-text { margin-bottom: 10px; text-align: justify; }
        .signature { margin-top: 40px; line-height: 1.4; }
        .footer { margin-top: 40px; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
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
        {{provider_address}}
    </div>

    {{bulk_letter_body}}

    <div class="signature">
        <p>Sincerely,</p>
        <br><br>
        <p><strong>{{sender_name}}</strong>,<br>
        Administrative Personnel<br>
        {{firm_name}}<br>
        {{firm_address}}, {{firm_city_state_zip}}<br>
        Telephone: {{firm_phone}}&nbsp;&nbsp;Fax: {{firm_fax}}</p>
    </div>

    <div class="footer">
        CONFIDENTIALITY NOTICE: This communication contains privileged and confidential information.
        If you are not the intended recipient, please notify the sender immediately and destroy all copies.
    </div>
</div>
</body>
</html>
HTML;

$newSubject = 'Medical Records Request - Multiple Cases ({{case_count}} cases)';

// Save current version
$latestVersion = dbFetchOne(
    "SELECT MAX(version_number) as max_v FROM letter_template_versions WHERE template_id = ?",
    [$existing['id']]
);
$nextVersion = ($latestVersion['max_v'] ?? 0) + 1;

dbInsert('letter_template_versions', [
    'template_id' => $existing['id'],
    'version_number' => $nextVersion,
    'body_template' => $newBody,
    'subject_template' => $newSubject,
    'changed_by' => null,
    'change_notes' => 'Updated to match Bulk email Request.docx - {{bulk_letter_body}}, Sincerely signature',
]);

// Update the template
dbUpdate('letter_templates', [
    'body_template' => $newBody,
    'subject_template' => $newSubject,
    'description' => 'Template for bulk medical records requests. Uses {{bulk_letter_body}} to auto-generate the letter content (case list, records requested, etc.) with firm letterhead and signature wrapper.',
], 'id = ?', [$existing['id']]);

echo "UPDATED: bulk_request template (id={$existing['id']}) - matches Bulk email Request.docx format\n";
echo "VERSION: Created version {$nextVersion}\n";
echo "\n=== Done ===\n";

if (!$isCli) echo '</pre>';
