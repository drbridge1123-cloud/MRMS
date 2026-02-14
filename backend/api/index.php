<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validator.php';
require_once __DIR__ . '/../helpers/date.php';

header('Content-Type: application/json; charset=utf-8');

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/MRMS/backend/api';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace($basePath, '', $path);
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Parse path segments
$segments = $path ? explode('/', $path) : [];
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$action = $segments[2] ?? null;

// Route mapping
switch ($resource) {
    case 'auth':
        $authAction = $id ?? '';
        switch ($authAction) {
            case 'login':
                require __DIR__ . '/auth/login.php';
                break;
            case 'logout':
                require __DIR__ . '/auth/logout.php';
                break;
            case 'me':
                require __DIR__ . '/auth/me.php';
                break;
            default:
                errorResponse('Auth endpoint not found', 404);
        }
        break;

    case 'cases':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/cases/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/cases/get.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/cases/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/cases/update.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/cases/delete.php';
        } else {
            errorResponse('Case endpoint not found', 404);
        }
        break;

    case 'providers':
        if ($method === 'GET' && $id === 'search') {
            require __DIR__ . '/providers/search.php';
        } elseif ($method === 'GET' && !$id) {
            require __DIR__ . '/providers/list.php';
        } elseif ($method === 'GET' && $id && !$action) {
            $_GET['id'] = $id;
            require __DIR__ . '/providers/get.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/providers/create.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/providers/update.php';
        } else {
            errorResponse('Provider endpoint not found', 404);
        }
        break;

    case 'case-providers':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/case-providers/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/case-providers/create.php';
        } elseif ($method === 'PUT' && $id && $action === 'status') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/update-status.php';
        } elseif ($method === 'PUT' && $id && $action === 'assign') {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/assign.php';
        } elseif ($method === 'PUT' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/update-status.php';
        } elseif ($method === 'DELETE' && $id) {
            $_GET['id'] = $id;
            require __DIR__ . '/case-providers/delete.php';
        } else {
            errorResponse('Case-provider endpoint not found', 404);
        }
        break;

    case 'requests':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/requests/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/requests/create.php';
        } elseif ($method === 'POST' && $id === 'followup') {
            require __DIR__ . '/requests/followup.php';
        } else {
            errorResponse('Request endpoint not found', 404);
        }
        break;

    case 'receipts':
        if ($method === 'POST' && !$id) {
            require __DIR__ . '/receipts/create.php';
        } elseif ($method === 'PUT' && $id && $action === 'verify') {
            $_GET['id'] = $id;
            require __DIR__ . '/receipts/verify.php';
        } else {
            errorResponse('Receipt endpoint not found', 404);
        }
        break;

    case 'notifications':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/notifications/list.php';
        } elseif ($method === 'PUT' && $id && $action === 'read') {
            $_GET['id'] = $id;
            require __DIR__ . '/notifications/mark-read.php';
        } elseif ($method === 'PUT' && $id === 'read-all') {
            require __DIR__ . '/notifications/mark-read.php';
        } else {
            errorResponse('Notification endpoint not found', 404);
        }
        break;

    case 'notes':
        if ($method === 'GET' && !$id) {
            require __DIR__ . '/notes/list.php';
        } elseif ($method === 'POST' && !$id) {
            require __DIR__ . '/notes/create.php';
        } else {
            errorResponse('Notes endpoint not found', 404);
        }
        break;

    case 'dashboard':
        $dashAction = $id ?? 'summary';
        switch ($dashAction) {
            case 'summary':
                require __DIR__ . '/dashboard/summary.php';
                break;
            case 'overdue':
                require __DIR__ . '/dashboard/overdue.php';
                break;
            case 'followup-due':
                require __DIR__ . '/dashboard/followup-due.php';
                break;
            default:
                errorResponse('Dashboard endpoint not found', 404);
        }
        break;

    default:
        errorResponse('API endpoint not found', 404);
}
