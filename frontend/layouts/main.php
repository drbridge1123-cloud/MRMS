<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'MRMS' ?> - Medical Records Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/MRMS/frontend/assets/css/app.css">
    <script src="/MRMS/frontend/assets/js/app.js"></script>
    <script src="/MRMS/frontend/assets/js/utils.js"></script>
</head>
<body class="bg-gray-50 min-h-screen" x-data x-init="$store.auth.init(); $store.notifications.load();">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <!-- Main content wrapper -->
    <div class="main-content" :class="{ 'expanded': $store.sidebar.collapsed }">
        <!-- Top Header -->
        <?php include __DIR__ . '/../components/header.php'; ?>

        <!-- Page Content -->
        <main class="p-6">
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <script src="/MRMS/frontend/assets/js/alpine-stores.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
