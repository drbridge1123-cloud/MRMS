-- Add separate address fields: city, state, zip
-- Keep existing address field for street address

ALTER TABLE providers
ADD COLUMN city VARCHAR(100) NULL AFTER address,
ADD COLUMN state VARCHAR(2) NULL AFTER city,
ADD COLUMN zip VARCHAR(10) NULL AFTER state;

-- Show updated structure
DESCRIBE providers;
