-- Migration 017: MR Fee Payment System
-- Tracks payments for medical record fees, litigation costs, and other expenses

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
    FOREIGN KEY (receipt_document_id) REFERENCES case_documents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_mr_fee_payments_case ON mr_fee_payments(case_id);
CREATE INDEX idx_mr_fee_payments_cp ON mr_fee_payments(case_provider_id);
CREATE INDEX idx_mr_fee_payments_paid_by ON mr_fee_payments(paid_by);
CREATE INDEX idx_mr_fee_payments_date ON mr_fee_payments(payment_date);
CREATE INDEX idx_mr_fee_payments_category ON mr_fee_payments(expense_category);
