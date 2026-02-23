-- Add adjuster fax field to case_negotiations
ALTER TABLE case_negotiations ADD COLUMN adjuster_fax VARCHAR(50) NULL AFTER adjuster_phone;
