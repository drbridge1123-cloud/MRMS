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
    'collecting'          => 2,  // Miki
    'verification'        => 1,  // Ella
    'completed'           => 4,  // Jimi
    'rfd'                 => 4,  // Jimi
    'final_verification'  => 1,  // Ella
    'disbursement'        => 4,  // Jimi
    'accounting'          => 6,  // Chloe
    'closed'              => 3,  // Daniel
]);

// Card last-4-digits → cardholder name mapping (for bank statement imports)
define('CARD_OWNER_MAP', [
    '9027' => 'Sunny',
    '8433' => 'Soyong',
    '2443' => 'Jimi',
    '2518' => 'Karl',
    '3052' => 'Miki',
    '3060' => 'Ella',
    '3128' => 'Dave',
    '2984' => 'Chloe',
]);

date_default_timezone_set('America/New_York');
