-- Migration 027: Add Negotiate + Disbursement tables
-- Coverage-level negotiations and provider-level lien negotiations
-- Settlement settings on cases table

-- Case Negotiations (Coverage-level insurance negotiations)
CREATE TABLE IF NOT EXISTS case_negotiations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    coverage_type ENUM('3rd_party','um','uim','dv') NOT NULL DEFAULT '3rd_party',
    round_number INT NOT NULL DEFAULT 1,
    demand_date DATE NULL,
    demand_amount DECIMAL(12,2) DEFAULT 0,
    offer_date DATE NULL,
    offer_amount DECIMAL(12,2) DEFAULT 0,
    party VARCHAR(255) NULL COMMENT 'Adjuster/insurance company name',
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

-- Settlement columns on cases table
ALTER TABLE cases ADD COLUMN settlement_amount DECIMAL(12,2) DEFAULT 0 AFTER treatment_end_date;
ALTER TABLE cases ADD COLUMN attorney_fee_percent DECIMAL(5,4) DEFAULT 0.3333 AFTER settlement_amount;
ALTER TABLE cases ADD COLUMN coverage_3rd_party TINYINT(1) DEFAULT 0 AFTER attorney_fee_percent;
ALTER TABLE cases ADD COLUMN coverage_um TINYINT(1) DEFAULT 0 AFTER coverage_3rd_party;
ALTER TABLE cases ADD COLUMN coverage_uim TINYINT(1) DEFAULT 0 AFTER coverage_um;
ALTER TABLE cases ADD COLUMN policy_limit TINYINT(1) DEFAULT 0 AFTER coverage_uim;
ALTER TABLE cases ADD COLUMN um_uim_limit TINYINT(1) DEFAULT 0 AFTER policy_limit;
ALTER TABLE cases ADD COLUMN pip_subrogation_amount DECIMAL(12,2) DEFAULT 0 AFTER um_uim_limit;
ALTER TABLE cases ADD COLUMN pip_insurance_company VARCHAR(255) NULL AFTER pip_subrogation_amount;
ALTER TABLE cases ADD COLUMN settlement_method VARCHAR(20) NULL AFTER pip_insurance_company;
