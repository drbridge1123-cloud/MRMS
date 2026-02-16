-- Provider Name Cleanup Script
-- Fixes spelling errors, unifies chiro→chiropractic, applies proper capitalization
-- Keeps parenthetical content unchanged for manual review

-- 1. Fix common spelling errors
UPDATE providers SET name = REPLACE(name, 'Emegency', 'Emergency') WHERE name LIKE '%Emegency%';
UPDATE providers SET name = REPLACE(name, 'Medicinie', 'Medicine') WHERE name LIKE '%Medicinie%';
UPDATE providers SET name = REPLACE(name, 'Imaing', 'Imaging') WHERE name LIKE '%Imaing%';
UPDATE providers SET name = REPLACE(name, 'Burlinton', 'Burlington') WHERE name LIKE '%Burlinton%';
UPDATE providers SET name = REPLACE(name, 'Psychatry', 'Psychiatry') WHERE name LIKE '%Psychatry%';
UPDATE providers SET name = REPLACE(name, 'Soultion', 'Solution') WHERE name LIKE '%Soultion%';
UPDATE providers SET name = REPLACE(name, 'Acupun.', 'Acupuncture') WHERE name LIKE '%Acupun.%';
UPDATE providers SET name = REPLACE(name, 'Physicans', 'Physicians') WHERE name LIKE '%Physicans%';
UPDATE providers SET name = REPLACE(name, 'Hosptial', 'Hospital') WHERE name LIKE '%Hosptial%';
UPDATE providers SET name = REPLACE(name, 'Hopital', 'Hospital') WHERE name LIKE '%Hopital%';
UPDATE providers SET name = REPLACE(name, 'Clnic', 'Clinic') WHERE name LIKE '%Clnic%';
UPDATE providers SET name = REPLACE(name, 'Centr ', 'Center ') WHERE name LIKE '%Centr %';
UPDATE providers SET name = REPLACE(name, 'Thearpy', 'Therapy') WHERE name LIKE '%Thearpy%';
UPDATE providers SET name = REPLACE(name, 'Physicial', 'Physical') WHERE name LIKE '%Physicial%';

-- 2. Unify all variations of "chiro" to "Chiropractic"
UPDATE providers SET name = REPLACE(name, ' chiro ', ' Chiropractic ') WHERE name LIKE '% chiro %';
UPDATE providers SET name = REPLACE(name, ' Chiro ', ' Chiropractic ') WHERE name LIKE '% Chiro %';
UPDATE providers SET name = REPLACE(name, ' chiro.', ' Chiropractic') WHERE name LIKE '% chiro.%';
UPDATE providers SET name = REPLACE(name, ' Chiro.', ' Chiropractic') WHERE name LIKE '% Chiro.%';

-- 3. Remove leading special characters (-, (, etc)
UPDATE providers SET name = TRIM(LEADING '(' FROM name) WHERE name LIKE '(%';
UPDATE providers SET name = TRIM(LEADING '-' FROM name) WHERE name LIKE '-%';
UPDATE providers SET name = TRIM(LEADING '`' FROM name) WHERE name LIKE '`%';

-- 4. Add suspicious flag column if it doesn't exist
ALTER TABLE providers ADD COLUMN IF NOT EXISTS is_suspicious TINYINT(1) DEFAULT 0;

-- 5. Mark suspicious entries for staff review
UPDATE providers SET is_suspicious = 1 WHERE
    name LIKE '%???%' OR
    name LIKE '%????%' OR
    name = 'Center' OR
    name LIKE 'Center %' OR
    name LIKE '% Center' OR
    LENGTH(name) < 5 OR
    name REGEXP '[^a-zA-Z0-9 &.,()\'/-]' OR
    name LIKE '%  %' OR  -- double spaces
    name LIKE '%..%' OR  -- double periods
    name LIKE '%Emergency Emergency%' OR
    name LIKE '%Hospital Hospital%' OR
    name LIKE '%Clinic Clinic%' OR
    name LIKE '%Center Center%' OR
    name LIKE '%Imaging Imaging%';

-- 6. Clean up double spaces that may have been created
UPDATE providers SET name = REPLACE(name, '  ', ' ') WHERE name LIKE '%  %';
UPDATE providers SET name = REPLACE(name, '  ', ' ') WHERE name LIKE '%  %';
UPDATE providers SET name = REPLACE(name, '  ', ' ') WHERE name LIKE '%  %';

-- 7. Trim leading/trailing whitespace
UPDATE providers SET name = TRIM(name);

-- View results
SELECT
    id,
    name,
    is_suspicious,
    type
FROM providers
WHERE is_suspicious = 1
ORDER BY name;

-- Summary
SELECT
    'Total Providers' as metric,
    COUNT(*) as count
FROM providers
UNION ALL
SELECT
    'Suspicious Entries',
    COUNT(*)
FROM providers
WHERE is_suspicious = 1;
