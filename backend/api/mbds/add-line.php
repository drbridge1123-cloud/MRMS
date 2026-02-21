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

$lineId = dbInsert('mbds_lines', [
    'report_id' => $reportId,
    'line_type' => $input['line_type'],
    'provider_name' => !empty($input['provider_name']) ? sanitizeString($input['provider_name']) : strtoupper(str_replace('_', ' ', $input['line_type'])),
    'sort_order' => ((int)($maxSort['ms'] ?? 0)) + 10
]);

$newLine = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);
successResponse($newLine, 'Line added');
