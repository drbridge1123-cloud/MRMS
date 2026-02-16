<?php
/**
 * File Upload Helper
 *
 * Secure file upload handling with validation, sanitization, and storage management.
 * Supports document attachments for medical records requests.
 */

/**
 * Validate uploaded file
 *
 * @param array $file $_FILES array element
 * @param array $options Validation options
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateUploadedFile($file, $options = []) {
    // Default options
    $maxSize = $options['max_size'] ?? 10485760; // 10MB default
    $allowedMimeTypes = $options['allowed_mime_types'] ?? [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/tiff',
        'image/tif'
    ];
    $allowedExtensions = $options['allowed_extensions'] ?? [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'tif', 'tiff'
    ];

    // Check for upload errors
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'File exceeds maximum size'];
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => false, 'error' => 'No file uploaded'];
        default:
            return ['valid' => false, 'error' => 'File upload failed'];
    }

    // Check file size
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File exceeds maximum size of ' . formatBytes($maxSize)];
    }

    // Check file size is not zero
    if ($file['size'] === 0) {
        return ['valid' => false, 'error' => 'File is empty'];
    }

    // Get file extension
    $pathInfo = pathinfo($file['name']);
    $extension = strtolower($pathInfo['extension'] ?? '');

    // Validate extension
    if (!in_array($extension, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    // Validate MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return ['valid' => false, 'error' => 'Invalid file type detected'];
    }

    // Additional security: Check if file is actually an image for image extensions
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'tif', 'tiff'])) {
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Invalid image file'];
        }
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Sanitize filename to prevent directory traversal and special characters
 *
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitizeFilename($filename) {
    // Get extension
    $pathInfo = pathinfo($filename);
    $extension = strtolower($pathInfo['extension'] ?? '');
    $basename = $pathInfo['filename'] ?? 'file';

    // Remove any path components
    $basename = basename($basename);

    // Remove special characters, keep only alphanumeric, dash, underscore
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);

    // Limit length
    $basename = substr($basename, 0, 100);

    return $basename . '.' . $extension;
}

/**
 * Generate unique filename for storage
 *
 * @param int $caseId Case ID
 * @param string $originalFilename Original filename
 * @return string Generated unique filename
 */
function generateUniqueFilename($caseId, $originalFilename) {
    $pathInfo = pathinfo($originalFilename);
    $extension = strtolower($pathInfo['extension'] ?? 'bin');

    // Format: {case_id}_{timestamp}_{random_hash}.{ext}
    $timestamp = date('YmdHis');
    $hash = substr(md5(uniqid(rand(), true)), 0, 8);

    return "{$caseId}_{$timestamp}_{$hash}.{$extension}";
}

/**
 * Store uploaded file securely
 *
 * @param array $file $_FILES array element
 * @param string $subdir Subdirectory within storage/documents/ (e.g., 'hipaa', 'releases', 'other')
 * @param int $caseId Case ID
 * @return array ['success' => bool, 'file_path' => string, 'file_name' => string, 'error' => string|null]
 */
function storeUploadedFile($file, $subdir, $caseId) {
    // Validate file first
    $validation = validateUploadedFile($file);
    if (!$validation['valid']) {
        return [
            'success' => false,
            'file_path' => null,
            'file_name' => null,
            'error' => $validation['error']
        ];
    }

    // Sanitize subdirectory name to prevent directory traversal
    $subdir = basename($subdir);
    $allowedSubdirs = ['hipaa', 'releases', 'other'];
    if (!in_array($subdir, $allowedSubdirs)) {
        $subdir = 'other';
    }

    // Define storage paths
    $storageBase = __DIR__ . '/../../storage/documents';
    $storageDir = $storageBase . '/' . $subdir;

    // Create directory if it doesn't exist
    if (!is_dir($storageDir)) {
        if (!mkdir($storageDir, 0755, true)) {
            return [
                'success' => false,
                'file_path' => null,
                'file_name' => null,
                'error' => 'Failed to create storage directory'
            ];
        }
    }

    // Generate unique filename
    $uniqueFilename = generateUniqueFilename($caseId, $file['name']);
    $fullPath = $storageDir . '/' . $uniqueFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        return [
            'success' => false,
            'file_path' => null,
            'file_name' => null,
            'error' => 'Failed to move uploaded file'
        ];
    }

    // Set file permissions
    chmod($fullPath, 0644);

    // Return relative path from storage/
    $relativePath = 'documents/' . $subdir . '/' . $uniqueFilename;

    return [
        'success' => true,
        'file_path' => $relativePath,
        'file_name' => $uniqueFilename,
        'error' => null
    ];
}

/**
 * Delete file from storage
 *
 * @param string $filePath Relative path from storage/ directory
 * @return array ['success' => bool, 'error' => string|null]
 */
function deleteStoredFile($filePath) {
    // Security: Verify path is within storage directory
    $storageBase = realpath(__DIR__ . '/../../storage');

    // Normalize path separators for Windows compatibility
    $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
    $fullPath = $storageBase . DIRECTORY_SEPARATOR . $filePath;

    // Check if file exists
    if (!file_exists($fullPath)) {
        // File doesn't exist - consider this a success (already deleted or never existed)
        return ['success' => true, 'error' => null];
    }

    $realPath = realpath($fullPath);

    // Prevent directory traversal
    if ($realPath === false || strpos($realPath, $storageBase) !== 0) {
        return ['success' => false, 'error' => 'Invalid file path'];
    }

    // Delete file
    if (!unlink($realPath)) {
        return ['success' => false, 'error' => 'Failed to delete file'];
    }

    return ['success' => true, 'error' => null];
}

/**
 * Get full file path for download/reading
 *
 * @param string $filePath Relative path from storage/ directory
 * @return string|null Full file path or null if invalid
 */
function getStoredFilePath($filePath) {
    $storageBase = realpath(__DIR__ . '/../../storage');
    $fullPath = $storageBase . '/' . $filePath;
    $realPath = realpath($fullPath);

    // Prevent directory traversal
    if ($realPath === false || strpos($realPath, $storageBase) !== 0) {
        return null;
    }

    // Check if file exists
    if (!file_exists($realPath)) {
        return null;
    }

    return $realPath;
}

/**
 * Format bytes to human-readable size
 *
 * @param int $bytes File size in bytes
 * @param int $precision Decimal precision
 * @return string Formatted size
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Get MIME type from file extension
 *
 * @param string $extension File extension
 * @return string MIME type
 */
function getMimeTypeFromExtension($extension) {
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff'
    ];

    return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
}
