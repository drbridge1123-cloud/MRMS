<?php
/**
 * CSV Helper Functions for Import/Export
 */

/**
 * Output CSV file for download
 * @param string $filename Download filename
 * @param array $headers Column headers
 * @param array $rows Array of associative arrays
 */
function outputCSV($filename, $headers, $rows) {
    // Clear any previous output
    if (ob_get_level()) ob_end_clean();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");

    // Write headers
    fputcsv($output, $headers);

    // Write data rows
    foreach ($rows as $row) {
        $line = [];
        foreach ($headers as $header) {
            $key = str_replace(' ', '_', strtolower($header));
            $line[] = $row[$key] ?? '';
        }
        fputcsv($output, $line);
    }

    fclose($output);
    exit;
}

/**
 * Parse uploaded CSV file
 * @param string $fileKey $_FILES key name
 * @return array ['headers' => [...], 'rows' => [...]]
 */
function parseCSV($fileKey = 'file') {
    // Check file exists
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        ];
        $code = $_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE;
        errorResponse($errorMessages[$code] ?? 'File upload error');
    }

    // Check file size (5MB max)
    if ($_FILES[$fileKey]['size'] > 5 * 1024 * 1024) {
        errorResponse('File size exceeds 5MB limit');
    }

    // Check extension
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        errorResponse('Only CSV files are accepted');
    }

    // Read file content
    $content = file_get_contents($_FILES[$fileKey]['tmp_name']);
    if ($content === false || strlen(trim($content)) === 0) {
        errorResponse('File is empty or unreadable');
    }

    // Remove BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    // Normalize line endings
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    // Parse CSV
    $lines = explode("\n", trim($content));
    if (count($lines) < 2) {
        errorResponse('CSV file must contain a header row and at least one data row');
    }

    // Parse header
    $headers = str_getcsv(array_shift($lines));
    $headers = array_map(function($h) {
        return strtolower(trim(str_replace(' ', '_', $h)));
    }, $headers);

    // Parse data rows
    $rows = [];
    foreach ($lines as $i => $line) {
        if (trim($line) === '') continue;
        $values = str_getcsv($line);

        $row = [];
        foreach ($headers as $j => $header) {
            $row[$header] = isset($values[$j]) ? trim($values[$j]) : '';
        }
        $rows[] = $row;
    }

    if (empty($rows)) {
        errorResponse('No data rows found in CSV file');
    }

    return ['headers' => $headers, 'rows' => $rows];
}
