<?php
// GET /api/documents/{id} - Get document metadata
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireAuth();

require_once __DIR__ . '/../../helpers/file-upload.php';

$documentId = (int)($_GET['id'] ?? 0);
if (!$documentId) {
    errorResponse('Document ID is required', 400);
}

// Fetch document with related data
$document = dbFetchOne(
    "SELECT
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
        (SELECT full_name FROM users WHERE id = cd.uploaded_by) AS uploaded_by_name,
        (SELECT case_number FROM cases WHERE id = cd.case_id) AS case_number,
        (SELECT provider_name FROM case_providers cp
         JOIN providers p ON cp.provider_id = p.id
         WHERE cp.id = cd.case_provider_id) AS provider_name
    FROM case_documents cd
    WHERE cd.id = ?",
    [$documentId]
);

if (!$document) {
    errorResponse('Document not found', 404);
}

// Add formatted file size
$document['file_size_formatted'] = formatBytes($document['file_size']);

// Check if document is attached to any requests
$attachments = dbFetchAll(
    "SELECT
        ra.id,
        ra.record_request_id,
        rr.request_date,
        rr.send_status
    FROM request_attachments ra
    JOIN record_requests rr ON ra.record_request_id = rr.id
    WHERE ra.case_document_id = ?
    ORDER BY ra.created_at DESC",
    [$documentId]
);

$document['attachments'] = $attachments;
$document['is_attached'] = count($attachments) > 0;

successResponse($document, 'Document retrieved successfully');
