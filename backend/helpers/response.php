<?php
function jsonResponse($data, $statusCode = 200) {
    // Clean any output buffer content to prevent HTML/PHP errors from corrupting JSON
    if (ob_get_level() > 0) {
        ob_clean();
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function successResponse($data = null, $message = 'Success') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

function errorResponse($message = 'Error', $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'message' => $message,
        'data' => null
    ], $statusCode);
}

function paginatedResponse($data, $total, $page, $perPage, $extra = []) {
    $response = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total' => (int)$total,
            'page' => (int)$page,
            'per_page' => (int)$perPage,
            'total_pages' => ceil($total / $perPage)
        ]
    ];
    if ($extra) {
        $response = array_merge($response, $extra);
    }
    jsonResponse($response);
}
