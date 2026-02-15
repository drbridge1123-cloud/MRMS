-- Health Ledger Requests table
-- Tracks insurance carrier record requests managed by Ella
CREATE TABLE IF NOT EXISTS health_ledger_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NULL,
    case_number VARCHAR(20) NULL,
    client_name VARCHAR(255) NOT NULL,
    insurance_carrier VARCHAR(255) NOT NULL,
    request_method ENUM('fax','email','portal','phone','mail') NULL,
    request_date DATE NULL,
    sent_date DATE NULL,
    assigned_to INT NULL,
    first_followup DATE NULL,
    second_followup DATE NULL,
    note TEXT NULL,
    status ENUM('pending','sent','follow_up','received','done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hlr_case_id (case_id),
    INDEX idx_hlr_status (status),
    INDEX idx_hlr_assigned_to (assigned_to),
    INDEX idx_hlr_sent_date (sent_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
