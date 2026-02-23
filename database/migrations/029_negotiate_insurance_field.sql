-- Add insurance company field to case_negotiations
ALTER TABLE case_negotiations ADD COLUMN insurance_company VARCHAR(255) NULL AFTER coverage_type;
