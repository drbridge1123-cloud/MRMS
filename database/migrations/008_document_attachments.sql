-- Migration 008: Document Attachment System
-- Creates tables for case document uploads and request attachments

-- Table: case_documents
-- Stores uploaded documents (HIPAA forms, signed releases, etc.)
CREATE TABLE IF NOT EXISTS case_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL COMMENT 'Optional: provider-specific document',
    document_type ENUM('hipaa_authorization', 'signed_release', 'other') NOT NULL DEFAULT 'other',
    file_name VARCHAR(255) NOT NULL COMMENT 'Generated unique filename',
    original_file_name VARCHAR(255) NOT NULL COMMENT 'User-uploaded filename',
    file_path VARCHAR(500) NOT NULL COMMENT 'Relative path from storage/',
    file_size INT NOT NULL COMMENT 'Size in bytes',
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_case (case_id),
    INDEX idx_provider (case_provider_id),
    INDEX idx_type (document_type),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: request_attachments
-- Tracks which documents were sent with which requests
CREATE TABLE IF NOT EXISTS request_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_request_id INT NOT NULL,
    case_document_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_request_id) REFERENCES record_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (case_document_id) REFERENCES case_documents(id) ON DELETE CASCADE,
    INDEX idx_request (record_request_id),
    INDEX idx_document (case_document_id),
    UNIQUE KEY unique_request_document (record_request_id, case_document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: hl_request_attachments
-- Similar to request_attachments but for health ledger requests
CREATE TABLE IF NOT EXISTS hl_request_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hl_request_id INT NOT NULL,
    case_document_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hl_request_id) REFERENCES hl_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (case_document_id) REFERENCES case_documents(id) ON DELETE CASCADE,
    INDEX idx_hl_request (hl_request_id),
    INDEX idx_document (case_document_id),
    UNIQUE KEY unique_hl_request_document (hl_request_id, case_document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
