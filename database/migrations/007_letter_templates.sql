-- Migration 007: Letter Template Management System
-- Creates tables for storing and versioning letter templates

-- Table: letter_templates
-- Stores letter templates with placeholder support
CREATE TABLE IF NOT EXISTS letter_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    template_type ENUM('medical_records', 'health_ledger', 'bulk_request', 'custom') NOT NULL DEFAULT 'custom',
    subject_template VARCHAR(255) NULL COMMENT 'Subject line with placeholders',
    body_template LONGTEXT NOT NULL COMMENT 'HTML template with {{placeholders}}',
    is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if this is the default template for its type',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 if template is active, 0 if soft-deleted',
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (template_type),
    INDEX idx_default (is_default, template_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: letter_template_versions
-- Tracks version history for template changes (audit trail)
CREATE TABLE IF NOT EXISTS letter_template_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    version_number INT NOT NULL,
    body_template LONGTEXT NOT NULL,
    subject_template VARCHAR(255) NULL,
    changed_by INT NULL,
    change_notes TEXT NULL COMMENT 'Notes about what changed in this version',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_template (template_id),
    INDEX idx_version (template_id, version_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alter record_requests table to support template selection
ALTER TABLE record_requests
    ADD COLUMN template_id INT NULL COMMENT 'Template used for this request' AFTER case_provider_id,
    ADD FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE SET NULL;

-- Alter hl_requests table to support template selection
ALTER TABLE hl_requests
    ADD COLUMN template_id INT NULL COMMENT 'Template used for this request' AFTER item_id,
    ADD FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE SET NULL;
