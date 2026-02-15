-- Migration 004: Treatment tracking, hold support, and ChartSwap method
-- Date: 2026-02-15
-- Description: Add treatment_status and treatment_end_date to cases,
--              hold tracking to case_providers, and 'chartswap'/'online'
--              to request/provider method enums.

USE mrms_db;

-- 1. Cases: treatment status tracking
ALTER TABLE cases
    ADD COLUMN treatment_status ENUM('in_treatment','treatment_done','neg','rfd') NULL AFTER status,
    ADD COLUMN treatment_end_date DATE NULL AFTER treatment_status;

CREATE INDEX idx_cases_treatment_status ON cases(treatment_status);

-- 2. Case Providers: hold tracking
ALTER TABLE case_providers
    ADD COLUMN is_on_hold TINYINT(1) NOT NULL DEFAULT 0 AFTER notes,
    ADD COLUMN hold_reason VARCHAR(255) NULL AFTER is_on_hold;

CREATE INDEX idx_case_providers_hold ON case_providers(is_on_hold);

-- 3. Record Requests: add chartswap and online to request_method
ALTER TABLE record_requests
    MODIFY COLUMN request_method ENUM('email','fax','portal','phone','mail','chartswap','online') NOT NULL;

-- 4. Providers: add chartswap and online to preferred_method
ALTER TABLE providers
    MODIFY COLUMN preferred_method ENUM('email','fax','portal','phone','mail','chartswap','online') NOT NULL DEFAULT 'fax';
