<?php
// DELETE /api/requests/{id} - Delete a draft request
if ($method !== 'DELETE') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$requestId = (int)($_GET['id'] ?? 0);
if (!$requestId) {
    errorResponse('Request ID is required', 400);
}

// Load request
$request = dbFetchOne("SELECT id, send_status, case_provider_id FROM record_requests WHERE id = ?", [$requestId]);

if (!$request) {
    errorResponse('Request not found', 404);
}

// Only allow deleting draft or failed requests
if (!in_array($request['send_status'], ['draft', 'failed'])) {
    errorResponse('Can only delete draft or failed requests. This request has status: ' . $request['send_status'], 422);
}

// Delete the request
dbDelete('record_requests', 'id = ?', [$requestId]);

// Also delete any send_log entries
dbDelete('send_log', 'record_request_id = ?', [$requestId]);

// Recalculate case_provider status based on remaining requests
$cpId = $request['case_provider_id'];
$remainingRequests = dbFetchAll(
    "SELECT request_type, send_status FROM record_requests
     WHERE case_provider_id = ?
     ORDER BY request_date DESC",
    [$cpId]
);

// Determine new status based on remaining requests
if (empty($remainingRequests)) {
    // No requests left - back to pending
    $newStatus = 'pending';
} else {
    $latestRequest = $remainingRequests[0];
    if ($latestRequest['request_type'] === 'follow_up') {
        $newStatus = 'follow_up';
    } else {
        $newStatus = 'requesting';
    }
}

// Update case_provider status
dbUpdate('case_providers', ['overall_status' => $newStatus], 'id = ?', [$cpId]);

// Log activity
logActivity($userId, 'request_deleted', 'record_request', $requestId, [
    'case_provider_id' => $request['case_provider_id'],
    'send_status' => $request['send_status']
]);

successResponse(['deleted' => true], 'Request deleted successfully');
