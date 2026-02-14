<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Cases';
$currentPage = 'cases';
ob_start();
?>

<div x-data="casesListPage()" x-init="loadData()">

    <!-- Top bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="loadData(1)"
                       placeholder="Search cases..."
                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <!-- Status filter -->
            <select x-model="statusFilter" @change="loadData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending_review">Pending Review</option>
                <option value="completed">Completed</option>
                <option value="on_hold">On Hold</option>
            </select>
        </div>

        <!-- Create button -->
        <button @click="showCreateModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Case
        </button>
    </div>

    <!-- Cases table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('case_number')"><div class="flex items-center gap-1">Case # <template x-if="sortBy==='case_number'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('client_name')"><div class="flex items-center gap-1">Client Name <template x-if="sortBy==='client_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('client_dob')"><div class="flex items-center gap-1">DOB <template x-if="sortBy==='client_dob'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('doi')"><div class="flex items-center gap-1">DOI <template x-if="sortBy==='doi'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('attorney_name')"><div class="flex items-center gap-1">Attorney <template x-if="sortBy==='attorney_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('assigned_name')"><div class="flex items-center gap-1">Assigned To <template x-if="sortBy==='assigned_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('status')"><div class="flex items-center gap-1">Status <template x-if="sortBy==='status'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('created_at')"><div class="flex items-center gap-1">Created <template x-if="sortBy==='created_at'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="8" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr>
                    </template>
                    <template x-if="!loading && cases.length === 0">
                        <tr><td colspan="8" class="text-center text-gray-400 py-8">No cases found</td></tr>
                    </template>
                    <template x-for="c in cases" :key="c.id">
                        <tr class="cursor-pointer" @click="window.location.href='/MRMS/frontend/pages/cases/detail.php?id='+c.id">
                            <td class="font-medium text-blue-600" x-text="c.case_number"></td>
                            <td class="font-medium" x-text="c.client_name"></td>
                            <td x-text="formatDate(c.client_dob)"></td>
                            <td x-text="formatDate(c.doi)"></td>
                            <td x-text="c.attorney_name || '-'"></td>
                            <td x-text="c.assigned_name || '-'"></td>
                            <td>
                                <span class="status-badge" :class="'status-' + c.status" x-text="getStatusLabel(c.status)"></span>
                            </td>
                            <td class="text-gray-500" x-text="formatDate(c.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <template x-if="pagination && pagination.total_pages > 1">
            <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100">
                <div class="text-sm text-gray-500">
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

    <!-- Create Case Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showCreateModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold">New Case</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="createCase()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Case Number *</label>
                        <input type="text" x-model="newCase.case_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Name *</label>
                        <input type="text" x-model="newCase.client_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input type="date" x-model="newCase.client_dob"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Injury</label>
                        <input type="date" x-model="newCase.doi"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attorney</label>
                        <input type="text" x-model="newCase.attorney_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select x-model="newCase.assigned_to"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Unassigned</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea x-model="newCase.notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="saving ? 'Creating...' : 'Create Case'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function casesListPage() {
    return {
        cases: [],
        pagination: null,
        loading: true,
        searchQuery: '',
        statusFilter: '',
        sortBy: '',
        sortDir: 'desc',
        showCreateModal: false,
        saving: false,
        users: [],
        newCase: { case_number: '', client_name: '', client_dob: '', doi: '', attorney_name: '', assigned_to: '', notes: '' },

        async loadData(page = 1) {
            this.loading = true;
            const params = buildQueryString({
                page,
                search: this.searchQuery,
                status: this.statusFilter,
                sort_by: this.sortBy,
                sort_dir: this.sortDir
            });
            try {
                const res = await api.get('cases' + params);
                this.cases = res.data || [];
                this.pagination = res.pagination || null;
            } catch (e) {}
            this.loading = false;
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

        async createCase() {
            this.saving = true;
            try {
                const data = { ...this.newCase };
                if (!data.assigned_to) delete data.assigned_to;
                if (!data.client_dob) delete data.client_dob;
                if (!data.doi) delete data.doi;
                const res = await api.post('cases', data);
                showToast('Case created successfully');
                this.showCreateModal = false;
                this.newCase = { case_number: '', client_name: '', client_dob: '', doi: '', attorney_name: '', assigned_to: '', notes: '' };
                this.loadData(1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to create case', 'error');
            }
            this.saving = false;
        },

        async init() {
            // Load users for dropdown
            try {
                // Simple fetch of users - we'll use a basic endpoint
                this.users = [
                    { id: 1, full_name: 'Ella' },
                    { id: 2, full_name: 'Micky' }
                ];
            } catch (e) {}
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
