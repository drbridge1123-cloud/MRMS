-- Migration 019: Add Health #3 insurance column and Health Subrogation #2
-- Adds health3_name to mbds_reports, health3_amount to mbds_lines,
-- and has_health_subrogation2 toggle to mbds_reports

ALTER TABLE mbds_reports ADD COLUMN health3_name VARCHAR(255) NULL AFTER health2_name;
ALTER TABLE mbds_reports ADD COLUMN has_health_subrogation2 TINYINT(1) DEFAULT 0 AFTER has_health_subrogation;

ALTER TABLE mbds_lines ADD COLUMN health3_amount DECIMAL(12,2) DEFAULT 0 AFTER health2_amount;

-- Add health_subrogation2 to line_type ENUM
ALTER TABLE mbds_lines MODIFY COLUMN line_type ENUM('provider','bridge_law','wage_loss','essential_service','health_subrogation','health_subrogation2','rx') NOT NULL;
