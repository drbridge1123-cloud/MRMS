<?php
define('APP_NAME', 'MRMS');
define('APP_VERSION', '1.0.0');
define('BASE_PATH', dirname(dirname(__DIR__)));
define('BACKEND_PATH', dirname(__DIR__));
define('FRONTEND_PATH', BASE_PATH . '/frontend');
define('STORAGE_PATH', BASE_PATH . '/storage');

define('DEFAULT_FOLLOWUP_DAYS', 14);
define('DEADLINE_WARNING_DAYS', 7);
define('DEFAULT_DEADLINE_DAYS', 30);

// Escalation thresholds (days since first request)
define('ESCALATION_ACTION_NEEDED_DAYS', 30);
define('ESCALATION_MANAGER_DAYS', 42);
define('ESCALATION_ADMIN_DAYS', 60);

define('ITEMS_PER_PAGE', 20);

date_default_timezone_set('America/New_York');
