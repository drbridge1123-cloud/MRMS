<?php
if ($method !== 'PUT') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$notifId = $_GET['id'] ?? $id ?? null;

// Mark all as read
if ($notifId === 'read-all') {
    $count = dbUpdate(
        'notifications',
        ['is_read' => 1],
        'user_id = ? AND is_read = 0',
        [$userId]
    );

    successResponse(['marked_count' => $count], 'All notifications marked as read');
}

// Mark single notification as read
$notifId = (int)$notifId;
if (!$notifId) {
    errorResponse('Notification ID is required');
}

// Verify the notification belongs to the current user
$notification = dbFetchOne(
    "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
    [$notifId, $userId]
);
if (!$notification) {
    errorResponse('Notification not found', 404);
}

dbUpdate('notifications', ['is_read' => 1], 'id = ?', [$notifId]);

successResponse(null, 'Notification marked as read');
