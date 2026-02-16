-- Fix is_suspicious flags to only show truly problematic entries

-- 1. Reset all suspicious flags
UPDATE providers SET is_suspicious = 0;

-- 2. Mark only entries with ??? question marks
UPDATE providers SET is_suspicious = 1
WHERE name LIKE '%???%' OR name LIKE '%????%';

-- 3. Mark very short names (less than 4 characters) or single abbreviations
UPDATE providers SET is_suspicious = 1
WHERE
    LENGTH(TRIM(name)) < 4 OR
    (LENGTH(TRIM(name)) < 6 AND name NOT LIKE '% %'); -- Single word and very short

-- 4. Mark names that are just "Center" or similar standalone words
UPDATE providers SET is_suspicious = 1
WHERE
    name = 'Center' OR
    name = 'Clinic' OR
    name = 'Hospital' OR
    name = 'Imaging';

-- View results
SELECT
    id,
    name,
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
