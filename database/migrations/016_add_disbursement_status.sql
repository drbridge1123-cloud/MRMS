-- Add disbursement stage between final_verification and accounting
ALTER TABLE cases MODIFY COLUMN status
  ENUM('collecting','verification','completed','rfd','final_verification','disbursement','accounting','closed')
  NOT NULL DEFAULT 'collecting';
