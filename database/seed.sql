USE mrms_db;

-- Default users (password: 'password')
INSERT INTO users (username, password_hash, full_name, role) VALUES
('ella', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ella', 'admin'),
('micky', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Micky', 'staff');

-- Sample providers
INSERT INTO providers (name, type, phone, fax, email, preferred_method, difficulty_level) VALUES
('Holy Cross Hospital', 'hospital', '(301) 754-7000', '(301) 754-7001', 'medicalrecords@holycross.com', 'fax', 'medium'),
('Washington Adventist Hospital', 'hospital', '(301) 891-7600', '(301) 891-7601', 'records@adventisthealthcare.com', 'fax', 'hard'),
('Concentra Urgent Care', 'physician', '(301) 555-0100', '(301) 555-0101', 'records@concentra.com', 'email', 'easy'),
('Advanced Radiology', 'imaging', '(301) 555-0200', '(301) 555-0201', 'records@advancedrad.com', 'portal', 'easy'),
('Capital Spine & Pain', 'chiro', '(301) 555-0300', '(301) 555-0301', 'office@capitalspine.com', 'fax', 'medium');

-- Sample provider contacts
INSERT INTO provider_contacts (provider_id, department, contact_type, contact_value, is_primary) VALUES
(1, 'Medical Records', 'fax', '(301) 754-7001', 1),
(1, 'Medical Records', 'email', 'medicalrecords@holycross.com', 0),
(1, 'Billing', 'fax', '(301) 754-7002', 1),
(2, 'Medical Records', 'fax', '(301) 891-7601', 1),
(2, 'Medical Records', 'phone', '(301) 891-7600', 0),
(3, 'Medical Records', 'email', 'records@concentra.com', 1),
(4, 'Medical Records', 'portal', 'https://portal.advancedrad.com', 1),
(5, 'Medical Records', 'fax', '(301) 555-0301', 1);

-- Sample cases
INSERT INTO cases (case_number, client_name, client_dob, doi, assigned_to, status, attorney_name, ini_completed) VALUES
('2024-001', 'John Smith', '1985-03-15', '2024-01-10', 2, 'active', 'Mr. Kim', 1),
('2024-002', 'Maria Garcia', '1990-07-22', '2024-02-05', 2, 'active', 'Mr. Park', 1),
('2024-003', 'David Johnson', '1978-11-30', '2024-01-25', 1, 'active', 'Mr. Kim', 1);

-- Sample case providers
INSERT INTO case_providers (case_id, provider_id, treatment_start_date, treatment_end_date, record_types_needed, overall_status, assigned_to, deadline) VALUES
(1, 1, '2024-01-10', '2024-01-12', 'medical_records,billing', 'requesting', 2, '2024-03-10'),
(1, 3, '2024-01-15', '2024-03-15', 'medical_records,billing,chart', 'follow_up', 2, '2024-03-15'),
(1, 5, '2024-02-01', '2024-04-01', 'medical_records,billing', 'not_started', 2, '2024-04-01'),
(2, 2, '2024-02-05', '2024-02-07', 'medical_records,billing,imaging', 'requesting', 2, '2024-04-05'),
(2, 4, '2024-02-10', NULL, 'imaging', 'received_complete', 1, '2024-04-10'),
(3, 1, '2024-01-25', '2024-01-27', 'medical_records,billing', 'verified', 1, '2024-03-25');

-- Sample record requests
INSERT INTO record_requests (case_provider_id, request_date, request_method, request_type, sent_to, authorization_sent, requested_by, next_followup_date) VALUES
(1, '2024-02-10', 'fax', 'initial', '(301) 754-7001', 1, 2, '2024-02-24'),
(2, '2024-02-01', 'email', 'initial', 'records@concentra.com', 1, 2, '2024-02-15'),
(2, '2024-02-15', 'email', 'follow_up', 'records@concentra.com', 0, 2, '2024-03-01'),
(4, '2024-02-20', 'fax', 'initial', '(301) 891-7601', 1, 2, '2024-03-06');

-- Sample record receipts
INSERT INTO record_receipts (case_provider_id, received_date, received_method, has_medical_records, has_billing, has_chart, has_imaging, has_op_report, is_complete, file_location, received_by) VALUES
(5, '2024-03-01', 'portal', 0, 0, 0, 1, 0, 1, '\\\\sharepoint\\cases\\2024-002\\Advanced Radiology', 2),
(6, '2024-02-20', 'fax', 1, 1, 0, 0, 0, 1, '\\\\sharepoint\\cases\\2024-003\\Holy Cross Hospital', 1);
