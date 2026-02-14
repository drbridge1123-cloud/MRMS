<?php
require_once __DIR__ . '/../backend/helpers/auth.php';
startSecureSession();

if (empty($_SESSION['user_id'])) {
    header('Location: /MRMS/frontend/pages/auth/login.php');
} else {
    header('Location: /MRMS/frontend/pages/dashboard/index.php');
}
exit;
