<?php

function getEscalationTier($daysSinceFirstRequest) {
    if ($daysSinceFirstRequest === null) return 'normal';
    if ($daysSinceFirstRequest >= ESCALATION_ADMIN_DAYS) return 'admin';
    if ($daysSinceFirstRequest >= ESCALATION_MANAGER_DAYS) return 'manager';
    if ($daysSinceFirstRequest >= ESCALATION_ACTION_NEEDED_DAYS) return 'action_needed';
    return 'normal';
}

function getEscalationInfo($daysSinceFirstRequest) {
    $tier = getEscalationTier($daysSinceFirstRequest);
    $info = [
        'normal' => ['tier' => 'normal', 'label' => 'Normal', 'css' => 'escalation-normal'],
        'action_needed' => ['tier' => 'action_needed', 'label' => 'Action Needed', 'css' => 'escalation-action-needed'],
        'manager' => ['tier' => 'manager', 'label' => 'Manager Review', 'css' => 'escalation-manager'],
        'admin' => ['tier' => 'admin', 'label' => 'Admin Escalation', 'css' => 'escalation-admin'],
    ];
    return $info[$tier];
}

function generateEscalationNotifications() {
    $today = date('Y-m-d');

    $items = dbFetchAll("
        SELECT cp.id, cp.assigned_to, cp.case_id,
               c.case_number, c.client_name,
               p.name AS provider_name,
               MIN(rr.request_date) AS first_request_date,
               DATEDIFF(CURDATE(), MIN(rr.request_date)) AS days_since_first_request
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        LEFT JOIN record_requests rr ON rr.case_provider_id = cp.id
        WHERE cp.overall_status NOT IN ('received_complete', 'verified')
          AND c.status = 'active'
        GROUP BY cp.id
        HAVING first_request_date IS NOT NULL
    ");

    $created = 0;

    foreach ($items as $item) {
        $days = (int)$item['days_since_first_request'];
        $tier = getEscalationTier($days);
        if ($tier === 'normal') continue;

        $provName = $item['provider_name'];
        $clientInfo = "{$item['client_name']} ({$item['case_number']})";
        $notifications = [];

        // Action needed: notify assigned staff
        if ($item['assigned_to']) {
            $notifications[] = [
                'user_id' => (int)$item['assigned_to'],
                'type' => 'escalation_action_needed',
                'message' => "Action needed ({$days}d): {$provName} for {$clientInfo}"
            ];
        }

        // Manager escalation (42+ days)
        if ($tier === 'manager' || $tier === 'admin') {
            $managers = dbFetchAll("SELECT id, email FROM users WHERE role = 'manager' AND is_active = 1");
            foreach ($managers as $mgr) {
                $notifications[] = [
                    'user_id' => (int)$mgr['id'],
                    'type' => 'escalation_manager',
                    'message' => "Manager escalation ({$days}d): {$provName} for {$clientInfo}",
                    'email' => $mgr['email'] ?? null
                ];
            }
        }

        // Admin escalation (60+ days)
        if ($tier === 'admin') {
            $admins = dbFetchAll("SELECT id, email FROM users WHERE role = 'admin' AND is_active = 1");
            foreach ($admins as $adm) {
                $notifications[] = [
                    'user_id' => (int)$adm['id'],
                    'type' => 'escalation_admin',
                    'message' => "Admin escalation ({$days}d): {$provName} for {$clientInfo}",
                    'email' => $adm['email'] ?? null
                ];
            }
        }

        // Insert notifications + send emails (skip if already created today)
        foreach ($notifications as $notif) {
            $exists = dbFetchOne(
                "SELECT id FROM notifications WHERE case_provider_id = ? AND type = ? AND user_id = ? AND DATE(created_at) = ?",
                [$item['id'], $notif['type'], $notif['user_id'], $today]
            );

            if (!$exists) {
                dbInsert('notifications', [
                    'user_id' => $notif['user_id'],
                    'case_provider_id' => $item['id'],
                    'type' => $notif['type'],
                    'message' => $notif['message'],
                    'due_date' => $today
                ]);
                $created++;

                // Send escalation email
                if (!empty($notif['email'])) {
                    sendEscalationEmail($notif['email'], $notif['type'], $provName, $clientInfo, $days, $item['case_id']);
                }
            }
        }
    }

    return $created;
}

function sendEscalationEmail($to, $type, $providerName, $clientInfo, $days, $caseId) {
    $tierLabels = [
        'escalation_action_needed' => ['Action Needed', '#d97706', 'This provider request has been pending for over 30 days. Please take action.'],
        'escalation_manager' => ['Manager Escalation', '#ea580c', 'This provider request has been pending for over 6 weeks and requires manager attention.'],
        'escalation_admin' => ['Admin Escalation', '#dc2626', 'This provider request has been pending for over 2 months and requires immediate admin attention.'],
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
            <p style='margin:0 0 12px;color:#374151;'><strong>Days since first request:</strong> <span style='color:{$info[1]};font-weight:bold;'>{$days} days</span></p>
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
               MIN(rr.request_date) AS first_request_date,
               DATEDIFF(CURDATE(), MIN(rr.request_date)) AS days_since_first_request
        FROM case_providers cp
        JOIN cases c ON c.id = cp.case_id
        JOIN providers p ON p.id = cp.provider_id
        LEFT JOIN users u ON cp.assigned_to = u.id
        LEFT JOIN record_requests rr ON rr.case_provider_id = cp.id
        WHERE cp.overall_status NOT IN ('received_complete', 'verified')
          AND c.status = 'active'
        GROUP BY cp.id
        HAVING first_request_date IS NOT NULL";

    $params = [];

    if ($role === 'staff') {
        $baseQuery .= " AND days_since_first_request >= " . ESCALATION_ACTION_NEEDED_DAYS;
        $baseQuery .= " AND cp.assigned_to = ?";
        $params[] = $userId;
    } elseif ($role === 'manager') {
        $baseQuery .= " AND days_since_first_request >= " . ESCALATION_MANAGER_DAYS;
    } else {
        $baseQuery .= " AND days_since_first_request >= " . ESCALATION_ACTION_NEEDED_DAYS;
    }

    $baseQuery .= " ORDER BY days_since_first_request DESC";
    $rows = dbFetchAll($baseQuery, $params);

    foreach ($rows as &$row) {
        $esc = getEscalationInfo((int)$row['days_since_first_request']);
        $row['escalation_tier'] = $esc['tier'];
        $row['escalation_label'] = $esc['label'];
        $row['escalation_css'] = $esc['css'];
    }
    unset($row);

    return $rows;
}
