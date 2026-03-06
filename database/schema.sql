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
    role ENUM('admin','manager','accounting','staff') NOT NULL DEFAULT 'staff',
    permissions TEXT NULL,
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
    status ENUM('collecting','verification','completed','rfd','final_verification','disbursement','accounting','closed') NOT NULL DEFAULT 'collecting',
    treatment_status ENUM('in_treatment','treatment_done','neg','rfd') NULL,
    treatment_end_date DATE NULL,
    settlement_amount DECIMAL(12,2) DEFAULT 0,
    attorney_fee_percent DECIMAL(5,4) DEFAULT 0.3333,
    coverage_3rd_party TINYINT(1) DEFAULT 0,
    coverage_um TINYINT(1) DEFAULT 0,
    coverage_uim TINYINT(1) DEFAULT 0,
    policy_limit TINYINT(1) DEFAULT 0,
    um_uim_limit TINYINT(1) DEFAULT 0,
    pip_subrogation_amount DECIMAL(12,2) DEFAULT 0,
    pip_insurance_company VARCHAR(255) NULL,
    settlement_method VARCHAR(20) NULL,
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
    type ENUM('hospital','er','chiro','imaging','physician','surgery_center','pharmacy','acupuncture','massage','pain_management','pt','other') NOT NULL DEFAULT 'other',
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

-- Insurance Companies (Master DB)
CREATE TABLE IF NOT EXISTS insurance_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('auto','health','workers_comp','liability','um_uim','other') NOT NULL DEFAULT 'auto',
    phone VARCHAR(50) NULL,
    fax VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    address VARCHAR(300) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(2) NULL,
    zip VARCHAR(10) NULL,
    website VARCHAR(300) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_ins_co_name ON insurance_companies(name);
CREATE INDEX idx_ins_co_type ON insurance_companies(type);

-- Adjusters (linked to Insurance Company)
CREATE TABLE IF NOT EXISTS adjusters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insurance_company_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NULL,
    adjuster_type ENUM('pip','um','uim','3rd_party','liability','pd','bi') NULL,
    phone VARCHAR(50) NULL,
    fax VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_adjusters_name ON adjusters(last_name, first_name);
CREATE INDEX idx_adjusters_type ON adjusters(adjuster_type);
CREATE INDEX idx_adjusters_ins_co ON adjusters(insurance_company_id);

-- Case Providers (Case <-> Provider Link)
CREATE TABLE IF NOT EXISTS case_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    provider_id INT NOT NULL,
    treatment_start_date DATE NULL,
    treatment_end_date DATE NULL,
    record_types_needed SET('medical_records','billing','chart','imaging','op_report') NULL,
    overall_status ENUM('treating','not_started','requesting','follow_up','action_needed','received_partial','on_hold','no_records','received_complete','verified') NOT NULL DEFAULT 'treating',
    request_mr TINYINT(1) NOT NULL DEFAULT 0,
    request_bill TINYINT(1) NOT NULL DEFAULT 0,
    request_chart TINYINT(1) NOT NULL DEFAULT 0,
    request_img TINYINT(1) NOT NULL DEFAULT 0,
    request_op TINYINT(1) NOT NULL DEFAULT 0,
    received_date DATE NULL,
    assigned_to INT NULL,
    assignment_status ENUM('pending','accepted','declined') DEFAULT NULL,
    activated_by INT NULL,
    deadline DATE NULL,
    notes TEXT NULL,
    is_on_hold TINYINT(1) NOT NULL DEFAULT 0,
    hold_reason VARCHAR(255) NULL,
    no_records_reason VARCHAR(50) NULL,
    no_records_detail TEXT NULL,
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

-- Internal Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_to_user (to_user_id),
    INDEX idx_messages_from_user (from_user_id),
    INDEX idx_messages_is_read (is_read)
) ENGINE=InnoDB;

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

-- MBDS Reports (Medical Bills Summary)
CREATE TABLE IF NOT EXISTS mbds_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL UNIQUE,
    pip1_name VARCHAR(255) NULL,
    pip2_name VARCHAR(255) NULL,
    health1_name VARCHAR(255) NULL,
    health2_name VARCHAR(255) NULL,
    health3_name VARCHAR(255) NULL,
    has_wage_loss TINYINT(1) DEFAULT 0,
    has_essential_service TINYINT(1) DEFAULT 0,
    has_health_subrogation TINYINT(1) DEFAULT 0,
    has_health_subrogation2 TINYINT(1) DEFAULT 0,
    status ENUM('draft','completed','approved') DEFAULT 'draft',
    completed_by INT NULL,
    completed_at DATETIME NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_mbds_reports_case ON mbds_reports(case_id);
CREATE INDEX idx_mbds_reports_status ON mbds_reports(status);

-- MBDS Lines (Individual line items in MBDS report)
CREATE TABLE IF NOT EXISTS mbds_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    line_type ENUM('provider','bridge_law','wage_loss','essential_service','health_subrogation','health_subrogation2','rx') NOT NULL,
    provider_name VARCHAR(255) NULL,
    case_provider_id INT NULL,
    charges DECIMAL(12,2) DEFAULT 0,
    pip1_amount DECIMAL(12,2) DEFAULT 0,
    pip2_amount DECIMAL(12,2) DEFAULT 0,
    health1_amount DECIMAL(12,2) DEFAULT 0,
    health2_amount DECIMAL(12,2) DEFAULT 0,
    health3_amount DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    office_paid DECIMAL(12,2) DEFAULT 0,
    client_paid DECIMAL(12,2) DEFAULT 0,
    balance DECIMAL(12,2) DEFAULT 0,
    treatment_dates VARCHAR(100) NULL,
    visits VARCHAR(50) NULL,
    note TEXT NULL,
    record_types_needed SET('medical_records','billing','chart','imaging','op_report') NULL,
    ini_status ENUM('pending','complete') NOT NULL DEFAULT 'pending',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES mbds_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_mbds_lines_report ON mbds_lines(report_id);

-- MR Fee Payments (Expense Tracking)
CREATE TABLE IF NOT EXISTS mr_fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL COMMENT 'Links to specific provider (nullable for non-provider costs)',
    expense_category ENUM('mr_cost','litigation','other') NOT NULL DEFAULT 'mr_cost',
    provider_name VARCHAR(200) NULL COMMENT 'Denormalized for display or manual entry',
    description VARCHAR(255) NULL COMMENT 'Record Fee, Police Report, etc.',
    billed_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_type ENUM('check','card','cash','wire','other') NULL,
    check_number VARCHAR(50) NULL,
    payment_date DATE NULL,
    paid_by INT NULL COMMENT 'Staff who made the payment',
    receipt_document_id INT NULL COMMENT 'Links to case_documents for uploaded receipt',
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_mr_fee_payments_case ON mr_fee_payments(case_id);
CREATE INDEX idx_mr_fee_payments_cp ON mr_fee_payments(case_provider_id);
CREATE INDEX idx_mr_fee_payments_paid_by ON mr_fee_payments(paid_by);
CREATE INDEX idx_mr_fee_payments_date ON mr_fee_payments(payment_date);
CREATE INDEX idx_mr_fee_payments_category ON mr_fee_payments(expense_category);

-- Case Negotiations (Coverage-level insurance negotiations)
CREATE TABLE IF NOT EXISTS case_negotiations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    coverage_type ENUM('3rd_party','um','uim','dv') NOT NULL DEFAULT '3rd_party',
    insurance_company VARCHAR(255) NULL,
    round_number INT NOT NULL DEFAULT 1,
    demand_date DATE NULL,
    demand_amount DECIMAL(12,2) DEFAULT 0,
    offer_date DATE NULL,
    offer_amount DECIMAL(12,2) DEFAULT 0,
    party VARCHAR(255) NULL COMMENT 'Adjuster/insurance company name',
    adjuster_phone VARCHAR(50) NULL,
    adjuster_fax VARCHAR(50) NULL,
    adjuster_email VARCHAR(255) NULL,
    claim_number VARCHAR(100) NULL,
    status ENUM('pending','countered','accepted','rejected') DEFAULT 'pending',
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_case_neg_case ON case_negotiations(case_id);
CREATE INDEX idx_case_neg_coverage ON case_negotiations(coverage_type);

-- Provider Negotiations (Provider-level lien negotiations)
CREATE TABLE IF NOT EXISTS provider_negotiations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL,
    mbds_line_id INT NULL COMMENT 'Links to mbds_lines for balance reference',
    provider_name VARCHAR(255) NOT NULL,
    original_balance DECIMAL(12,2) DEFAULT 0,
    requested_reduction DECIMAL(12,2) DEFAULT 0,
    accepted_amount DECIMAL(12,2) DEFAULT 0 COMMENT 'Final agreed amount after reduction',
    reduction_percent DECIMAL(5,2) DEFAULT 0,
    status ENUM('pending','negotiating','accepted','rejected','waived') DEFAULT 'pending',
    contact_name VARCHAR(255) NULL,
    contact_info VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (mbds_line_id) REFERENCES mbds_lines(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_prov_neg_case ON provider_negotiations(case_id);
