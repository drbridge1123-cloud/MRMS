-- Add adjuster contact fields and claim number to case_negotiations
ALTER TABLE case_negotiations ADD COLUMN adjuster_phone VARCHAR(50) NULL AFTER party;
ALTER TABLE case_negotiations ADD COLUMN adjuster_email VARCHAR(255) NULL AFTER adjuster_phone;
ALTER TABLE case_negotiations ADD COLUMN claim_number VARCHAR(100) NULL AFTER adjuster_email;
