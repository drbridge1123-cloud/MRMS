<?php
// POST /api/mbds/{id}/lines - Add a new line to MBDS report
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
$errors = validateRequired($input, ['line_type']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$allowedTypes = ['provider', 'wage_loss', 'essential_service', 'health_subrogation', 'health_subrogation2', 'rx'];
if (!in_array($input['line_type'], $allowedTypes)) {
    errorResponse('Invalid line type');
}

// Get max sort_order
$maxSort = dbFetchOne("SELECT MAX(sort_order) AS ms FROM mbds_lines WHERE report_id = ?", [$reportId]);

$lineData = [
    'report_id' => $reportId,
    'line_type' => $input['line_type'],
    'provider_name' => !empty($input['provider_name']) ? sanitizeString($input['provider_name']) : strtoupper(str_replace('_', ' ', $input['line_type'])),
    'sort_order' => ((int)($maxSort['ms'] ?? 0)) + 10
];

// Record types
if (!empty($input['record_types_needed'])) {
    $allowed = ['medical_records','billing','chart','imaging','op_report'];
    $values = array_filter(explode(',', $input['record_types_needed']), fn($v) => in_array(trim($v), $allowed));
    $lineData['record_types_needed'] = $values ? implode(',', $values) : null;
}

// If provider line with provider_id: auto-create case_provider + cost entry
$caseProviderId = null;
if ($input['line_type'] === 'provider' && !empty($input['provider_id'])) {
    $providerId = (int)$input['provider_id'];
    $caseId = $report['case_id'];

    // Check provider exists
    $provider = dbFetchOne("SELECT id, name FROM providers WHERE id = ?", [$providerId]);
    if (!$provider) {
        errorResponse('Provider not found', 404);
    }

    // Use provider name from DB
    $lineData['provider_name'] = $provider['name'];

    // Find or create case_provider
    $existingCp = dbFetchOne(
        "SELECT id FROM case_providers WHERE case_id = ? AND provider_id = ?",
        [$caseId, $providerId]
    );

    if ($existingCp) {
        $caseProviderId = $existingCp['id'];
        // Update record_types if provided
        if (!empty($lineData['record_types_needed'])) {
            dbUpdate('case_providers', ['record_types_needed' => $lineData['record_types_needed']], 'id = ?', [$caseProviderId]);
        }
    } else {
        $cpData = [
            'case_id' => $caseId,
            'provider_id' => $providerId,
            'overall_status' => 'not_started',
        ];
        if (!empty($lineData['record_types_needed'])) {
            $cpData['record_types_needed'] = $lineData['record_types_needed'];
        }
        // Auto-set deadline 30 days from now
        $cpData['deadline'] = date('Y-m-d', strtotime('+30 days'));
        $caseProviderId = dbInsert('case_providers', $cpData);
    }

    $lineData['case_provider_id'] = $caseProviderId;

    // Auto-create cost ledger entry (Record Fee) if doesn't exist
    $existingFee = dbFetchOne(
        "SELECT id FROM mr_fee_payments WHERE case_id = ? AND case_provider_id = ? AND description = 'Record Fee'",
        [$caseId, $caseProviderId]
    );
    if (!$existingFee) {
        dbInsert('mr_fee_payments', [
            'case_id' => $caseId,
            'case_provider_id' => $caseProviderId,
            'expense_category' => 'mr_cost',
            'provider_name' => $provider['name'],
            'description' => 'Record Fee',
            'billed_amount' => 0,
            'paid_amount' => 0,
            'payment_date' => date('Y-m-d'),
            'created_by' => $userId,
        ]);
    }
}

$lineId = dbInsert('mbds_lines', $lineData);

$newLine = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);
successResponse($newLine, 'Line added');
