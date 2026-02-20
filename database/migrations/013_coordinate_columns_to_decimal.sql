-- Migration 013: Change coordinate columns from INT to DECIMAL for precision
-- The visual PDF coordinate picker generates decimal values (e.g., 80.3mm)
-- INT columns truncate these, causing placement inaccuracy

ALTER TABLE case_documents
    MODIFY COLUMN provider_name_x DECIMAL(8,1) NULL,
    MODIFY COLUMN provider_name_y DECIMAL(8,1) NULL,
    MODIFY COLUMN provider_name_width DECIMAL(8,1) NULL,
    MODIFY COLUMN provider_name_height DECIMAL(8,1) NULL,
    MODIFY COLUMN date_x DECIMAL(8,1) NULL,
    MODIFY COLUMN date_y DECIMAL(8,1) NULL,
    MODIFY COLUMN date_width DECIMAL(8,1) NULL,
    MODIFY COLUMN date_height DECIMAL(8,1) NULL;
