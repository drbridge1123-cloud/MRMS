<?php
// POST /api/documents/generate-provider-version - Generate provider-specific PDF from template
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$input = getInput();

require_once __DIR__ . '/../../helpers/pdf-overlay.php';

// Validate required fields
if (empty($input['document_id'])) {
    errorResponse('document_id is required', 400);
}

if (empty($input['provider_name'])) {
    errorResponse('provider_name is required', 400);
}

$documentId = (int)$input['document_id'];
$providerName = sanitizeString($input['provider_name']);
$caseId = !empty($input['case_id']) ? (int)$input['case_id'] : null;

// Get the template document
$template = dbFetchOne(
    "SELECT * FROM case_documents WHERE id = ? AND is_provider_template = 1",
    [$documentId]
);

if (!$template) {
    errorResponse('Template document not found', 404);
}

// If case_id not provided, use the template's case_id
if (!$caseId) {
    $caseId = $template['case_id'];
}

// Verify user has access to this case
$case = dbFetchOne("SELECT id, case_number FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Generate the provider-specific PDF
$outputDir = __DIR__ . '/../../../storage/documents/generated';
$result = generateProviderDocument($documentId, $providerName, $outputDir);

if (!$result['success']) {
    errorResponse($result['error'], 422);
}

// Get file info
$fullPath = $result['file_path'];
$fileSize = filesize($fullPath);
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($fullPath);

// Calculate relative path from storage/
$relativePath = 'documents/generated/' . $result['filename'];

// Insert the generated document as a new case_document
$documentData = [
    'case_id' => $caseId,
    'case_provider_id' => $template['case_provider_id'],
    'document_type' => $template['document_type'],
    'file_name' => $result['filename'],
    'original_file_name' => $providerName . ' - ' . $template['original_file_name'],
    'file_path' => $relativePath,
    'file_size' => $fileSize,
    'mime_type' => $mimeType,
    'uploaded_by' => $userId,
    'notes' => 'Generated from template #' . $documentId . ' for provider: ' . $providerName,
    'is_provider_template' => 0  // Generated documents are not templates
];

$newDocumentId = dbInsert('case_documents', $documentData);

// Log activity
logActivity($userId, 'document_generated', 'case_document', $newDocumentId, [
    'case_id' => $caseId,
    'case_number' => $case['case_number'],
    'template_id' => $documentId,
    'provider_name' => $providerName,
    'file_name' => $result['filename']
]);

// Return the new document info
successResponse([
    'id' => $newDocumentId,
    'file_name' => $result['filename'],
    'original_file_name' => $documentData['original_file_name'],
    'file_size' => $fileSize,
    'file_size_formatted' => formatBytes($fileSize),
    'provider_name' => $providerName
], 'Provider-specific document generated successfully');
