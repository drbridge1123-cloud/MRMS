-- Add department field to record_requests for provider contact department tracking
ALTER TABLE record_requests ADD COLUMN department VARCHAR(100) NULL AFTER sent_to;
