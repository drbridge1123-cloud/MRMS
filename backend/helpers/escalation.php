<?php

function getEscalationTier($daysPastDeadline) {
    if ($daysPastDeadline !== null && $daysPastDeadline >= ADMIN_ESCALATION_DAYS_AFTER_DEADLINE) return 'admin';
    if ($daysPastDeadline !== null && $daysPastDeadline >= 0) return 'action_needed';
    return 'normal';
}

function getEscalationInfo($daysPastDeadline) {
    $tier = getEscalationTier($daysPastDeadline);
    $info = [
        'normal' => ['tier' => 'normal', 'label' => 'Normal', 'css' => 'escalation-normal'],
        'action_needed' => ['tier' => 'action_needed', 'label' => 'Action Needed', 'css' => 'escalation-action-needed'],
        'admin' => ['tier' => 'admin', 'label' => 'Admin Escalation', 'css' => 'escalation-admin'],
    ];
    return $info[$tier];
}

function generateEscalationNotifications() {
    $today = date('Y-m-d');
    $created = 0;

    // 1. Deadline reached (0+ days past) → notify managers, set action_needed
    $actionNeededItems = dbFetchAll("
        SELECT cp.id, cp.assigned_to, cp.case_id, cp.overall_status, cp.deadline,
               c.case_number, c.client_name,
               p.name AS provider_name,
               DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        WHERE cp.overall_status NOT IN ('action_needed', 'received_complete', 'verified')
          AND c.status NOT IN ('completed','closed')
          AND cp.deadline IS NOT NULL
          AND cp.deadline <= CURDATE()
    ");

    foreach ($actionNeededItems as $item) {
        $provName = $item['provider_name'];
        $clientInfo = "{$item['client_name']} ({$item['case_number']})";
        $daysPast = (int)$item['days_past_deadline'];

        // Auto-set status to action_needed
        dbUpdate('case_providers', ['overall_status' => 'action_needed'], 'id = ?', [$item['id']]);

        $managers = dbFetchAll("SELECT id, email FROM users WHERE role = 'manager' AND is_active = 1");
        foreach ($managers as $mgr) {
            $exists = dbFetchOne(
                "SELECT id FROM notifications WHERE case_provider_id = ? AND type = 'escalation_action_needed' AND user_id = ? AND DATE(created_at) = ?",
                [$item['id'], $mgr['id'], $today]
            );
            if (!$exists) {
                dbInsert('notifications', [
                    'user_id' => (int)$mgr['id'],
                    'case_provider_id' => $item['id'],
                    'type' => 'escalation_action_needed',
                    'message' => "Action Needed (deadline reached): {$provName} for {$clientInfo}",
                    'due_date' => $today
                ]);
                $created++;

                if (!empty($mgr['email'])) {
                    sendEscalationEmail($mgr['email'], 'escalation_action_needed', $provName, $clientInfo, $daysPast, $item['case_id']);
                }
            }
        }
    }

    // 2. Deadline + 14 days overdue → notify admins
    $adminItems = dbFetchAll("
        SELECT cp.id, cp.assigned_to, cp.case_id, cp.deadline,
               c.case_number, c.client_name,
               p.name AS provider_name,
               DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        WHERE cp.overall_status NOT IN ('received_complete', 'verified')
          AND c.status NOT IN ('completed','closed')
          AND cp.deadline IS NOT NULL
          AND DATEDIFF(CURDATE(), cp.deadline) >= " . ADMIN_ESCALATION_DAYS_AFTER_DEADLINE
    );

    foreach ($adminItems as $item) {
        $provName = $item['provider_name'];
        $clientInfo = "{$item['client_name']} ({$item['case_number']})";
        $daysPast = (int)$item['days_past_deadline'];

        $admins = dbFetchAll("SELECT id, email FROM users WHERE role = 'admin' AND is_active = 1");
        foreach ($admins as $adm) {
            $exists = dbFetchOne(
                "SELECT id FROM notifications WHERE case_provider_id = ? AND type = 'escalation_admin' AND user_id = ? AND DATE(created_at) = ?",
                [$item['id'], $adm['id'], $today]
            );
            if (!$exists) {
                dbInsert('notifications', [
                    'user_id' => (int)$adm['id'],
                    'case_provider_id' => $item['id'],
                    'type' => 'escalation_admin',
                    'message' => "Admin Escalation ({$daysPast}d past deadline): {$provName} for {$clientInfo}",
                    'due_date' => $today
                ]);
                $created++;

                if (!empty($adm['email'])) {
                    sendEscalationEmail($adm['email'], 'escalation_admin', $provName, $clientInfo, $daysPast, $item['case_id']);
                }
            }
        }
    }

    return $created;
}

function sendEscalationEmail($to, $type, $providerName, $clientInfo, $daysPast, $caseId) {
    $tierLabels = [
        'escalation_action_needed' => ['Action Needed', '#d97706', "This provider has reached the 30-day deadline with no records received. Manager action required."],
        'escalation_admin' => ['Admin Escalation', '#dc2626', "This provider is {$daysPast} days past deadline. Immediate admin attention required."],
    ];

    $info = $tierLabels[$type] ?? ['Escalation', '#666', 'Action required.'];
    $subject = "[MRMS] {$info[0]}: {$providerName} - {$clientInfo}";
    $caseUrl = "http://localhost/MRMS/frontend/pages/cases/detail.php?id={$caseId}";

    $html = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:{$info[1]};color:white;padding:16px 24px;border-radius:8px 8px 0 0;'>
            <h2 style='margin:0;font-size:18px;'>{$info[0]}</h2>
        </div>
        <div style='border:1px solid #e5e7eb;border-top:none;padding:24px;border-radius:0 0 8px 8px;'>
            <p style='margin:0 0 12px;color:#374151;'><strong>Provider:</strong> {$providerName}</p>
            <p style='margin:0 0 12px;color:#374151;'><strong>Case:</strong> {$clientInfo}</p>
            <p style='margin:0 0 12px;color:#374151;'><strong>Days past deadline:</strong> <span style='color:{$info[1]};font-weight:bold;'>{$daysPast}</span></p>
            <p style='margin:0 0 20px;color:#6b7280;'>{$info[2]}</p>
            <a href='{$caseUrl}' style='display:inline-block;background:{$info[1]};color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;'>View Case</a>
        </div>
        <p style='color:#9ca3af;font-size:12px;margin-top:12px;text-align:center;'>Sent by MRMS - Medical Records Management System</p>
    </div>";

    sendEmail($to, $subject, $html);
}

function getEscalatedItems($role, $userId = null) {
    $baseQuery = "
        SELECT cp.id, cp.case_id, cp.deadline, cp.overall_status, cp.assigned_to,
               c.case_number, c.client_name,
               p.name AS provider_name, p.type AS provider_type,
               u.full_name AS assigned_name,
               DATEDIFF(CURDATE(), cp.deadline) AS days_past_deadline
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        LEFT JOIN users u ON cp.assigned_to = u.id
        WHERE cp.overall_status NOT IN ('received_complete', 'verified')
          AND c.status NOT IN ('completed','closed')
          AND cp.deadline IS NOT NULL
          AND cp.deadline <= CURDATE()";

    $params = [];

    // Staff: only see their own assigned items
    if ($role === 'staff') {
        $baseQuery .= " AND cp.assigned_to = ?";
        $params[] = $userId;
    }

    $baseQuery .= " ORDER BY days_past_deadline DESC";
    $rows = dbFetchAll($baseQuery, $params);

    foreach ($rows as &$row) {
        $daysPast = $row['days_past_deadline'] !== null ? (int)$row['days_past_deadline'] : null;
        $esc = getEscalationInfo($daysPast);
        $row['escalation_tier'] = $esc['tier'];
        $row['escalation_label'] = $esc['label'];
        $row['escalation_css'] = $esc['css'];
    }
    unset($row);

    return $rows;
}
