-- Add 'accounting' role to users table
ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','accounting','staff') NOT NULL DEFAULT 'staff';
