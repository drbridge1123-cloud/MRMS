-- Migrate existing addresses to separate fields
-- Expected format: "Street Address, City, State ZIP"

-- Update addresses that follow the pattern: "..., City, ST ZIP"
UPDATE providers
SET
    city = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(address, ',', -2), ',', 1)),
    state = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(address, ' ', -2), ' ', 1)),
    zip = TRIM(SUBSTRING_INDEX(address, ' ', -1)),
    address = TRIM(SUBSTRING_INDEX(address, ',', 1))
WHERE address LIKE '%,%'
  AND address REGEXP '.*, [A-Za-z ]+, [A-Z]{2} [0-9]{5}';

-- Show results
SELECT id, name, address, city, state, zip
FROM providers
WHERE city IS NOT NULL OR state IS NOT NULL OR zip IS NOT NULL
LIMIT 20;

-- Count migrated
SELECT
    'Total with address' as metric,
    COUNT(*) as count
FROM providers
WHERE address IS NOT NULL
UNION ALL
SELECT
    'Migrated to separate fields',
    COUNT(*)
FROM providers
WHERE city IS NOT NULL OR state IS NOT NULL OR zip IS NOT NULL;
