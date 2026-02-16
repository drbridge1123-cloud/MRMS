-- Migration 010: Provider Template Fields
-- Adds fields to case_documents for PDF provider name overlay functionality

ALTER TABLE case_documents
    ADD COLUMN is_provider_template TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this a provider name template document?',
    ADD COLUMN provider_name_x INT NULL COMMENT 'X coordinate for provider name overlay',
    ADD COLUMN provider_name_y INT NULL COMMENT 'Y coordinate for provider name overlay',
    ADD COLUMN provider_name_width INT NULL COMMENT 'Width of overlay area',
    ADD COLUMN provider_name_height INT NULL COMMENT 'Height of overlay area',
    ADD COLUMN provider_name_font_size INT NULL DEFAULT 12 COMMENT 'Font size for overlaid text',
    ADD INDEX idx_template (is_provider_template);
