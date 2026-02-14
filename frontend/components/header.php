<header class="header-bar bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-20">
    <!-- Page title -->
    <div>
        <h1 class="text-xl font-semibold text-gray-800"><?= $pageTitle ?? 'Dashboard' ?></h1>
        <?php if (!empty($pageSubtitle)): ?>
            <p class="text-sm text-gray-500"><?= $pageSubtitle ?></p>
        <?php endif; ?>
    </div>

    <!-- Right side: notifications + user menu -->
    <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <?php include __DIR__ . '/notification-bell.php'; ?>

        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
                <template x-if="$store.auth.user">
                    <span class="font-medium" x-text="$store.auth.user.full_name"></span>
                </template>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-transition
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                <a href="#" @click.prevent="$store.auth.logout()" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>
