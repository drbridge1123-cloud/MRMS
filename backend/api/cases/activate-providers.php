<?php
// POST /api/cases/{id}/activate-providers
// Activate treating providers → not_started (with staff assignment, deadline, record types)
// Auto-creates Cost Ledger entry and MBR line
$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

$case = dbFetchOne("SELECT id, case_number, client_name FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

$input = getInput();
$assignedTo = (int)($input['assigned_to'] ?? 0);
if (!$assignedTo) {
    errorResponse('Staff assignment is required', 400);
}

$staff = dbFetchOne("SELECT id, full_name FROM users WHERE id = ? AND is_active = 1", [$assignedTo]);
if (!$staff) {
    errorResponse('Staff member not found', 404);
}

// Record types to request
$recordTypes = [
    'request_mr' => !empty($input['request_mr']) ? 1 : 0,
    'request_bill' => !empty($input['request_bill']) ? 1 : 0,
    'request_chart' => !empty($input['request_chart']) ? 1 : 0,
    'request_img' => !empty($input['request_img']) ? 1 : 0,
    'request_op' => !empty($input['request_op']) ? 1 : 0,
];

// Get case_providers to activate (specific IDs or all treating)
$providerIds = $input['provider_ids'] ?? [];
if (!empty($providerIds)) {
    $placeholders = implode(',', array_fill(0, count($providerIds), '?'));
    $providers = dbFetchAll(
        "SELECT cp.*, p.name AS provider_name, p.charges_record_fee
         FROM case_providers cp
         JOIN providers p ON cp.provider_id = p.id
         WHERE cp.case_id = ? AND cp.id IN ($placeholders) AND cp.overall_status = 'treating'",
        array_merge([$caseId], array_map('intval', $providerIds))
    );
} else {
    $providers = dbFetchAll(
        "SELECT cp.*, p.name AS provider_name, p.charges_record_fee
         FROM case_providers cp
         JOIN providers p ON cp.provider_id = p.id
         WHERE cp.case_id = ? AND cp.overall_status = 'treating'",
        [$caseId]
    );
}

if (empty($providers)) {
    errorResponse('No treating providers to activate', 400);
}

$deadline = date('Y-m-d', strtotime('+30 days'));
$activated = 0;

foreach ($providers as $cp) {
    // Update status to not_started with assignment
    $updateData = array_merge([
        'overall_status' => 'not_started',
        'deadline' => $deadline,
        'assigned_to' => $assignedTo,
        'assignment_status' => 'pending',
        'activated_by' => $userId
    ], $recordTypes);
    dbUpdate('case_providers', $updateData, 'id = ?', [$cp['id']]);

    // Get activator name for message
    $activator = dbFetchOne("SELECT full_name FROM users WHERE id = ?", [$userId]);
    $activatorName = $activator['full_name'] ?? 'System';

    // Send assignment message
    dbInsert('messages', [
        'from_user_id' => $userId,
        'to_user_id' => $assignedTo,
        'subject' => "[Assignment] {$cp['provider_name']} — Case #{$case['case_number']}",
        'message' => "{$activatorName} assigned you to request records from {$cp['provider_name']} for case {$case['case_number']} ({$case['client_name']}).\n\nDeadline: " . date('M j, Y', strtotime($deadline))
    ]);

    // Auto-create Cost Ledger entry (Record Fee) if not exists
    $existingFee = dbFetchOne(
        "SELECT id FROM mr_fee_payments WHERE case_id = ? AND case_provider_id = ? AND description = 'Record Fee'",
        [$caseId, $cp['id']]
    );
    if (!$existingFee) {
        dbInsert('mr_fee_payments', [
            'case_id' => $caseId,
            'case_provider_id' => $cp['id'],
            'expense_category' => 'mr_cost',
            'provider_name' => $cp['provider_name'],
            'description' => 'Record Fee',
            'billed_amount' => 0,
            'paid_amount' => 0,
            'payment_date' => date('Y-m-d'),
            'created_by' => $userId,
        ]);
    }

    // Auto-create MBR line if report exists
    $report = dbFetchOne("SELECT id FROM mbds_reports WHERE case_id = ? ORDER BY id DESC LIMIT 1", [$caseId]);
    if ($report) {
        $existingLine = dbFetchOne(
            "SELECT id FROM mbds_lines WHERE report_id = ? AND case_provider_id = ?",
            [$report['id'], $cp['id']]
        );
        if (!$existingLine) {
            dbInsert('mbds_lines', [
                'report_id' => $report['id'],
                'line_type' => 'provider',
                'provider_name' => $cp['provider_name'],
                'case_provider_id' => $cp['id'],
                'charges' => 0,
                'ini_status' => 'complete'
            ]);
        }
    }

    $activated++;
}

// Auto-set ini_completed if no treating providers remain
$remainingTreating = dbCount('case_providers', "case_id = ? AND overall_status = 'treating'", [$caseId]);
if ($remainingTreating === 0) {
    dbUpdate('cases', ['ini_completed' => 1], 'id = ?', [$caseId]);
}

logActivity($userId, 'activated_providers', 'case', $caseId, [
    'activated_count' => $activated,
    'assigned_to' => $staff['full_name']
]);

successResponse([
    'activated' => $activated,
    'deadline' => $deadline,
    'assigned_to_name' => $staff['full_name']
], $activated . ' provider(s) activated');
