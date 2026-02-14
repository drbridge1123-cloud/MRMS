-- Migration 002: Email & eFax Send Capabilities
-- Add send tracking columns to record_requests, create send_log table

ALTER TABLE record_requests
    ADD COLUMN send_status ENUM('draft','sending','sent','failed') NOT NULL DEFAULT 'draft' AFTER notes,
    ADD COLUMN sent_at DATETIME NULL AFTER send_status,
    ADD COLUMN send_error TEXT NULL AFTER sent_at,
    ADD COLUMN send_attempts INT NOT NULL DEFAULT 0 AFTER send_error,
    ADD COLUMN letter_html LONGTEXT NULL AFTER send_attempts;

CREATE INDEX idx_record_requests_send_status ON record_requests(send_status);

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
