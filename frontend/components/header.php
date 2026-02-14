<header class="header-bar bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-20">
    <!-- Page title -->
    <div>
        <h1 class="text-xl font-semibold text-gray-800"><?= $pageTitle ?? 'Dashboard' ?></h1>
        <?php if (!empty($pageSubtitle)): ?>
            <p class="text-sm text-gray-500"><?= $pageSubtitle ?></p>
        <?php endif; ?>
    </div>

    <!-- Right side: notifications -->
    <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <?php include __DIR__ . '/notification-bell.php'; ?>
    </div>
</header>
