<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'MRMS' ?> - Medical Records Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/MRMS/frontend/assets/css/app.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <?= $content ?? '' ?>
    <script src="/MRMS/frontend/assets/js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
