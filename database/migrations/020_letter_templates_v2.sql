-- Migration 020: Letter Templates V2
-- Expand template_type ENUM, add claim_number/member_id to health_ledger_items,
-- add template_data JSON to hl_requests and record_requests

-- 1a. Expand letter_templates.template_type ENUM
ALTER TABLE letter_templates
  MODIFY template_type ENUM(
    'medical_records', 'health_ledger', 'bulk_request', 'custom',
    'balance_verification'
  ) NOT NULL DEFAULT 'custom';

-- 1b. Add claim_number and member_id to health_ledger_items
ALTER TABLE health_ledger_items
  ADD COLUMN claim_number VARCHAR(50) NULL AFTER insurance_carrier,
  ADD COLUMN member_id VARCHAR(50) NULL AFTER claim_number;

-- 1c. Add template_data to hl_requests (template_id already exists from migration 007)
ALTER TABLE hl_requests
  ADD COLUMN template_data JSON NULL COMMENT 'Extra fields: settlement_amount, etc.' AFTER template_id;

-- 1d. Add template_data to record_requests (template_id already exists from migration 007)
ALTER TABLE record_requests
  ADD COLUMN template_data JSON NULL COMMENT 'Extra fields for template rendering' AFTER template_id;
