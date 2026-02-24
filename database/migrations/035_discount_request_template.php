<?php
/**
 * Migration 035: Add Discount Request Template
 *
 * 1. Expands template_type ENUM to include 'discount_request'
 * 2. Seeds default discount request template matching firm's Word document
 *
 * Usage: php database/migrations/035_discount_request_template.php
 *   Or:  http://localhost/MRMS/database/migrations/035_discount_request_template.php
 */

$isCli = php_sapi_name() === 'cli';
if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/helpers/db.php';

$pdo = getDBConnection();

// Step 1: Expand ENUM to include 'discount_request'
echo "Expanding template_type ENUM...\n";
try {
    $pdo->exec("ALTER TABLE letter_templates
        MODIFY template_type ENUM(
            'medical_records', 'health_ledger', 'bulk_request', 'custom',
            'balance_verification', 'discount_request'
        ) NOT NULL DEFAULT 'custom'");
    echo "OK: ENUM expanded\n";
} catch (Exception $e) {
    echo "WARN: ENUM alter - " . $e->getMessage() . "\n";
}

// Step 2: Check if template already exists
$existing = dbFetchOne("SELECT id FROM letter_templates WHERE template_type = 'discount_request'", []);
if ($existing) {
    echo "SKIP: discount_request template already exists (id={$existing['id']})\n";
    if (!$isCli) echo '</pre>';
    exit;
}

// Step 3: Create template
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

    <p><strong>{{provider_name}}</strong></p>

    <div style="margin-bottom:15px;">
        <table style="border-collapse:collapse;">
            <tr><td style="padding:1px 0;width:30px;">RE:</td><td style="padding:1px 0;">{{client_name}}</td></tr>
            <tr><td style="padding:1px 0;"></td><td style="padding:1px 0;">{{client_dob|date:m/d/Y}}</td></tr>
            <tr><td style="padding:1px 0;"></td><td style="padding:1px 0;">{{doi|date:m/d/Y}}</td></tr>
        </table>
    </div>

    <p class="body-text">Dear {{provider_name}}:</p>

    <p class="body-text">As this case is approaching settlement, the current recovery does not sufficiently cover the client's total medical expenses, attorney's fees, and compensation for pain and suffering.</p>

    <p class="body-text">According to our records, the total billed amount for services rendered by {{provider_name}} is $______, which remains unpaid. Considering the limited recovery and facilitating equitable distribution of settlement funds, we respectfully request a ______% reduction of the outstanding balance, resulting in a revised amount of $______.</p>

    <p class="body-text">We sincerely appreciate your consideration of this request. While we strive to obtain full recovery in every matter, the circumstances of this case need to make a reasonable compromise. We respectfully ask for your cooperation in assisting our mutual patient during this settlement process.</p>

    <p class="body-text">Please do not hesitate to contact our office should you require any additional documentation or information.</p>

    <p class="body-text">Thank you for your time and consideration.</p>

    <div class="signature">
        <p>Best regards,</p>
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

$subject = 'Medical Records Discount Request - {{client_name}} ({{provider_name}})';

$id = dbInsert('letter_templates', [
    'name' => 'Medical Records Discount Request',
    'description' => 'Request for reduction of outstanding medical balance during settlement. Used when case recovery is insufficient to cover full medical expenses.',
    'template_type' => 'discount_request',
    'subject_template' => $subject,
    'body_template' => $body,
    'is_default' => 1,
    'is_active' => 1,
]);

dbInsert('letter_template_versions', [
    'template_id' => $id,
    'version_number' => 1,
    'body_template' => $body,
    'subject_template' => $subject,
    'changed_by' => null,
    'change_notes' => 'Initial version - Medical Records Discount Request',
]);

echo "INSERT: 'Medical Records Discount Request' (id={$id}, type=discount_request, DEFAULT)\n";
echo "\n=== Done ===\n";

if (!$isCli) echo '</pre>';
