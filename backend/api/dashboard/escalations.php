<?php
if ($method !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();
$user = getCurrentUser();

// On-demand: generate escalation notifications for today
generateEscalationNotifications();

$items = getEscalatedItems($user['role'], $userId);

successResponse($items);
