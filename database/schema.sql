-- MRMS Database Schema
-- Medical Records Management System
-- Phase 1 - MVP

CREATE DATABASE IF NOT EXISTS mrms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mrms_db;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Cases
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(50) NOT NULL UNIQUE,
    client_name VARCHAR(100) NOT NULL,
    client_dob DATE NULL,
    doi DATE NULL,
    assigned_to INT NULL,
    status ENUM('collecting','in_review','verification','completed','closed') NOT NULL DEFAULT 'collecting',
    treatment_status ENUM('in_treatment','treatment_done','neg','rfd') NULL,
    treatment_end_date DATE NULL,
    attorney_name VARCHAR(100) NULL,
    ini_completed TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_treatment_status ON cases(treatment_status);
CREATE INDEX idx_cases_assigned ON cases(assigned_to);

-- Providers (Master DB)
CREATE TABLE IF NOT EXISTS providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM('hospital','er','chiro','imaging','physician','surgery_center','pharmacy','other') NOT NULL DEFAULT 'other',
    address VARCHAR(300) NULL,
    phone VARCHAR(20) NULL,
    fax VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    portal_url VARCHAR(300) NULL,
    preferred_method ENUM('email','fax','portal','phone','mail','chartswap','online') NOT NULL DEFAULT 'fax',
    uses_third_party TINYINT(1) NOT NULL DEFAULT 0,
    third_party_name VARCHAR(200) NULL,
    third_party_contact VARCHAR(200) NULL,
    avg_response_days INT NULL,
    difficulty_level ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_providers_name ON providers(name);
CREATE INDEX idx_providers_type ON providers(type);

-- Provider Contacts (Per Department)
CREATE TABLE IF NOT EXISTS provider_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    department VARCHAR(100) NULL,
    contact_type ENUM('email','fax','portal','phone') NOT NULL,
    contact_value VARCHAR(200) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    verified_at DATE NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_provider_contacts_provider ON provider_contacts(provider_id);

-- Case Providers (Case <-> Provider Link)
CREATE TABLE IF NOT EXISTS case_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    provider_id INT NOT NULL,
    treatment_start_date DATE NULL,
    treatment_end_date DATE NULL,
    record_types_needed SET('medical_records','billing','chart','imaging','op_report') NULL,
    overall_status ENUM('not_started','requesting','follow_up','received_partial','received_complete','verified') NOT NULL DEFAULT 'not_started',
    assigned_to INT NULL,
    deadline DATE NULL,
    notes TEXT NULL,
    is_on_hold TINYINT(1) NOT NULL DEFAULT 0,
    hold_reason VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_case_providers_case ON case_providers(case_id);
CREATE INDEX idx_case_providers_provider ON case_providers(provider_id);
CREATE INDEX idx_case_providers_status ON case_providers(overall_status);
CREATE INDEX idx_case_providers_hold ON case_providers(is_on_hold);

-- Record Requests (Request History)
CREATE TABLE IF NOT EXISTS record_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_provider_id INT NOT NULL,
    request_date DATE NOT NULL,
    request_method ENUM('email','fax','portal','phone','mail','chartswap','online') NOT NULL,
    request_type ENUM('initial','follow_up','re_request','rfd') NOT NULL DEFAULT 'initial',
    sent_to VARCHAR(200) NULL,
    authorization_sent TINYINT(1) NOT NULL DEFAULT 0,
    requested_by INT NULL,
    notes TEXT NULL,
    send_status ENUM('draft','sending','sent','failed') NOT NULL DEFAULT 'draft',
    sent_at DATETIME NULL,
    send_error TEXT NULL,
    send_attempts INT NOT NULL DEFAULT 0,
    letter_html LONGTEXT NULL,
    next_followup_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_record_requests_cp ON record_requests(case_provider_id);
CREATE INDEX idx_record_requests_followup ON record_requests(next_followup_date);
CREATE INDEX idx_record_requests_send_status ON record_requests(send_status);

-- Record Receipts
CREATE TABLE IF NOT EXISTS record_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_provider_id INT NOT NULL,
    received_date DATE NOT NULL,
    received_method ENUM('email','fax','portal','mail','in_person') NOT NULL,
    has_medical_records TINYINT(1) NOT NULL DEFAULT 0,
    has_billing TINYINT(1) NOT NULL DEFAULT 0,
    has_chart TINYINT(1) NOT NULL DEFAULT 0,
    has_imaging TINYINT(1) NOT NULL DEFAULT 0,
    has_op_report TINYINT(1) NOT NULL DEFAULT 0,
    is_complete TINYINT(1) NOT NULL DEFAULT 0,
    incomplete_reason TEXT NULL,
    file_location VARCHAR(500) NULL,
    received_by INT NULL,
    verified_by INT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_record_receipts_cp ON record_receipts(case_provider_id);

-- Case Notes
CREATE TABLE IF NOT EXISTS case_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL,
    user_id INT NOT NULL,
    note_type ENUM('general','follow_up','issue','handoff') NOT NULL DEFAULT 'general',
    contact_method ENUM('phone','fax','email','portal','mail','in_person','other') NULL,
    contact_date DATETIME NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_case_notes_case ON case_notes(case_id);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_provider_id INT NULL,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(500) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    due_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_unread ON notifications(user_id, is_read);

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    details JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_activity_log_entity ON activity_log(entity_type, entity_id);
CREATE INDEX idx_activity_log_user ON activity_log(user_id);

-- Send Log (email/fax audit trail)
CREATE TABLE IF NOT EXISTS send_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_request_id INT NOT NULL,
    send_method ENUM('email','fax') NOT NULL,
    recipient VARCHAR(200) NOT NULL,
    status ENUM('success','failed') NOT NULL,
    external_id VARCHAR(200) NULL COMMENT 'Phaxio fax ID or SMTP message ID',
    error_message TEXT NULL,
    sent_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_request_id) REFERENCES record_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_send_log_request ON send_log(record_request_id);

-- Deadline Changes (audit trail)
CREATE TABLE IF NOT EXISTS deadline_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_provider_id INT NOT NULL,
    old_deadline DATE NOT NULL,
    new_deadline DATE NOT NULL,
    reason VARCHAR(500) NOT NULL,
    changed_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_deadline_changes_cp ON deadline_changes(case_provider_id);
