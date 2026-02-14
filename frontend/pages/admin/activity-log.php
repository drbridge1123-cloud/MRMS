<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAdmin();
$pageTitle = 'Activity Log';
$currentPage = 'admin-activity';
ob_start();
?>

<div x-data="activityLogPage()" x-init="loadData()">

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <select x-model="userFilter" @change="loadData(1)"
                class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
            <option value="">All Users</option>
            <template x-for="u in allUsers" :key="u.id">
                <option :value="u.id" x-text="u.full_name"></option>
            </template>
        </select>

        <input type="text" x-model="actionFilter" @input.debounce.300ms="loadData(1)"
               placeholder="Filter by action..."
               class="border border-v2-card-border rounded-lg px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-gold outline-none">

        <select x-model="entityFilter" @change="loadData(1)"
                class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
            <option value="">All Entities</option>
            <option value="user">User</option>
            <option value="case">Case</option>
            <option value="case_provider">Case Provider</option>
            <option value="record_request">Request</option>
            <option value="record_receipt">Receipt</option>
            <option value="note">Note</option>
        </select>

        <input type="date" x-model="dateFrom" @change="loadData(1)"
               class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
        <span class="text-v2-text-light text-sm">to</span>
        <input type="date" x-model="dateTo" @change="loadData(1)"
               class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">

        <button @click="clearFilters()" class="text-sm text-v2-text-light hover:text-v2-text underline">Clear</button>
    </div>

    <!-- Log table -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('created_at')"><div class="flex items-center gap-1">Time <template x-if="sortBy==='created_at'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('user_name')"><div class="flex items-center gap-1">User <template x-if="sortBy==='user_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('action')"><div class="flex items-center gap-1">Action <template x-if="sortBy==='action'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('entity_type')"><div class="flex items-center gap-1">Entity <template x-if="sortBy==='entity_type'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('entity_id')"><div class="flex items-center gap-1">Entity ID <template x-if="sortBy==='entity_id'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="6" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr>
                    </template>
                    <template x-if="!loading && logs.length === 0">
                        <tr><td colspan="6" class="text-center text-v2-text-light py-8">No activity logs found</td></tr>
                    </template>
                    <template x-for="log in logs" :key="log.id">
                        <tr>
                            <td class="text-v2-text-light text-xs whitespace-nowrap" x-text="log.created_at"></td>
                            <td>
                                <span class="font-medium text-sm" x-text="log.user_name || 'System'"></span>
                                <span class="text-xs text-v2-text-light ml-1" x-text="log.username ? '(' + log.username + ')' : ''"></span>
                            </td>
                            <td>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-v2-bg text-v2-text"
                                      x-text="log.action.replace(/_/g, ' ')"></span>
                            </td>
                            <td class="text-sm text-v2-text-mid" x-text="log.entity_type.replace(/_/g, ' ')"></td>
                            <td class="text-sm text-v2-text-light" x-text="log.entity_id || '-'"></td>
                            <td>
                                <template x-if="log.details">
                                    <button @click="log._showDetails = !log._showDetails"
                                            class="text-xs text-gold hover:underline">
                                        <span x-text="log._showDetails ? 'Hide' : 'View'"></span>
                                    </button>
                                </template>
                                <template x-if="!log.details">
                                    <span class="text-xs text-v2-text-light">-</span>
                                </template>
                                <template x-if="log._showDetails && log.details">
                                    <pre class="mt-1 text-xs bg-v2-bg rounded p-2 max-w-xs overflow-x-auto"
                                         x-text="typeof log.details === 'string' ? log.details : JSON.stringify(JSON.parse(log.details), null, 2)"></pre>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <template x-if="pagination && pagination.total_pages > 1">
            <div class="flex items-center justify-between px-6 py-3 border-t border-v2-card-border">
                <div class="text-sm text-v2-text-light">
                    Showing <span x-text="((pagination.page - 1) * pagination.per_page) + 1"></span>-<span x-text="Math.min(pagination.page * pagination.per_page, pagination.total)"></span> of <span x-text="pagination.total"></span>
                </div>
                <div class="flex gap-1">
                    <button @click="loadData(pagination.page - 1)" :disabled="pagination.page <= 1"
                            class="px-3 py-1.5 text-sm border rounded-md disabled:opacity-50">Prev</button>
                    <button @click="loadData(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages"
                            class="px-3 py-1.5 text-sm border rounded-md disabled:opacity-50">Next</button>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function activityLogPage() {
    return {
        logs: [],
        allUsers: [],
        pagination: null,
        loading: true,
        userFilter: '',
        actionFilter: '',
        entityFilter: '',
        dateFrom: '',
        dateTo: '',
        sortBy: '',
        sortDir: 'desc',

        async loadData(page = 1) {
            this.loading = true;
            const params = buildQueryString({
                page,
                per_page: 30,
                user_id: this.userFilter,
                action: this.actionFilter,
                entity_type: this.entityFilter,
                date_from: this.dateFrom,
                date_to: this.dateTo,
                sort_by: this.sortBy,
                sort_dir: this.sortDir
            });
            try {
                const res = await api.get('activity-log' + params);
                this.logs = (res.data || []).map(l => ({ ...l, _showDetails: false }));
                this.pagination = res.pagination || null;
            } catch (e) {}
            this.loading = false;

            if (this.allUsers.length === 0) this.loadUsers();
        },

        sort(column) {
            if (this.sortBy === column) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDir = 'asc';
            }
            this.loadData(1);
        },

        async loadUsers() {
            try {
                const res = await api.get('users?per_page=100');
                this.allUsers = res.data || [];
            } catch (e) {}
        },

        clearFilters() {
            this.userFilter = '';
            this.actionFilter = '';
            this.entityFilter = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.sortBy = '';
            this.sortDir = 'desc';
            this.loadData(1);
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
