<?php
/**
 * Import provider_contacts.csv into the provider_contacts table.
 * Run from browser: http://localhost/MRMS/database/import_provider_contacts.php
 */

require_once __DIR__ . '/../backend/config/database.php';

$pdo = getDBConnection();

$csvFile = __DIR__ . '/../provider_contacts.csv';
if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile");
}

// Load all providers by name for matching
$providers = $pdo->query("SELECT id, name FROM providers")->fetchAll(PDO::FETCH_ASSOC);
$providerMap = [];
foreach ($providers as $p) {
    $providerMap[mb_strtolower(trim($p['name']))] = $p['id'];
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle); // skip header row

$inserted = 0;
$skipped = 0;
$notFound = [];

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 5) continue;

    $providerName = trim($row[0]);
    $department = trim($row[1]);
    $contactType = trim($row[2]);
    $contactValue = trim($row[3]);
    $isPrimary = (int)trim($row[4]);

    // Validate contact_type
    if (!in_array($contactType, ['email', 'fax', 'portal', 'phone'])) {
        echo "SKIP: Invalid contact type '$contactType' for '$providerName'\n<br>";
        $skipped++;
        continue;
    }

    // Find provider by name (case-insensitive)
    $key = mb_strtolower($providerName);
    $providerId = $providerMap[$key] ?? null;

    if (!$providerId) {
        if (!in_array($providerName, $notFound)) {
            $notFound[] = $providerName;
        }
        $skipped++;
        continue;
    }

    // Check for duplicate
    $existing = $pdo->prepare(
        "SELECT id FROM provider_contacts WHERE provider_id = ? AND contact_type = ? AND contact_value = ? AND department = ?"
    );
    $existing->execute([$providerId, $contactType, $contactValue, $department]);
    if ($existing->fetch()) {
        $skipped++;
        continue;
    }

    // Insert
    $stmt = $pdo->prepare(
        "INSERT INTO provider_contacts (provider_id, department, contact_type, contact_value, is_primary) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$providerId, $department, $contactType, $contactValue, $isPrimary]);
    $inserted++;
}

fclose($handle);

echo "<h2>Import Complete</h2>";
echo "<p><strong>Inserted:</strong> $inserted contacts</p>";
echo "<p><strong>Skipped:</strong> $skipped (duplicates or unmatched)</p>";

if (!empty($notFound)) {
    echo "<h3>Providers not found in DB (need to create first):</h3><ul>";
    foreach ($notFound as $name) {
        echo "<li>" . htmlspecialchars($name) . "</li>";
    }
    echo "</ul>";
}
