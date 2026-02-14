<?php
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$input = getInput();
$errors = validateRequired($input, ['case_id', 'provider_id']);
if (!empty($errors)) {
    errorResponse(implode(', ', $errors));
}

$caseId = (int)$input['case_id'];
$providerId = (int)$input['provider_id'];

// Check that the case exists
$case = dbFetchOne("SELECT id, case_number FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Check that the provider exists
$provider = dbFetchOne("SELECT id, name FROM providers WHERE id = ?", [$providerId]);
if (!$provider) {
    errorResponse('Provider not found', 404);
}

// Check for duplicate link
$existing = dbFetchOne(
    "SELECT id FROM case_providers WHERE case_id = ? AND provider_id = ?",
    [$caseId, $providerId]
);
if ($existing) {
    errorResponse('This provider is already linked to the case');
}

// Build insert data
$data = [
    'case_id' => $caseId,
    'provider_id' => $providerId,
    'overall_status' => 'not_started'
];

if (!empty($input['treatment_start_date'])) {
    if (!validateDate($input['treatment_start_date'])) {
        errorResponse('Invalid treatment_start_date format (YYYY-MM-DD)');
    }
    $data['treatment_start_date'] = $input['treatment_start_date'];
}

if (!empty($input['treatment_end_date'])) {
    if (!validateDate($input['treatment_end_date'])) {
        errorResponse('Invalid treatment_end_date format (YYYY-MM-DD)');
    }
    $data['treatment_end_date'] = $input['treatment_end_date'];
}

if (isset($input['record_types_needed'])) {
    $data['record_types_needed'] = sanitizeString($input['record_types_needed']);
}

if (!empty($input['assigned_to'])) {
    $data['assigned_to'] = (int)$input['assigned_to'];
}

// Auto-calculate deadline if not provided
if (!empty($input['deadline'])) {
    if (!validateDate($input['deadline'])) {
        errorResponse('Invalid deadline format (YYYY-MM-DD)');
    }
    $data['deadline'] = $input['deadline'];
} else {
    $data['deadline'] = calculateDeadline();
}

if (isset($input['notes'])) {
    $data['notes'] = sanitizeString($input['notes']);
}

$newId = dbInsert('case_providers', $data);

logActivity($userId, 'created', 'case_provider', $newId, [
    'case_id' => $caseId,
    'provider_id' => $providerId,
    'provider_name' => $provider['name']
]);

$record = dbFetchOne("SELECT * FROM case_providers WHERE id = ?", [$newId]);

successResponse($record, 'Provider linked to case successfully');
