-- Health Ledger v2: Parent/child structure with send capability
-- Parent: one row per case+carrier combination
CREATE TABLE IF NOT EXISTS health_ledger_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NULL,
    case_number VARCHAR(20) NULL,
    client_name VARCHAR(255) NOT NULL,
    insurance_carrier VARCHAR(255) NOT NULL,
    carrier_contact_email VARCHAR(255) NULL,
    carrier_contact_fax VARCHAR(50) NULL,
    overall_status ENUM('not_started','requesting','follow_up','received','done') DEFAULT 'not_started',
    assigned_to INT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hli_case_id (case_id),
    INDEX idx_hli_status (overall_status),
    INDEX idx_hli_assigned_to (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Child: each request/follow-up attempt (name reused after dropping old table)
CREATE TABLE IF NOT EXISTS hl_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    request_type ENUM('initial','follow_up','re_request') DEFAULT 'initial',
    request_date DATE NOT NULL,
    request_method ENUM('fax','email','portal','phone','mail') NOT NULL,
    sent_to VARCHAR(255) NULL,
    send_status ENUM('draft','sending','sent','failed') DEFAULT 'draft',
    sent_at DATETIME NULL,
    send_error TEXT NULL,
    send_attempts INT DEFAULT 0,
    letter_html LONGTEXT NULL,
    next_followup_date DATE NULL,
    notes TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES health_ledger_items(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hlr_item_id (item_id),
    INDEX idx_hlr_send_status (send_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
