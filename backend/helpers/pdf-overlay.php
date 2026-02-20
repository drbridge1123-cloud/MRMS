<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

/**
 * Generate a new PDF by overlaying provider name and optionally date on a template PDF
 *
 * @param string $templatePath Full path to the template PDF file
 * @param string $providerName Provider name to overlay
 * @param int $x X coordinate for text overlay
 * @param int $y Y coordinate for text overlay
 * @param int $width Width of the overlay area (for white rectangle)
 * @param int $height Height of the overlay area (for white rectangle)
 * @param int $fontSize Font size for the text
 * @param array $dateOverlay Optional date overlay config: ['x', 'y', 'width', 'height', 'fontSize']
 * @return string Generated PDF content as binary string
 */
function generateProviderPDF($templatePath, $providerName, $x, $y, $width, $height, $fontSize = 12, $dateOverlay = null) {
    try {
        // Debug: log coordinates being used
        error_log("[PDF Overlay Debug] Provider: x=$x, y=$y, w=$width, h=$height, fontSize=$fontSize");
        if ($dateOverlay) {
            error_log("[PDF Overlay Debug] Date: x={$dateOverlay['x']}, y={$dateOverlay['y']}, w={$dateOverlay['width']}, h={$dateOverlay['height']}, fontSize={$dateOverlay['fontSize']}");
        }

        // Create new PDF instance
        $pdf = new Fpdi();

        // Get the page count of the source PDF
        $pageCount = $pdf->setSourceFile($templatePath);

        // Process each page
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import the page
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // Add a page with the same orientation and size
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            if ($pageNo === 1) {
                error_log("[PDF Overlay Debug] FPDI page size: w={$size['width']}mm, h={$size['height']}mm, orientation=$orientation");
            }
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);

            // Use the imported page as template
            $pdf->useTemplate($templateId);

            // Disable auto page break to prevent text near bottom from creating new pages
            $pdf->SetAutoPageBreak(false);

            // Only overlay on first page (where provider name and date typically are)
            if ($pageNo === 1) {
                // Draw white rectangle to cover existing provider name text
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Rect($x, $y, $width, $height, 'F');

                // Set font for provider name
                $pdf->SetFont('Arial', '', $fontSize);
                $pdf->SetTextColor(0, 0, 0);

                // Position and write the provider name
                // Line height should be proportional to font size
                $lineHeight = $fontSize * 0.6; // Increased for better spacing and prevent cutoff
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($width, $lineHeight, $providerName, 0, 'L');

                // Add date overlay if configured
                if ($dateOverlay && isset($dateOverlay['x'], $dateOverlay['y'], $dateOverlay['width'], $dateOverlay['height'])) {
                    // Draw white rectangle for date
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->Rect($dateOverlay['x'], $dateOverlay['y'], $dateOverlay['width'], $dateOverlay['height'], 'F');

                    // Set font for date
                    $dateFontSize = $dateOverlay['fontSize'] ?? 12;
                    $pdf->SetFont('Arial', '', $dateFontSize);
                    $pdf->SetTextColor(0, 0, 0);

                    // Format today's date (MM/DD/YYYY format for US)
                    $currentDate = date('m/d/Y');

                    // Position and write the date (use Cell since date is always single-line)
                    $pdf->SetXY($dateOverlay['x'], $dateOverlay['y']);
                    $pdf->Cell($dateOverlay['width'], $dateOverlay['height'], $currentDate, 0, 0, 'L');
                }
            }
        }

        // Return the PDF as string
        return $pdf->Output('S');

    } catch (Exception $e) {
        error_log("PDF Overlay Error: " . $e->getMessage());
        throw new Exception("Failed to generate PDF: " . $e->getMessage());
    }
}

/**
 * Save generated PDF to a file
 *
 * @param string $pdfContent Binary PDF content
 * @param string $outputPath Full path where to save the file
 * @return bool Success status
 */
function savePDFToFile($pdfContent, $outputPath) {
    try {
        // Ensure directory exists
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Write the file
        $result = file_put_contents($outputPath, $pdfContent);

        if ($result === false) {
            throw new Exception("Failed to write PDF file");
        }

        return true;

    } catch (Exception $e) {
        error_log("PDF Save Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate provider-specific PDF from template document
 * This is the main function that combines template lookup and PDF generation
 *
 * @param int $documentId The case_documents.id of the template
 * @param string $providerName Provider name to insert
 * @param string $outputDir Directory where to save the generated PDF
 * @return array ['success' => bool, 'file_path' => string, 'error' => string]
 */
function generateProviderDocument($documentId, $providerName, $outputDir) {
    require_once __DIR__ . '/db.php';

    try {
        // Get document details
        $doc = dbFetchOne(
            "SELECT * FROM case_documents WHERE id = ? AND is_provider_template = 1",
            [$documentId]
        );

        if (!$doc) {
            return ['success' => false, 'error' => 'Template document not found'];
        }

        // Validate template has coordinates set
        if (!$doc['provider_name_x'] || !$doc['provider_name_y'] ||
            !$doc['provider_name_width'] || !$doc['provider_name_height']) {
            return ['success' => false, 'error' => 'Template coordinates not configured'];
        }

        // Build full path to template file
        $templatePath = __DIR__ . '/../../storage/' . $doc['file_path'];

        if (!file_exists($templatePath)) {
            return ['success' => false, 'error' => 'Template file not found'];
        }

        // Prepare date overlay if configured
        $dateOverlay = null;
        if ($doc['use_date_overlay'] && $doc['date_x'] && $doc['date_y'] &&
            $doc['date_width'] && $doc['date_height']) {
            $dateOverlay = [
                'x' => (float)$doc['date_x'],
                'y' => (float)$doc['date_y'],
                'width' => (float)$doc['date_width'],
                'height' => (float)$doc['date_height'],
                'fontSize' => (int)$doc['date_font_size'] ?: 12
            ];
        }

        // Generate the PDF
        $pdfContent = generateProviderPDF(
            $templatePath,
            $providerName,
            (float)$doc['provider_name_x'],
            (float)$doc['provider_name_y'],
            (float)$doc['provider_name_width'],
            (float)$doc['provider_name_height'],
            (int)$doc['provider_name_font_size'] ?: 12,
            $dateOverlay
        );

        // Generate output filename
        $timestamp = date('YmdHis');
        $safeProviderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $providerName);
        $outputFilename = $doc['case_id'] . '_' . $safeProviderName . '_' . $timestamp . '.pdf';
        $outputPath = $outputDir . '/' . $outputFilename;

        // Save the file
        if (!savePDFToFile($pdfContent, $outputPath)) {
            return ['success' => false, 'error' => 'Failed to save generated PDF'];
        }

        return [
            'success' => true,
            'file_path' => $outputPath,
            'filename' => $outputFilename
        ];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
