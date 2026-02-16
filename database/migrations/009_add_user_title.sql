-- Migration: Add title column to users table
-- Date: 2026-02-16

ALTER TABLE users
ADD COLUMN title VARCHAR(100) NULL AFTER full_name;

-- Update existing users with default titles based on role
UPDATE users SET title = 'Administrator' WHERE role = 'admin' AND title IS NULL;
UPDATE users SET title = 'Legal Assistant' WHERE role = 'staff' AND title IS NULL;
UPDATE users SET title = 'Case Manager' WHERE role = 'manager' AND title IS NULL;
