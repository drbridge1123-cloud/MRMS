<?php
/**
 * Generate static Balance Verification Form PDF
 *
 * This creates the Part 2 form that gets auto-attached when
 * sending a Provider Balance Verification letter (Template 7).
 *
 * Usage: php database/migrations/generate_bv_form.php
 *   Or:  http://localhost/MRMS/database/migrations/generate_bv_form.php
 */

$isCli = php_sapi_name() === 'cli';
if (!$isCli) echo '<pre>';

require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
    .form { max-width: 8.5in; margin: 0 auto; padding: 0.75in 1in; }
    h2 { text-align: center; font-size: 14pt; margin-bottom: 25px; text-decoration: underline; }
    .field-row { margin-bottom: 12px; }
    .field-label { font-weight: normal; }
    .field-line { border-bottom: 1px solid #000; display: inline-block; min-width: 200px; }
    .field-line-wide { border-bottom: 1px solid #000; display: inline-block; min-width: 400px; }
    .checkbox { display: inline-block; width: 14px; height: 14px; border: 1px solid #000; margin-right: 5px; vertical-align: middle; }
    table.form-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    table.form-table td { padding: 8px 4px; vertical-align: top; }
    .section { margin-top: 20px; }
    .cert-line { margin-top: 30px; }
</style>
</head>
<body>
<div class="form">
    <h2>ACCOUNT(S) BALANCE VERIFICATION FORM</h2>

    <div class="field-row">
        <strong>Provider Name:</strong> <span class="field-line-wide">&nbsp;</span>
    </div>

    <div class="field-row" style="margin-top:15px;">
        <strong>RE:</strong> Our Client/Your Patient: <span class="field-line-wide">&nbsp;</span>
    </div>
    <div class="field-row" style="padding-left:30px;">
        Date of Birth: <span class="field-line">&nbsp;</span>
    </div>
    <div class="field-row" style="padding-left:30px;">
        Date of Loss: <span class="field-line">&nbsp;</span>
    </div>

    <p style="margin-top:20px;">We verify that with regard to the above-named patient, the following information is true and correct:</p>

    <div class="section">
        <div class="field-row">
            1. The total medical costs incurred to date by this patient as a result of the injuries for which we have provided treatment is: $<span class="field-line">&nbsp;</span>
        </div>

        <div class="field-row">
            2. Dates of treatment from: <span class="field-line" style="min-width:120px;">&nbsp;</span> to: <span class="field-line" style="min-width:120px;">&nbsp;</span>
        </div>

        <div class="field-row">
            3. Payments were made on the bill?&nbsp;&nbsp;
            <span class="checkbox"></span> Yes&nbsp;&nbsp;&nbsp;
            <span class="checkbox"></span> No
        </div>

        <div class="field-row">
            4. If payments have been made, please state the totals for how much was paid on the bill and by whom (e.g., State Farm, Premera, etc.): $<span class="field-line-wide">&nbsp;</span>
        </div>

        <div class="field-row">
            5. Were there any contractual write-offs or adjustments made on the account after payment? If so, what was the amount written-off or adjusted?: $<span class="field-line">&nbsp;</span>
        </div>

        <div class="field-row">
            6. There is an unpaid balance currently due on the bill:&nbsp;&nbsp;
            <span class="checkbox"></span> Yes&nbsp;&nbsp;&nbsp;
            <span class="checkbox"></span> No
        </div>

        <div class="field-row">
            7. If so, how much?: $<span class="field-line">&nbsp;</span>
        </div>

        <div class="field-row">
            8. Was part of this bill forwarded to a debt collection agency? If so, how much was forwarded and what is the name and telephone number of the agency?: <span class="field-line-wide">&nbsp;</span>
        </div>

        <div class="field-row">
            9. If there is an outstanding balance, Check should be made payable to: <span class="field-line-wide">&nbsp;</span>
        </div>

        <div class="field-row">
            10. Mailing address to send payment to: <span class="field-line-wide">&nbsp;</span>
        </div>
    </div>

    <div class="cert-line" style="margin-top:40px;">
        <p>I, <span class="field-line" style="min-width:180px;">&nbsp;</span>, hereby certify that the above information is true and correct as of the <span class="field-line" style="min-width:30px;">&nbsp;</span> day of <span class="field-line" style="min-width:120px;">&nbsp;</span>, 20<span class="field-line" style="min-width:30px;">&nbsp;</span>.</p>
    </div>

    <div style="margin-top:40px;">
        <div class="field-row">
            Name and Title: <span class="field-line-wide">&nbsp;</span>
        </div>
    </div>
</div>
</body>
</html>
HTML;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Times New Roman');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

$outputPath = __DIR__ . '/../../storage/templates/balance_verification_form.pdf';
file_put_contents($outputPath, $dompdf->output());

echo "Generated: {$outputPath}\n";
echo "File size: " . filesize($outputPath) . " bytes\n";

if (!$isCli) echo '</pre>';
