<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

if (empty($_GET['case_id'])) {
    errorResponse('case_id is required');
}

$caseId = (int)$_GET['case_id'];

// Sorting
$sortColumns = [
    'provider_name' => 'p.name',
    'provider_type' => 'p.type',
    'overall_status' => 'cp.overall_status',
    'first_request_date' => 'first_request_date',
    'last_request_date' => 'last_request_date',
    'deadline' => 'cp.deadline',
    'assigned_name' => 'u.full_name',
    'created_at' => 'cp.created_at',
];
$sortBy = $sortColumns[$_GET['sort_by'] ?? ''] ?? 'cp.created_at';
$sortDir = ($_GET['sort_dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$sql = "SELECT cp.*,
            p.name AS provider_name,
            p.type AS provider_type,
            p.phone AS provider_phone,
            p.fax AS provider_fax,
            p.email AS provider_email,
            p.preferred_method,
            u.full_name AS assigned_name,
            (SELECT MIN(r.request_date) FROM record_requests r WHERE r.case_provider_id = cp.id) AS first_request_date,
            (SELECT MAX(r.request_date) FROM record_requests r WHERE r.case_provider_id = cp.id) AS last_request_date,
            (SELECT COUNT(*) FROM record_requests r WHERE r.case_provider_id = cp.id) AS request_count,
            (SELECT COUNT(*) FROM record_requests r WHERE r.case_provider_id = cp.id AND r.request_type = 'follow_up') AS followup_count
        FROM case_providers cp
        JOIN providers p ON cp.provider_id = p.id
        LEFT JOIN users u ON cp.assigned_to = u.id
        WHERE cp.case_id = ?
        ORDER BY {$sortBy} {$sortDir}";

$rows = dbFetchAll($sql, [$caseId]);

foreach ($rows as &$row) {
    $row['days_since_request'] = $row['last_request_date'] ? daysElapsed($row['last_request_date']) : null;
    $row['is_overdue'] = isOverdue($row['deadline']);
    $row['days_until_deadline'] = daysUntil($row['deadline']);

    $daysPastDeadline = $row['deadline'] ? (int)((strtotime('today') - strtotime($row['deadline'])) / 86400) : null;
    $esc = getEscalationInfo($daysPastDeadline);
    $row['escalation_tier'] = $esc['tier'];
    $row['escalation_label'] = $esc['label'];
    $row['escalation_css'] = $esc['css'];

    // Load provider contacts (departments)
    $row['contacts'] = dbFetchAll(
        "SELECT id, department, contact_type, contact_value, is_primary
         FROM provider_contacts WHERE provider_id = ? ORDER BY is_primary DESC, department",
        [$row['provider_id']]
    );

    // Aggregate received record types from receipts
    $receipt = dbFetchOne(
        "SELECT MAX(has_medical_records) AS has_medical_records,
                MAX(has_billing) AS has_billing,
                MAX(has_chart) AS has_chart,
                MAX(has_imaging) AS has_imaging,
                MAX(has_op_report) AS has_op_report
         FROM record_receipts WHERE case_provider_id = ?",
        [$row['id']]
    );
    $row['received_types'] = $receipt ? [
        'medical_records' => (int)($receipt['has_medical_records'] ?? 0),
        'billing' => (int)($receipt['has_billing'] ?? 0),
        'chart' => (int)($receipt['has_chart'] ?? 0),
        'imaging' => (int)($receipt['has_imaging'] ?? 0),
        'op_report' => (int)($receipt['has_op_report'] ?? 0),
    ] : null;
}
unset($row);

successResponse($rows);
