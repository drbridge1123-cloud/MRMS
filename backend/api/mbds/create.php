<?php
// POST /api/mbds/{case_id} - Create MBDS report and auto-populate providers
$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('Case ID is required', 400);
}

$case = dbFetchOne("SELECT * FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// If report already exists, return it instead of error
$existing = dbFetchOne("SELECT id FROM mbds_reports WHERE case_id = ?", [$caseId]);
if ($existing) {
    $report = dbFetchOne(
        "SELECT r.*, c.case_number, c.client_name, c.doi, c.status AS case_status
         FROM mbds_reports r JOIN cases c ON c.id = r.case_id WHERE r.id = ?",
        [$existing['id']]
    );
    $lines = dbFetchAll("SELECT * FROM mbds_lines WHERE report_id = ? ORDER BY sort_order, id", [$existing['id']]);
    $report['lines'] = $lines;
    successResponse($report, 'Medical Balance report already exists');
}

// Create the report
$reportId = dbInsert('mbds_reports', [
    'case_id' => $caseId
]);

// Auto-populate health insurance names from received Health Tracker items
$healthItems = dbFetchAll(
    "SELECT insurance_carrier FROM health_ledger_items
     WHERE case_id = ? AND overall_status IN ('received', 'done')
     ORDER BY id LIMIT 3",
    [$caseId]
);
if (!empty($healthItems)) {
    $healthSlots = ['health1_name', 'health2_name', 'health3_name'];
    $healthUpdate = [];
    foreach ($healthItems as $idx => $hi) {
        $healthUpdate[$healthSlots[$idx]] = $hi['insurance_carrier'];
    }
    dbUpdate('mbds_reports', $healthUpdate, 'id = ?', [$reportId]);
}

// Auto-populate provider lines from case_providers
$providers = dbFetchAll(
    "SELECT cp.id AS cp_id, p.name, cp.treatment_start_date, cp.treatment_end_date, cp.record_types_needed
     FROM case_providers cp
     JOIN providers p ON p.id = cp.provider_id
     WHERE cp.case_id = ?
     ORDER BY cp.id",
    [$caseId]
);

$sortOrder = 10;
foreach ($providers as $prov) {
    $dates = '';
    if ($prov['treatment_start_date']) {
        $dates = date('m/d/Y', strtotime($prov['treatment_start_date']));
        if ($prov['treatment_end_date']) {
            $dates .= '-' . date('m/d/Y', strtotime($prov['treatment_end_date']));
        }
    }

    dbInsert('mbds_lines', [
        'report_id' => $reportId,
        'line_type' => 'provider',
        'provider_name' => $prov['name'],
        'case_provider_id' => $prov['cp_id'],
        'treatment_dates' => $dates ?: null,
        'record_types_needed' => $prov['record_types_needed'] ?: null,
        'sort_order' => $sortOrder
    ]);
    $sortOrder += 10;

    // Auto-create cost ledger entry (Record Fee) if doesn't exist
    $existingFee = dbFetchOne(
        "SELECT id FROM mr_fee_payments WHERE case_id = ? AND case_provider_id = ? AND description = 'Record Fee'",
        [$caseId, $prov['cp_id']]
    );
    if (!$existingFee) {
        dbInsert('mr_fee_payments', [
            'case_id' => $caseId,
            'case_provider_id' => $prov['cp_id'],
            'expense_category' => 'mr_cost',
            'provider_name' => $prov['name'],
            'description' => 'Record Fee',
            'billed_amount' => 0,
            'paid_amount' => 0,
            'payment_date' => date('Y-m-d'),
            'created_by' => $userId,
        ]);
    }
}

logActivity($userId, 'created', 'mbds_report', $reportId, [
    'case_number' => $case['case_number']
]);

// Return the full report
$report = dbFetchOne("SELECT * FROM mbds_reports WHERE id = ?", [$reportId]);
$lines = dbFetchAll("SELECT * FROM mbds_lines WHERE report_id = ? ORDER BY sort_order, id", [$reportId]);
$report['lines'] = $lines;

successResponse($report, 'Medical Balance report created');
