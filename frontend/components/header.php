<header class="header-bar bg-white border-b border-v2-card-border px-6 py-3 flex items-center justify-between sticky top-0 z-20">
    <!-- Left: Badge + Page title -->
    <div class="flex items-center gap-4">
        <span class="hidden sm:inline-flex items-center px-3 py-1 bg-navy text-gold text-xs font-bold tracking-wider rounded">BRIDGE LAW & ASSOCIATES</span>
        <div>
            <h1 class="v2-page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            <?php if (!empty($pageSubtitle)): ?>
                <p class="text-sm text-v2-text-light"><?= $pageSubtitle ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right side: notifications -->
    <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <?php include __DIR__ . '/notification-bell.php'; ?>
    </div>
</header>
