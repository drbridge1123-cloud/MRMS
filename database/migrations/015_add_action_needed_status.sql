-- Add 'action_needed' to case_providers.overall_status ENUM
-- Triggered after 3 follow-ups with no response; notifies managers
ALTER TABLE case_providers
  MODIFY COLUMN overall_status ENUM('not_started','requesting','follow_up','action_needed','received_partial','on_hold','received_complete','verified')
  DEFAULT 'not_started';
