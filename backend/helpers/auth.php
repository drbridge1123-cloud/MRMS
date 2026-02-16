<?php
require_once __DIR__ . '/../config/auth.php';

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session cookie parameters for HTTPS/ngrok
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/MRMS',
            'domain' => '',
            'secure' => true,  // HTTPS only
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_name(SESSION_NAME);
        session_start();
    }
}

function requireAuth() {
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        // Check if this is an API request
        if (isApiRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }
        header('Location: /MRMS/frontend/pages/auth/login.php');
        exit;
    }
    return $_SESSION['user_id'];
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }
        header('Location: /MRMS/frontend/pages/dashboard/index.php');
        exit;
    }
}

function requireAdminOrManager() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'manager') {
        if (isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Admin or Manager access required']);
            exit;
        }
        header('Location: /MRMS/frontend/pages/dashboard/index.php');
        exit;
    }
}

function getCurrentUser() {
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['user_role']
    ];
}

function isApiRequest() {
    return strpos($_SERVER['REQUEST_URI'], '/api/') !== false ||
           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

function generateCSRFToken() {
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    startSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
