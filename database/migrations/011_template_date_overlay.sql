-- Add date overlay fields to case_documents table
-- This allows templates to automatically insert the current date when generating PDFs

ALTER TABLE case_documents
    ADD COLUMN use_date_overlay TINYINT(1) NOT NULL DEFAULT 0 AFTER provider_name_font_size,
    ADD COLUMN date_x INT NULL AFTER use_date_overlay,
    ADD COLUMN date_y INT NULL AFTER date_x,
    ADD COLUMN date_width INT NULL AFTER date_y,
    ADD COLUMN date_height INT NULL AFTER date_width,
    ADD COLUMN date_font_size INT NULL DEFAULT 12 AFTER date_height;
