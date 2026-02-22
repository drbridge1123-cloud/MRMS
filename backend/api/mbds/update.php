<?php
// PUT /api/mbds/{id} - Update report settings (insurance names, toggles)
$userId = requireAuth();

$reportId = (int)($_GET['id'] ?? 0);
if (!$reportId) {
    errorResponse('Report ID is required', 400);
}

$report = dbFetchOne("SELECT * FROM mbds_reports WHERE id = ?", [$reportId]);
if (!$report) {
    errorResponse('Report not found', 404);
}

$input = getInput();
$updateData = [];

// Reopen as draft
if (!empty($input['status']) && $input['status'] === 'draft') {
    if (!in_array($report['status'], ['completed', 'approved'])) {
        errorResponse('Report is already in draft status');
    }

    // Get case info for logging and status revert
    $case = dbFetchOne("SELECT id, case_number FROM cases WHERE id = ?", [$report['case_id']]);

    dbUpdate('mbds_reports', [
        'status' => 'draft',
        'completed_by' => null,
        'completed_at' => null,
        'approved_by' => null,
        'approved_at' => null,
    ], 'id = ?', [$reportId]);

    // Revert case status to verification (pre-completion stage)
    $newOwner = STATUS_OWNER_MAP['verification'] ?? null;
    $caseUpdate = ['status' => 'verification'];
    if ($newOwner) {
        $caseUpdate['assigned_to'] = $newOwner;
    }
    dbUpdate('cases', $caseUpdate, 'id = ?', [$report['case_id']]);

    logActivity($userId, 'mbds_reopened', 'mbds_report', $reportId, [
        'case_number' => $case['case_number'] ?? '',
        'previous_status' => $report['status'],
    ]);

    $updated = dbFetchOne("SELECT * FROM mbds_reports WHERE id = ?", [$reportId]);
    successResponse($updated, 'Report reopened as draft');
}

// Insurance carrier names
foreach (['pip1_name', 'pip2_name', 'health1_name', 'health2_name', 'health3_name'] as $field) {
    if (array_key_exists($field, $input)) {
        $updateData[$field] = $input[$field] ? sanitizeString($input[$field]) : null;
    }
}

// Toggle special line types
$toggleMap = [
    'has_wage_loss' => 'wage_loss',
    'has_essential_service' => 'essential_service',
    'has_health_subrogation' => 'health_subrogation',
    'has_health_subrogation2' => 'health_subrogation2',
];

$labelMap = [
    'wage_loss' => 'WAGE LOSS',
    'essential_service' => 'ESSENTIAL SERVICE',
    'health_subrogation' => 'HEALTH SUBROGATION',
    'health_subrogation2' => 'HEALTH SUBROGATION #2',
];

foreach ($toggleMap as $field => $lineType) {
    if (array_key_exists($field, $input)) {
        $newVal = $input[$field] ? 1 : 0;
        $updateData[$field] = $newVal;

        if ($newVal && !(int)$report[$field]) {
            // Toggled ON: create the line
            $maxSort = dbFetchOne(
                "SELECT MAX(sort_order) AS ms FROM mbds_lines WHERE report_id = ? AND line_type IN ('wage_loss','essential_service','health_subrogation','health_subrogation2')",
                [$reportId]
            );
            dbInsert('mbds_lines', [
                'report_id' => $reportId,
                'line_type' => $lineType,
                'provider_name' => $labelMap[$lineType],
                'sort_order' => ((int)($maxSort['ms'] ?? 0)) + 1
            ]);
        } elseif (!$newVal && (int)$report[$field]) {
            // Toggled OFF: delete the line
            dbDelete('mbds_lines', 'report_id = ? AND line_type = ?', [$reportId, $lineType]);
        }
    }
}

if (!empty($input['notes'])) {
    $updateData['notes'] = sanitizeString($input['notes']);
} elseif (array_key_exists('notes', $input)) {
    $updateData['notes'] = null;
}

if (!empty($updateData)) {
    dbUpdate('mbds_reports', $updateData, 'id = ?', [$reportId]);
}

$updated = dbFetchOne("SELECT * FROM mbds_reports WHERE id = ?", [$reportId]);
$updated['has_wage_loss'] = (int)$updated['has_wage_loss'];
$updated['has_essential_service'] = (int)$updated['has_essential_service'];
$updated['has_health_subrogation'] = (int)$updated['has_health_subrogation'];
$updated['has_health_subrogation2'] = (int)$updated['has_health_subrogation2'];

successResponse($updated, 'Report updated');
