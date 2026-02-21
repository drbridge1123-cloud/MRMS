<?php
// POST /api/bank-reconciliation/import - Import bank statement CSV
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAdmin();
require_once __DIR__ . '/../../helpers/csv.php';

$csv = parseCSV('file');

// Validate required columns exist
$requiredCols = ['date', 'amount'];
$missingCols = array_diff($requiredCols, $csv['headers']);
if (!empty($missingCols)) {
    errorResponse('Missing required columns: ' . implode(', ', $missingCols) . '. Required: date, amount. Optional: description, check_number, reference_number, category');
}

$batchId = bin2hex(random_bytes(16));
$imported = 0;
$skipped = 0;
$autoMatched = 0;
$errors = [];

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    foreach ($csv['rows'] as $i => $row) {
        $rowNum = $i + 2;

        // Parse date
        $dateStr = trim($row['date'] ?? '');
        if ($dateStr === '') {
            $errors[] = ['row' => $rowNum, 'message' => 'date is required'];
            $skipped++;
            continue;
        }
        $parsedDate = parseFlexibleDate($dateStr);
        if (!$parsedDate) {
            $errors[] = ['row' => $rowNum, 'message' => "Invalid date format: '{$dateStr}'"];
            $skipped++;
            continue;
        }

        // Parse amount
        $amountStr = trim($row['amount'] ?? '');
        $amountStr = str_replace(['$', ',', ' '], '', $amountStr);
        if ($amountStr === '' || !is_numeric($amountStr)) {
            $errors[] = ['row' => $rowNum, 'message' => "Invalid amount: '{$row['amount']}'"];
            $skipped++;
            continue;
        }
        $amount = round((float)$amountStr, 2);

        $checkNumber = sanitizeString(trim($row['check_number'] ?? $row['check_#'] ?? $row['check'] ?? '')) ?: null;
        $description = sanitizeString(trim($row['description'] ?? $row['memo'] ?? '')) ?: null;
        $reference = sanitizeString(trim($row['reference_number'] ?? $row['reference'] ?? $row['ref'] ?? '')) ?: null;
        $category = sanitizeString(trim($row['category'] ?? $row['type'] ?? '')) ?: null;

        $data = [
            'batch_id' => $batchId,
            'transaction_date' => $parsedDate,
            'description' => $description,
            'amount' => $amount,
            'check_number' => $checkNumber,
            'reference_number' => $reference,
            'bank_category' => $category,
            'reconciliation_status' => 'unmatched',
            'imported_by' => $userId,
        ];

        $entryId = dbInsert('bank_statement_entries', $data);

        // Auto-match by check number + amount
        if ($checkNumber) {
            $match = dbFetchOne(
                "SELECT p.id FROM mr_fee_payments p
                 LEFT JOIN bank_statement_entries bse ON bse.matched_payment_id = p.id AND bse.id != ?
                 WHERE p.check_number = ? AND p.paid_amount = ? AND bse.id IS NULL",
                [$entryId, $checkNumber, $amount]
            );
            if ($match) {
                dbUpdate('bank_statement_entries', [
                    'reconciliation_status' => 'matched',
                    'matched_payment_id' => $match['id'],
                    'matched_by' => $userId,
                    'matched_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$entryId]);
                $autoMatched++;
            }
        }

        $imported++;
    }

    $pdo->commit();

    logActivity($userId, 'bank_statement_imported', 'bank_reconciliation', null, [
        'batch_id' => $batchId,
        'imported' => $imported,
        'auto_matched' => $autoMatched,
        'skipped' => $skipped,
    ]);

    successResponse([
        'batch_id' => $batchId,
        'imported' => $imported,
        'auto_matched' => $autoMatched,
        'skipped' => $skipped,
        'errors' => $errors,
    ], "{$imported} entries imported, {$autoMatched} auto-matched");
} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Import failed: ' . $e->getMessage(), 500);
}

function parseFlexibleDate($str) {
    // Try common date formats
    $formats = ['Y-m-d', 'm/d/Y', 'm/d/y', 'n/j/Y', 'n/j/y', 'm-d-Y', 'Y/m/d'];
    foreach ($formats as $fmt) {
        $d = DateTime::createFromFormat($fmt, $str);
        if ($d && $d->format($fmt) === $str) {
            return $d->format('Y-m-d');
        }
    }
    // Fallback: let PHP try
    $ts = strtotime($str);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }
    return null;
}
