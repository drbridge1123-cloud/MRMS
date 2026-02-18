<?php
// DELETE /api/mbds/lines/{id} - Delete a line from MBDS report
$userId = requireAuth();

$lineId = (int)($_GET['id'] ?? 0);
if (!$lineId) {
    errorResponse('Line ID is required', 400);
}

$line = dbFetchOne("SELECT * FROM mbds_lines WHERE id = ?", [$lineId]);
if (!$line) {
    errorResponse('Line not found', 404);
}

dbDelete('mbds_lines', 'id = ?', [$lineId]);

successResponse(null, 'Line deleted');
