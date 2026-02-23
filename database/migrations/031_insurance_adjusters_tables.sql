-- Insurance Companies master table
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

-- Adjusters table (linked to insurance company)
CREATE TABLE IF NOT EXISTS adjusters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insurance_company_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NULL,
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
CREATE INDEX idx_adjusters_ins_co ON adjusters(insurance_company_id);
