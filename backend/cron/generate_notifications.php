<?php
/**
 * Generate System Messages - Run daily via cron/task scheduler
 * Usage: php generate_notifications.php
 *
 * Checks for:
 * 1. Follow-ups due (next_followup_date <= today)
 * 2. Deadline warnings (7 days before deadline)
 * 3. Deadline overdue (past deadline)
 * 4. Escalation: deadline reached → manager notification + action_needed status
 * 5. Escalation: deadline + 14 days → admin notification
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/escalation.php';
require_once __DIR__ . '/../helpers/email.php';

echo "=== MRMS System Message Generator ===\n";
echo "Running at: " . date('Y-m-d H:i:s') . "\n\n";

$today = date('Y-m-d');
$warningDate = date('Y-m-d', strtotime('+' . DEADLINE_WARNING_DAYS . ' days'));

/**
 * Helper: create a system message (prevents duplicates per day by checking existing messages)
 */
function createSystemMessage($toUserId, $subject, $message, $cpId, $type) {
    global $today;
    // Prevent duplicates: check if same subject sent to same user today
    $exists = dbFetchOne(
        "SELECT id FROM messages WHERE to_user_id = ? AND subject = ? AND DATE(created_at) = ?",
        [$toUserId, $subject, $today]
    );
    if ($exists) return false;

    dbInsert('messages', [
        'from_user_id' => $toUserId, // System message: from self
        'to_user_id' => $toUserId,
        'subject' => $subject,
        'message' => $message
    ]);
    return true;
}

// 1. Follow-up Due
echo "Checking follow-ups due...\n";
$followupDue = dbFetchAll("
    SELECT cp.id, cp.assigned_to, cp.case_id,
           c.case_number, c.client_name,
           p.name as provider_name,
           rr.request_date, rr.next_followup_date
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id
    JOIN providers p ON p.id = cp.provider_id
    LEFT JOIN (
        SELECT case_provider_id, MAX(request_date) as request_date, MAX(next_followup_date) as next_followup_date
        FROM record_requests
        GROUP BY case_provider_id
    ) rr ON rr.case_provider_id = cp.id
    WHERE cp.overall_status IN ('requesting', 'follow_up', 'action_needed')
      AND rr.next_followup_date <= ?
      AND cp.assigned_to IS NOT NULL
", [$today]);

foreach ($followupDue as $item) {
    $subject = "[System] Follow-up due: {$item['provider_name']} — Case #{$item['case_number']}";
    $msg = "Follow-up is due for {$item['provider_name']} on case {$item['case_number']} ({$item['client_name']}).";
    if (createSystemMessage($item['assigned_to'], $subject, $msg, $item['id'], 'followup_due')) {
        echo "  Created follow-up message for case_provider #{$item['id']}\n";
    }
}

// 2. Deadline Warning (7 days before)
echo "Checking deadline warnings...\n";
$deadlineWarnings = dbFetchAll("
    SELECT cp.id, cp.assigned_to, cp.deadline, cp.case_id,
           c.case_number, c.client_name,
           p.name as provider_name
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id
    JOIN providers p ON p.id = cp.provider_id
    WHERE cp.overall_status NOT IN ('treating', 'received_complete', 'verified')
      AND cp.deadline BETWEEN ? AND ?
      AND cp.assigned_to IS NOT NULL
", [$today, $warningDate]);

foreach ($deadlineWarnings as $item) {
    $daysLeft = (int)((strtotime($item['deadline']) - strtotime($today)) / 86400);
    $subject = "[System] Deadline in {$daysLeft} days: {$item['provider_name']} — Case #{$item['case_number']}";
    $msg = "Deadline approaching for {$item['provider_name']} on case {$item['case_number']} ({$item['client_name']}).\n\nDeadline: " . date('M j, Y', strtotime($item['deadline'])) . " ({$daysLeft} days remaining)";
    if (createSystemMessage($item['assigned_to'], $subject, $msg, $item['id'], 'deadline_warning')) {
        echo "  Created deadline warning for case_provider #{$item['id']}\n";
    }
}

// 3. Deadline Overdue
echo "Checking overdue items...\n";
$overdue = dbFetchAll("
    SELECT cp.id, cp.assigned_to, cp.deadline, cp.case_id,
           c.case_number, c.client_name,
           p.name as provider_name
    FROM case_providers cp
    JOIN cases c ON c.id = cp.case_id
    JOIN providers p ON p.id = cp.provider_id
    WHERE cp.overall_status NOT IN ('treating', 'received_complete', 'verified')
      AND cp.deadline < ?
      AND cp.assigned_to IS NOT NULL
", [$today]);

foreach ($overdue as $item) {
    $daysOver = (int)((strtotime($today) - strtotime($item['deadline'])) / 86400);
    $subject = "[System] OVERDUE ({$daysOver}d): {$item['provider_name']} — Case #{$item['case_number']}";
    $msg = "OVERDUE: {$item['provider_name']} for case {$item['case_number']} ({$item['client_name']}) is {$daysOver} day(s) past deadline.\n\nDeadline was: " . date('M j, Y', strtotime($item['deadline']));

    // Notify assigned staff
    if (createSystemMessage($item['assigned_to'], $subject, $msg, $item['id'], 'deadline_overdue')) {
        echo "  Created overdue message for case_provider #{$item['id']}\n";
    }

    // Also notify admin (user id 1) if different from assigned
    $adminId = 1;
    if ((int)$item['assigned_to'] !== $adminId) {
        createSystemMessage($adminId, $subject, $msg, $item['id'], 'deadline_overdue');
    }
}

// 4. Escalation notifications
echo "Checking escalation notifications...\n";
$escCreated = generateEscalationNotifications();
echo "  Created {$escCreated} escalation notification(s)\n";

echo "\nDone.\n";
