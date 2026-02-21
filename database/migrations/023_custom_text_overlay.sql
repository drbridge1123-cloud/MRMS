-- Migration 023: Add custom text overlay fields to case_documents
-- Allows users to overlay free-form text on PDF templates

ALTER TABLE case_documents
  ADD COLUMN use_custom_text_overlay TINYINT(1) NOT NULL DEFAULT 0 AFTER date_font_size,
  ADD COLUMN custom_text_value TEXT NULL AFTER use_custom_text_overlay,
  ADD COLUMN custom_text_x DECIMAL(8,1) NULL AFTER custom_text_value,
  ADD COLUMN custom_text_y DECIMAL(8,1) NULL AFTER custom_text_x,
  ADD COLUMN custom_text_width DECIMAL(8,1) NULL AFTER custom_text_y,
  ADD COLUMN custom_text_height DECIMAL(8,1) NULL AFTER custom_text_width,
  ADD COLUMN custom_text_font_size INT NOT NULL DEFAULT 12 AFTER custom_text_height;
