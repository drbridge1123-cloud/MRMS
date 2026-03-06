-- Migration 034: Assignment Accept/Decline + Internal Messaging System
-- Adds assignment_status and activated_by to case_providers
-- Creates messages table for internal staff communication

-- 1. Add assignment workflow columns to case_providers
ALTER TABLE case_providers
  ADD COLUMN assignment_status ENUM('pending','accepted','declined') DEFAULT NULL AFTER assigned_to,
  ADD COLUMN activated_by INT NULL AFTER assignment_status;

-- 2. Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_to_user (to_user_id),
    INDEX idx_messages_from_user (from_user_id),
    INDEX idx_messages_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
