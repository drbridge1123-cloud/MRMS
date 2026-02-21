-- Migration 018: Bank Statement Reconciliation
-- Stores imported bank statement lines and tracks reconciliation with mr_fee_payments

CREATE TABLE IF NOT EXISTS bank_statement_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id VARCHAR(36) NOT NULL COMMENT 'Groups entries from same CSV import',
    transaction_date DATE NOT NULL,
    description VARCHAR(500) NULL,
    amount DECIMAL(12,2) NOT NULL,
    check_number VARCHAR(50) NULL,
    reference_number VARCHAR(100) NULL,
    bank_category VARCHAR(100) NULL COMMENT 'Category from bank statement',
    reconciliation_status ENUM('unmatched','matched','ignored') NOT NULL DEFAULT 'unmatched',
    matched_payment_id INT NULL COMMENT 'Links to mr_fee_payments when matched',
    matched_by INT NULL,
    matched_at DATETIME NULL,
    notes TEXT NULL,
    imported_by INT NOT NULL,
    imported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matched_payment_id) REFERENCES mr_fee_payments(id) ON DELETE SET NULL,
    FOREIGN KEY (matched_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (imported_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_bank_entries_batch ON bank_statement_entries(batch_id);
CREATE INDEX idx_bank_entries_date ON bank_statement_entries(transaction_date);
CREATE INDEX idx_bank_entries_check ON bank_statement_entries(check_number);
CREATE INDEX idx_bank_entries_status ON bank_statement_entries(reconciliation_status);
CREATE INDEX idx_bank_entries_matched ON bank_statement_entries(matched_payment_id);
