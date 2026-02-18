<?php
function getInput() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return array_merge($_GET, $_POST);
}

function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $errors[] = "{$field} is required";
        }
    }
    return $errors;
}

function sanitizeString($value) {
    return trim($value);
}

function validateDate($date) {
    if (empty($date)) return true;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function validateEnum($value, $allowed) {
    return in_array($value, $allowed, true);
}

function getPaginationParams() {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int)($_GET['per_page'] ?? ITEMS_PER_PAGE)));
    $offset = ($page - 1) * $perPage;
    return [$page, $perPage, $offset];
}

function sanitizeLetterHtml($html) {
    // Remove script tags and their content
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
    // Remove event handler attributes (onclick, onload, onerror, etc.)
    $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
    $html = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $html);
    // Remove javascript: protocol URLs
    $html = preg_replace('/javascript\s*:/i', 'blocked:', $html);
    // Remove dangerous elements
    $html = preg_replace('/<(object|embed|applet|form|iframe)[^>]*>.*?<\/\1>/is', '', $html);
    $html = preg_replace('/<(object|embed|applet|form|iframe)[^>]*\/?>/i', '', $html);
    // Enforce size limit (100KB)
    if (strlen($html) > 102400) {
        $html = substr($html, 0, 102400);
    }
    return $html;
}
