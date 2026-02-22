-- Migration: New 7-stage case workflow
-- Remove: in_review
-- Add: rfd, final_verification, accounting

-- 1) Convert existing in_review cases to verification
UPDATE cases SET status = 'verification' WHERE status = 'in_review';

-- 2) Modify ENUM to new workflow stages
ALTER TABLE cases MODIFY COLUMN status
  ENUM('collecting','verification','completed','rfd','final_verification','accounting','closed')
  NOT NULL DEFAULT 'collecting';
