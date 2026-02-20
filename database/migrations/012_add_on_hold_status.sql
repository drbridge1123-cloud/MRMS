-- Add 'on_hold' to case_providers overall_status ENUM
ALTER TABLE case_providers
    MODIFY COLUMN overall_status ENUM('not_started','requesting','follow_up','received_partial','on_hold','received_complete','verified') NOT NULL DEFAULT 'not_started';
