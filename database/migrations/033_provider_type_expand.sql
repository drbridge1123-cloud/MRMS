-- Add missing provider types to ENUM
ALTER TABLE providers
    MODIFY COLUMN type ENUM(
        'hospital','er','chiro','imaging','physician',
        'surgery_center','pharmacy','acupuncture','massage',
        'pain_management','pt','other'
    ) NOT NULL DEFAULT 'other';
