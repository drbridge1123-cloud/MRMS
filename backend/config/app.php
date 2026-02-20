<?php
define('APP_NAME', 'MRMS');
define('APP_VERSION', '2.0.0');
define('BASE_PATH', dirname(dirname(__DIR__)));
define('BACKEND_PATH', dirname(__DIR__));
define('FRONTEND_PATH', BASE_PATH . '/frontend');
define('STORAGE_PATH', BASE_PATH . '/storage');

define('DEFAULT_FOLLOWUP_DAYS', 7);
define('DEADLINE_WARNING_DAYS', 7);
define('DEFAULT_DEADLINE_DAYS', 30);

// Escalation thresholds (deadline-based)
define('ADMIN_ESCALATION_DAYS_AFTER_DEADLINE', 14); // deadline + 14 days → notify admins

define('ITEMS_PER_PAGE', 20);

// Status-to-owner auto-assignment mapping
// Each case status is owned by a specific user
define('STATUS_OWNER_MAP', [
    'collecting'   => 2,  // Miki
    'in_review'    => 1,  // Ella
    'verification' => 1,  // Ella
    'completed'    => 4,  // Jimi
    'closed'       => 4,  // Jimi
]);

date_default_timezone_set('America/New_York');
