<?php
// POST /api/mbds/{case_id}/import - Import MBDS from Excel-exported CSV
if ($method !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$userId = requireAuth();

$caseId = (int)($_GET['id'] ?? 0);
if (!$caseId) {
    errorResponse('case_id is required');
}

$case = dbFetchOne("SELECT id, case_number, client_name FROM cases WHERE id = ?", [$caseId]);
if (!$case) {
    errorResponse('Case not found', 404);
}

// Check file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    errorResponse('No file uploaded or upload error');
}

if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    errorResponse('File size exceeds 5MB limit');
}

$content = file_get_contents($_FILES['file']['tmp_name']);
if (!$content || strlen(trim($content)) === 0) {
    errorResponse('File is empty');
}

// Remove BOM, normalize line endings
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
$content = str_replace(["\r\n", "\r"], "\n", $content);

$lines = explode("\n", $content);

// Get case providers for matching
$caseProviders = dbFetchAll(
    "SELECT cp.id, p.name AS provider_name
     FROM case_providers cp
     JOIN providers p ON cp.provider_id = p.id
     WHERE cp.case_id = ?",
    [$caseId]
);

// Parse MBDS CSV format:
// Row 6 (index 5): Header row — Provider, Charges, PIP#1, PIP#2, Health#1, Health#2, Discount, Office Paid, Client Paid, Balance, Treatment Dates, Visits, Note
// Row 7+: Data rows — special types first (BRIDGE LAW, WAGE LOSS, etc.), then Total, then providers

$parsedLines = [];
$specialTypes = [
    'bridge law' => 'bridge_law',
    'wage loss' => 'wage_loss',
    'essential service' => 'essential_service',
    'health subrogation' => 'health_subrogation',
    'health subrogation #2' => 'health_subrogation2',
    'health subrogation2' => 'health_subrogation2',
];

$sortOrder = 10;
$hasWageLoss = false;
$hasEssentialService = false;
$hasHealthSubrogation = false;
$hasHealthSubrogation2 = false;

foreach ($lines as $lineIdx => $line) {
    $cols = str_getcsv($line);

    // Pad columns to avoid index errors
    while (count($cols) < 20) {
        $cols[] = '';
    }

    // Data starts at col 4 (Provider name)
    $providerName = trim($cols[4] ?? '');
    $providerNameLower = strtolower($providerName);

    // Skip empty rows, header rows, name row, title row
    if ($providerName === '' || $providerNameLower === 'provider') continue;
    if (strpos($providerNameLower, 'medical bills') !== false) continue;
    if (strpos($providerNameLower, 'name:') !== false) continue;

    // Skip total row
    if ($providerNameLower === 'total') continue;

    // Parse amounts from the CSV columns
    // Col 4: Provider, Col 5: Charges, Col 6: PIP#1, Col 7: PIP#2, Col 8: Health#1, Col 9: Health#2
    // Col 10: Discount, Col 11: Office Paid, Col 12: Client Paid, Col 13: Balance
    // Col 14: Treatment Dates, Col 15: Visits, Col 16: Note
    $charges = parseAmount(trim($cols[5] ?? ''));
    $pip1 = parseAmount(trim($cols[6] ?? ''));
    $pip2 = parseAmount(trim($cols[7] ?? ''));
    $health1 = parseAmount(trim($cols[8] ?? ''));
    $health2 = parseAmount(trim($cols[9] ?? ''));
    $discount = parseAmount(trim($cols[10] ?? ''));
    $officePaid = parseAmount(trim($cols[11] ?? ''));
    $clientPaid = parseAmount(trim($cols[12] ?? ''));
    $treatmentDates = trim($cols[14] ?? '');
    $visits = trim($cols[15] ?? '');
    $note = trim($cols[16] ?? '');

    // Skip rows with all zeros (empty template rows)
    if ($charges == 0 && $pip1 == 0 && $pip2 == 0 && $health1 == 0 && $health2 == 0
        && $discount == 0 && $officePaid == 0 && $clientPaid == 0
        && $treatmentDates === '' && $note === '') continue;

    // Determine line type
    $lineType = 'provider';
    foreach ($specialTypes as $keyword => $type) {
        if (stripos($providerNameLower, $keyword) !== false) {
            $lineType = $type;
            break;
        }
    }

    // Track special type flags
    if ($lineType === 'wage_loss') $hasWageLoss = true;
    if ($lineType === 'essential_service') $hasEssentialService = true;
    if ($lineType === 'health_subrogation') $hasHealthSubrogation = true;
    if ($lineType === 'health_subrogation2') $hasHealthSubrogation2 = true;

    // Match provider to case_providers
    $cpId = null;
    if ($lineType === 'provider' && $providerName !== '') {
        foreach ($caseProviders as $cp) {
            if (stripos($cp['provider_name'], $providerName) !== false
                || stripos($providerName, $cp['provider_name']) !== false) {
                $cpId = (int)$cp['id'];
                break;
            }
        }
    }

    // Format treatment dates (normalize spacing around dash)
    if ($treatmentDates !== '') {
        $treatmentDates = preg_replace('/\s*[-–]\s*/', '-', $treatmentDates);
    }

    // Calculate balance
    $balance = round($charges - $pip1 - $pip2 - $health1 - $health2 - $discount - $officePaid - $clientPaid, 2);

    $parsedLines[] = [
        'line_type' => $lineType,
        'provider_name' => $providerName,
        'case_provider_id' => $cpId,
        'charges' => $charges,
        'pip1_amount' => $pip1,
        'pip2_amount' => $pip2,
        'health1_amount' => $health1,
        'health2_amount' => $health2,
        'discount' => $discount,
        'office_paid' => $officePaid,
        'client_paid' => $clientPaid,
        'balance' => $balance,
        'treatment_dates' => $treatmentDates ?: null,
        'visits' => $visits ?: null,
        'note' => $note ?: null,
        'sort_order' => $sortOrder,
        'matched_provider' => $cpId ? true : false,
    ];
    $sortOrder += 10;
}

if (empty($parsedLines)) {
    errorResponse('No MBDS entries found in the CSV. Make sure the file matches the expected Excel format.');
}

// Preview mode
if (!empty($_POST['preview'])) {
    $totalCharges = array_sum(array_column($parsedLines, 'charges'));
    $totalPip1 = array_sum(array_column($parsedLines, 'pip1_amount'));
    $totalBalance = array_sum(array_column($parsedLines, 'balance'));

    jsonResponse([
        'success' => true,
        'preview' => $parsedLines,
        'count' => count($parsedLines),
        'total_charges' => round($totalCharges, 2),
        'total_pip1' => round($totalPip1, 2),
        'total_balance' => round($totalBalance, 2),
        'has_wage_loss' => $hasWageLoss,
        'has_essential_service' => $hasEssentialService,
        'has_health_subrogation' => $hasHealthSubrogation,
        'has_health_subrogation2' => $hasHealthSubrogation2,
    ]);
}

// Actual import — get or create MBDS report
$report = dbFetchOne("SELECT * FROM mbds_reports WHERE case_id = ?", [$caseId]);

if (!$report) {
    // Create new report
    $reportId = dbInsert('mbds_reports', [
        'case_id' => $caseId,
        'has_wage_loss' => $hasWageLoss ? 1 : 0,
        'has_essential_service' => $hasEssentialService ? 1 : 0,
        'has_health_subrogation' => $hasHealthSubrogation ? 1 : 0,
        'has_health_subrogation2' => $hasHealthSubrogation2 ? 1 : 0,
    ]);
} else {
    $reportId = (int)$report['id'];

    // Update flags
    $flags = [];
    if ($hasWageLoss) $flags['has_wage_loss'] = 1;
    if ($hasEssentialService) $flags['has_essential_service'] = 1;
    if ($hasHealthSubrogation) $flags['has_health_subrogation'] = 1;
    if ($hasHealthSubrogation2) $flags['has_health_subrogation2'] = 1;
    if (!empty($flags)) {
        dbUpdate('mbds_reports', $flags, 'id = ?', [$reportId]);
    }

    // Delete existing lines to replace with import
    dbDelete('mbds_lines', 'report_id = ?', [$reportId]);
}

// Insert all lines
$imported = 0;
$errors = [];
foreach ($parsedLines as $i => $row) {
    try {
        dbInsert('mbds_lines', [
            'report_id' => $reportId,
            'line_type' => $row['line_type'],
            'provider_name' => $row['provider_name'],
            'case_provider_id' => $row['case_provider_id'],
            'charges' => $row['charges'],
            'pip1_amount' => $row['pip1_amount'],
            'pip2_amount' => $row['pip2_amount'],
            'health1_amount' => $row['health1_amount'],
            'health2_amount' => $row['health2_amount'],
            'discount' => $row['discount'],
            'office_paid' => $row['office_paid'],
            'client_paid' => $row['client_paid'],
            'balance' => $row['balance'],
            'treatment_dates' => $row['treatment_dates'],
            'visits' => $row['visits'],
            'note' => $row['note'],
            'sort_order' => $row['sort_order'],
        ]);
        $imported++;
    } catch (Exception $e) {
        $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
    }
}

logActivity($userId, 'mbds_import', 'mbds_report', $reportId, [
    'case_number' => $case['case_number'],
    'imported' => $imported,
    'total_rows' => count($parsedLines),
]);

jsonResponse([
    'success' => true,
    'imported' => $imported,
    'total_rows' => count($parsedLines),
    'errors' => $errors,
    'message' => "Imported {$imported} MBDS lines",
]);

// --- Helper functions ---

function parseAmount($str) {
    if (!$str || $str === '' || $str === '-') return 0;
    $str = str_replace(['$', ' ', ','], '', $str);
    // Handle parentheses for negative
    if (preg_match('/^\((.+)\)$/', $str, $m)) {
        return -abs((float)$m[1]);
    }
    $val = (float)$str;
    return round($val, 2);
}
