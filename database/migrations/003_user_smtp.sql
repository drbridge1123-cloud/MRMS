-- Per-user SMTP credentials for individual email sending
ALTER TABLE users ADD COLUMN smtp_email VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN smtp_app_password VARCHAR(255) NULL AFTER smtp_email;
