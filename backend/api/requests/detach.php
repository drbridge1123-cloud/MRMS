<?php
// DELETE /api/requests/{id}/attachments/{document_id} - Detach document from request
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
$documentId = (int)($_GET['document_id'] ?? 0);

if (!$requestId) {
    errorResponse('Request ID is required', 400);
}

if (!$documentId) {
    errorResponse('Document ID is required', 400);
}

// Verify request exists and is not already sent
$request = dbFetchOne(
    "SELECT rr.*, (SELECT case_number FROM cases WHERE id = (SELECT case_id FROM case_providers WHERE id = rr.case_provider_id)) AS case_number
     FROM record_requests rr WHERE id = ?",
    [$requestId]
);

if (!$request) {
    errorResponse('Request not found', 404);
}

if ($request['send_status'] === 'sent') {
    errorResponse('Cannot detach documents from already sent requests', 422);
}

// Verify attachment exists
$attachment = dbFetchOne(
    "SELECT ra.*, cd.original_file_name
     FROM request_attachments ra
     JOIN case_documents cd ON ra.case_document_id = cd.id
     WHERE ra.record_request_id = ? AND ra.case_document_id = ?",
    [$requestId, $documentId]
);

if (!$attachment) {
    errorResponse('Attachment not found', 404);
}

// Delete attachment
dbExecute(
    "DELETE FROM request_attachments WHERE record_request_id = ? AND case_document_id = ?",
    [$requestId, $documentId]
);

// Log activity
logActivity($userId, 'document_detached', 'record_request', $requestId, [
    'document_id' => $documentId,
    'file_name' => $attachment['original_file_name'],
    'case_number' => $request['case_number']
]);

successResponse(['detached' => true], 'Document detached successfully');
