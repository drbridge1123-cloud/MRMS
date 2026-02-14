<?php
// SMTP Configuration (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'bridgelawpllc@gmail.com');
define('SMTP_PASSWORD', 'lduq heqy sjff fxir');
define('SMTP_ENCRYPTION', 'tls');               // STARTTLS
define('SMTP_FROM_EMAIL', 'bridgelawpllc@gmail.com');
define('SMTP_FROM_NAME', 'Bridge Law & Associates - Records Department');

// eFax Configuration (Phaxio)
define('FAX_SERVICE', 'phaxio');                // 'phaxio' or 'srfax'
define('FAX_API_KEY', '');                      // Phaxio API key
define('FAX_API_SECRET', '');                   // Phaxio API secret
define('FAX_API_URL', 'https://api.phaxio.com/v2.1/faxes');
define('FAX_CALLER_ID', '');                    // Firm's fax number (E.164 format)
define('FAX_CALLBACK_URL', '');                 // Optional webhook URL for delivery status

// Firm letterhead info
define('FIRM_NAME', 'Bridge Law & Associates');
define('FIRM_ADDRESS', '123 Main Street, Suite 400');
define('FIRM_CITY_STATE_ZIP', 'New York, NY 10001');
define('FIRM_PHONE', '(212) 555-1234');
define('FIRM_FAX', '(212) 555-1235');
define('FIRM_EMAIL', 'records@bridgelawassociates.com');

// Send settings
define('SEND_TIMEOUT', 30);
define('MAX_SEND_ATTEMPTS', 3);
