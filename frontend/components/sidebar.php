<aside class="sidebar fixed left-0 top-0 bg-navy text-white z-30 flex flex-col"
       :class="{ 'collapsed': $store.sidebar.collapsed }">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-navy-border">
        <div class="w-8 h-8 bg-gold rounded-lg flex items-center justify-center font-bold text-sm text-navy flex-shrink-0">
            MR
        </div>
        <span class="sidebar-text font-semibold text-lg">MRMS</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-1 px-3">
            <li>
                <a href="/MRMS/frontend/pages/dashboard/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'dashboard' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/MRMS/frontend/pages/cases/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'cases' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-text">Cases</span>
                </a>
            </li>
            <li>
                <a href="/MRMS/frontend/pages/providers/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'providers' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="sidebar-text">Providers</span>
                </a>
            </li>
            <li>
                <a href="/MRMS/frontend/pages/mr-tracker/index.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'tracker' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="sidebar-text">Tracker</span>
                </a>
            </li>
            <li>
                <a href="/MRMS/frontend/pages/admin/templates.php"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'admin-templates' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-text">Templates</span>
                </a>
            </li>
        </ul>

        <!-- Finance Section -->
        <template x-if="$store.auth.hasPermission('expense_report') || $store.auth.hasPermission('reconciliation')">
            <div>
                <div class="border-t border-navy-border my-4 mx-3"></div>
                <div class="px-5 mb-2">
                    <span class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-wider">Finance</span>
                </div>
                <ul class="space-y-1 px-3">
                    <template x-if="$store.auth.hasPermission('expense_report')">
                        <li>
                            <a href="/MRMS/frontend/pages/reports/index.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'reports' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span class="sidebar-text">Expense Report</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('reconciliation')">
                        <li>
                            <a href="/MRMS/frontend/pages/admin/bank-reconciliation.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'admin-reconciliation' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="sidebar-text">Reconciliation</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <!-- Settings Section -->
        <template x-if="$store.auth.hasPermission('users') || $store.auth.hasPermission('activity_log') || $store.auth.hasPermission('data_management')">
            <div>
                <div class="border-t border-navy-border my-4 mx-3"></div>
                <div class="px-5 mb-2">
                    <span class="sidebar-text text-xs font-semibold text-slate-500 uppercase tracking-wider">Settings</span>
                </div>
                <ul class="space-y-1 px-3">
                    <template x-if="$store.auth.hasPermission('users')">
                        <li>
                            <a href="/MRMS/frontend/pages/admin/users.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'admin-users' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="sidebar-text">Users</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('activity_log')">
                        <li>
                            <a href="/MRMS/frontend/pages/admin/activity-log.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'admin-activity' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="sidebar-text">Activity Log</span>
                            </a>
                        </li>
                    </template>
                    <template x-if="$store.auth.hasPermission('data_management')">
                        <li>
                            <a href="/MRMS/frontend/pages/admin/data-management.php"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-navy-light transition-colors <?= ($currentPage ?? '') === 'admin-data' ? 'bg-navy-light text-gold' : 'text-slate-300' ?>">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                                <span class="sidebar-text">Data Management</span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </template>
    </nav>

    <!-- User info at bottom -->
    <div class="border-t border-navy-border px-4 py-3" x-data x-show="!$store.auth.loading">
        <template x-if="$store.auth.user">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gold flex items-center justify-center text-sm font-semibold text-navy flex-shrink-0"
                     x-text="$store.auth.user?.full_name?.charAt(0) || 'U'"></div>
                <div class="sidebar-text flex-1 min-w-0">
                    <div class="text-sm font-medium truncate" x-text="$store.auth.user?.full_name || 'User'"></div>
                    <div class="text-xs text-slate-400 capitalize" x-text="$store.auth.user?.role || 'user'"></div>
                </div>
                <button @click="$store.auth.logout()" title="Logout"
                        class="sidebar-text p-1.5 text-slate-400 hover:text-red-400 hover:bg-navy-light rounded transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Collapse toggle -->
    <button @click="$store.sidebar.toggle()"
            class="absolute -right-3 top-20 w-6 h-6 bg-navy-light rounded-full flex items-center justify-center text-slate-300 hover:bg-navy-border shadow-md">
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': $store.sidebar.collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
</aside>
