<?php
// GET /api/documents/{id}/download - Download document file
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

require_once __DIR__ . '/../../helpers/file-upload.php';

$documentId = (int)($_GET['id'] ?? 0);
if (!$documentId) {
    errorResponse('Document ID is required', 400);
}

// Fetch document
$document = dbFetchOne(
    "SELECT
        cd.*,
        (SELECT case_number FROM cases WHERE id = cd.case_id) AS case_number
    FROM case_documents cd
    WHERE cd.id = ?",
    [$documentId]
);

if (!$document) {
    errorResponse('Document not found', 404);
}

// Get full file path and verify it exists
$fullPath = getStoredFilePath($document['file_path']);

if (!$fullPath) {
    errorResponse('File not found or path invalid', 404);
}

// Log download activity
logActivity($userId, 'document_downloaded', 'case_document', $documentId, [
    'case_id' => $document['case_id'],
    'case_number' => $document['case_number'],
    'file_name' => $document['original_file_name']
]);

// Set headers for file download
header('Content-Type: ' . $document['mime_type']);
header('Content-Disposition: attachment; filename="' . $document['original_file_name'] . '"');
header('Content-Length: ' . $document['file_size']);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($fullPath);
exit;
