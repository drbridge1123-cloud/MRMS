<?php
// POST /api/templates/preview - Preview template with sample/real data
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

$input = getInput();

// Two modes:
// 1. Preview existing template with real request data: {template_id, request_id}
// 2. Preview custom template with sample data: {body_template, sample_data}

if (!empty($input['template_id']) && !empty($input['request_id'])) {
    // Mode 1: Preview existing template with real data
    $templateId = (int)$input['template_id'];
    $requestId = (int)$input['request_id'];

    // Load template
    $template = dbFetchOne("SELECT * FROM letter_templates WHERE id = ? AND is_active = 1", [$templateId]);
    if (!$template) {
        errorResponse('Template not found', 404);
    }

    // Load request data
    require_once __DIR__ . '/../../helpers/letter-template.php';
    $letterData = getRequestLetterData($requestId);
    if (!$letterData) {
        errorResponse('Request not found', 404);
    }

    // Render
    $html = renderLetterFromTemplate($templateId, $letterData);

    successResponse(['html' => $html]);

} elseif (!empty($input['body_template'])) {
    // Mode 2: Preview custom template with sample data
    $bodyTemplate = $input['body_template'];
    $sampleData = $input['sample_data'] ?? [];

    // Build sample data if not provided
    if (empty($sampleData)) {
        $sampleData = [
            'client_name' => 'Sample Client Name',
            'case_number' => 'CASE-12345',
            'client_dob' => '1980-01-15',
            'doi' => '2025-06-20',
            'attorney_name' => 'Attorney Name',
            'provider_name' => 'Sample Medical Provider',
            'provider_address' => "123 Medical Plaza\nSeattle, WA 98101",
            'request_date' => date('Y-m-d'),
            'treatment_start_date' => '2025-06-20',
            'treatment_end_date' => '2025-12-15',
            'record_types' => 'medical_records,billing',
            'notes' => 'Sample notes for preview',
            'authorization_sent' => true,
        ];
    }

    // Process placeholders
    require_once __DIR__ . '/../../helpers/letter-template.php';
    $html = processTemplatePlaceholders($bodyTemplate, $sampleData);

    successResponse(['html' => $html, 'sample_data' => $sampleData]);

} else {
    errorResponse('Either template_id+request_id or body_template must be provided', 400);
}
