<?php
// DELETE /api/documents/{id} - Delete document
if ($method !== 'DELETE') {
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
    "SELECT cd.*, (SELECT case_number FROM cases WHERE id = cd.case_id) AS case_number
     FROM case_documents cd WHERE cd.id = ?",
    [$documentId]
);

if (!$document) {
    errorResponse('Document not found', 404);
}

// Check if request_attachments table exists
$tableExists = false;
try {
    $pdo = getDBConnection();
    $result = $pdo->query("SHOW TABLES LIKE 'request_attachments'");
    $tableExists = $result->rowCount() > 0;
} catch (Exception $e) {
    // Table doesn't exist, continue without attachment checks
    $tableExists = false;
}

// Check attachment constraints only if table exists
if ($tableExists) {
    $attachedToSent = dbFetchOne(
        "SELECT COUNT(*) as count
         FROM request_attachments ra
         JOIN record_requests rr ON ra.record_request_id = rr.id
         WHERE ra.case_document_id = ? AND rr.send_status = 'sent'",
        [$documentId]
    );

    if ($attachedToSent && $attachedToSent['count'] > 0) {
        errorResponse('Cannot delete document that has been sent with ' . $attachedToSent['count'] . ' request(s)', 422);
    }
}

$pdo->beginTransaction();

try {
    // Remove from request_attachments if table exists
    if ($tableExists) {
        dbDelete('request_attachments', 'case_document_id = ?', [$documentId]);
    }

    // Delete document record
    dbDelete('case_documents', 'id = ?', [$documentId]);

    // Delete physical file
    $deleteResult = deleteStoredFile($document['file_path']);
    if (!$deleteResult['success']) {
        // Log warning but don't fail the transaction
        error_log("Failed to delete file: " . $document['file_path'] . " - " . $deleteResult['error']);
    }

    // Log activity
    logActivity($userId, 'document_deleted', 'case_document', $documentId, [
        'case_id' => $document['case_id'],
        'case_number' => $document['case_number'],
        'file_name' => $document['original_file_name']
    ]);

    $pdo->commit();

    successResponse(['deleted' => true], 'Document deleted successfully');

} catch (Exception $e) {
    $pdo->rollBack();
    errorResponse('Failed to delete document: ' . $e->getMessage(), 500);
}
