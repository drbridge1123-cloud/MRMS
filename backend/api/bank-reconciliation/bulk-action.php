<?php
// POST /api/bank-reconciliation/bulk-action — bulk ignore/restore/auto-match
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$input = getInput();

$ids = $input['ids'] ?? [];
$action = $input['action'] ?? '';

if (empty($ids) || !is_array($ids)) {
    errorResponse('No entries selected');
}

$allowedActions = ['ignore', 'restore', 'auto-match'];
if (!in_array($action, $allowedActions)) {
    errorResponse('Invalid action: ' . $action);
}

// Sanitize IDs
$ids = array_map('intval', $ids);
$ids = array_filter($ids, fn($id) => $id > 0);
if (empty($ids)) {
    errorResponse('No valid entry IDs');
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$affected = 0;

$pdo = getDBConnection();
$pdo->beginTransaction();

try {
    if ($action === 'ignore') {
        $stmt = $pdo->prepare(
            "UPDATE bank_statement_entries
             SET reconciliation_status = 'ignored', matched_payment_id = NULL,
                 matched_by = ?, matched_at = NOW()
             WHERE id IN ({$placeholders}) AND reconciliation_status = 'unmatched'"
        );
        $stmt->execute(array_merge([$userId], $ids));
        $affected = $stmt->rowCount();

    } elseif ($action === 'restore') {
        $stmt = $pdo->prepare(
            "UPDATE bank_statement_entries
             SET reconciliation_status = 'unmatched', matched_payment_id = NULL,
                 matched_by = NULL, matched_at = NULL
             WHERE id IN ({$placeholders}) AND reconciliation_status IN ('ignored', 'matched')"
        );
        $stmt->execute($ids);
        $affected = $stmt->rowCount();

    } elseif ($action === 'auto-match') {
        // Try to auto-match each selected entry by check_number + amount
        $entries = dbFetchAll(
            "SELECT id, check_number, amount FROM bank_statement_entries
             WHERE id IN ({$placeholders}) AND reconciliation_status = 'unmatched' AND check_number IS NOT NULL",
            $ids
        );

        foreach ($entries as $entry) {
            $match = dbFetchOne(
                "SELECT p.id FROM mr_fee_payments p
                 LEFT JOIN bank_statement_entries bse ON bse.matched_payment_id = p.id AND bse.id != ?
                 WHERE p.check_number = ? AND p.paid_amount = ? AND bse.id IS NULL",
                [$entry['id'], $entry['check_number'], $entry['amount']]
            );
            if ($match) {
                dbUpdate('bank_statement_entries', [
                    'reconciliation_status' => 'matched',
                    'matched_payment_id' => $match['id'],
                    'matched_by' => $userId,
                    'matched_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$entry['id']]);
                $affected++;
            }
        }
    }

    $pdo->commit();

    logActivity($userId, 'bank_bulk_' . str_replace('-', '_', $action), 'bank_reconciliation', null, [
        'ids' => $ids,
        'affected' => $affected,
    ]);

    $labels = ['ignore' => 'ignored', 'restore' => 'restored', 'auto-match' => 'auto-matched'];
    successResponse(['affected' => $affected], "{$affected} entries {$labels[$action]}");

} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Bulk action failed: ' . $e->getMessage(), 500);
}
