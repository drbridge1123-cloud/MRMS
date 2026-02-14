<?php
function renderStatusBadge($status) {
    $label = str_replace('_', ' ', ucfirst($status));
    $labels = [
        'not_started' => 'Not Started',
        'requesting' => 'Requesting',
        'follow_up' => 'Follow Up',
        'received_partial' => 'Partial',
        'received_complete' => 'Complete',
        'verified' => 'Verified',
        'active' => 'Active',
        'pending_review' => 'Review',
        'completed' => 'Completed',
        'on_hold' => 'On Hold',
    ];
    $display = $labels[$status] ?? $label;
    return '<span class="status-badge status-' . htmlspecialchars($status) . '">' . htmlspecialchars($display) . '</span>';
}

function renderDifficultyBadge($level) {
    $labels = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];
    $display = $labels[$level] ?? $level;
    return '<span class="status-badge difficulty-' . htmlspecialchars($level) . '">' . htmlspecialchars($display) . '</span>';
}
?>
