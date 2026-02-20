<?php
/**
 * Generate Notifications - Run daily via cron/task scheduler
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

echo "=== MRMS Notification Generator ===\n";
echo "Running at: " . date('Y-m-d H:i:s') . "\n\n";

$today = date('Y-m-d');
$warningDate = date('Y-m-d', strtotime('+' . DEADLINE_WARNING_DAYS . ' days'));

// 1. Follow-up Due notifications
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
    $exists = dbFetchOne("
        SELECT id FROM notifications
        WHERE case_provider_id = ? AND type = 'followup_due' AND DATE(created_at) = ?
    ", [$item['id'], $today]);

    if (!$exists) {
        dbInsert('notifications', [
            'user_id' => $item['assigned_to'],
            'case_provider_id' => $item['id'],
            'type' => 'followup_due',
            'message' => "Follow-up due: {$item['provider_name']} for {$item['client_name']} ({$item['case_number']})",
            'due_date' => $today
        ]);
        echo "  Created follow-up notification for case_provider #{$item['id']}\n";
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
    WHERE cp.overall_status NOT IN ('received_complete', 'verified')
      AND cp.deadline BETWEEN ? AND ?
      AND cp.assigned_to IS NOT NULL
", [$today, $warningDate]);

foreach ($deadlineWarnings as $item) {
    $exists = dbFetchOne("
        SELECT id FROM notifications
        WHERE case_provider_id = ? AND type = 'deadline_warning' AND DATE(created_at) = ?
    ", [$item['id'], $today]);

    if (!$exists) {
        $daysLeft = (int)((strtotime($item['deadline']) - strtotime($today)) / 86400);
        dbInsert('notifications', [
            'user_id' => $item['assigned_to'],
            'case_provider_id' => $item['id'],
            'type' => 'deadline_warning',
            'message' => "Deadline in {$daysLeft} days: {$item['provider_name']} for {$item['client_name']} ({$item['case_number']})",
            'due_date' => $item['deadline']
        ]);
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
    WHERE cp.overall_status NOT IN ('received_complete', 'verified')
      AND cp.deadline < ?
      AND cp.assigned_to IS NOT NULL
", [$today]);

foreach ($overdue as $item) {
    $exists = dbFetchOne("
        SELECT id FROM notifications
        WHERE case_provider_id = ? AND type = 'deadline_overdue' AND DATE(created_at) = ?
    ", [$item['id'], $today]);

    if (!$exists) {
        $daysOver = (int)((strtotime($today) - strtotime($item['deadline'])) / 86400);
        // Notify assigned staff
        dbInsert('notifications', [
            'user_id' => $item['assigned_to'],
            'case_provider_id' => $item['id'],
            'type' => 'deadline_overdue',
            'message' => "OVERDUE ({$daysOver} days): {$item['provider_name']} for {$item['client_name']} ({$item['case_number']})",
            'due_date' => $item['deadline']
        ]);

        // Also notify admin (Ella - user id 1)
        $adminId = 1;
        if ($item['assigned_to'] != $adminId) {
            dbInsert('notifications', [
                'user_id' => $adminId,
                'case_provider_id' => $item['id'],
                'type' => 'deadline_overdue',
                'message' => "OVERDUE ({$daysOver} days): {$item['provider_name']} for {$item['client_name']} ({$item['case_number']})",
                'due_date' => $item['deadline']
            ]);
        }
        echo "  Created overdue notification for case_provider #{$item['id']}\n";
    }
}

// 4. Escalation notifications (deadline reached → managers + action_needed, deadline+14d → admins)
echo "Checking escalation notifications...\n";
$escCreated = generateEscalationNotifications();
echo "  Created {$escCreated} escalation notification(s)\n";

echo "\nDone.\n";
