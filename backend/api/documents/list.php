<?php
// GET /api/documents - List documents for a case
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

require_once __DIR__ . '/../../helpers/file-upload.php';

// Get filters
$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : null;
$caseProviderId = isset($_GET['case_provider_id']) ? (int)$_GET['case_provider_id'] : null;
$documentType = $_GET['document_type'] ?? null;

if (!$caseId) {
    errorResponse('case_id parameter is required', 400);
}

// Verify case exists
$case = dbFetchOne("SELECT id FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Build query
$sql = "SELECT
    cd.id,
    cd.case_id,
    cd.case_provider_id,
    cd.document_type,
    cd.file_name,
    cd.original_file_name,
    cd.file_path,
    cd.file_size,
    cd.mime_type,
    cd.uploaded_by,
    cd.notes,
    cd.created_at,
    cd.is_provider_template,
    cd.provider_name_x,
    cd.provider_name_y,
    cd.provider_name_width,
    cd.provider_name_height,
    cd.provider_name_font_size,
    cd.use_date_overlay,
    cd.date_x,
    cd.date_y,
    cd.date_width,
    cd.date_height,
    cd.date_font_size,
    cd.use_custom_text_overlay,
    cd.custom_text_value,
    cd.custom_text_x,
    cd.custom_text_y,
    cd.custom_text_width,
    cd.custom_text_height,
    cd.custom_text_font_size,
    (SELECT full_name FROM users WHERE id = cd.uploaded_by) AS uploaded_by_name
FROM case_documents cd
WHERE cd.case_id = ?";

$params = [$caseId];

// Add optional filters
if ($caseProviderId) {
    $sql .= " AND cd.case_provider_id = ?";
    $params[] = $caseProviderId;
}

if ($documentType) {
    $sql .= " AND cd.document_type = ?";
    $params[] = $documentType;
}

$sql .= " ORDER BY cd.created_at DESC";

$documents = dbFetchAll($sql, $params);

// Add formatted file size
foreach ($documents as &$doc) {
    $doc['file_size_formatted'] = formatBytes($doc['file_size']);
}

successResponse([
    'documents' => $documents,
    'total' => count($documents)
], 'Retrieved ' . count($documents) . ' document(s)');
