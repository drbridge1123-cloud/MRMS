<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Cases';
$currentPage = 'cases';
$pageScripts = ['/MRMS/frontend/assets/js/pages/cases.js'];
ob_start();
?>

<div x-data="casesListPage()">

    <!-- Summary Cards -->
    <div class="grid grid-cols-7 gap-2 mb-3">
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 flex items-center justify-between cursor-pointer card-hover"
             :class="statusFilter === 'collecting,verification,completed,rfd,final_verification,accounting' ? 'ring-2 ring-gold' : ''"
             @click="statusFilter = statusFilter === 'collecting,verification,completed,rfd,final_verification,accounting' ? '' : 'collecting,verification,completed,rfd,final_verification,accounting'; loadData(1)">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Active</p>
                <p class="text-lg font-bold text-v2-text" x-text="summary.active ?? '-'"></p>
            </div>
            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
             :class="statusFilter === 'collecting' ? 'ring-2 ring-blue-400' : ''"
             @click="statusFilter = statusFilter === 'collecting' ? '' : 'collecting'; loadData(1)">
            <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Collection</p>
            <p class="text-lg font-bold text-blue-600" x-text="summary.collecting ?? '-'"></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
             :class="statusFilter === 'verification' ? 'ring-2 ring-orange-400' : ''"
             @click="statusFilter = statusFilter === 'verification' ? '' : 'verification'; loadData(1)">
            <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Verification</p>
            <p class="text-lg font-bold text-orange-600" x-text="summary.verification ?? '-'"></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
             :class="statusFilter === 'completed,rfd' ? 'ring-2 ring-purple-400' : ''"
             @click="statusFilter = statusFilter === 'completed,rfd' ? '' : 'completed,rfd'; loadData(1)">
            <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Attorney</p>
            <p class="text-lg font-bold text-purple-600" x-text="summary.attorney ?? '-'"></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
             :class="statusFilter === 'final_verification,accounting' ? 'ring-2 ring-amber-400' : ''"
             @click="statusFilter = statusFilter === 'final_verification,accounting' ? '' : 'final_verification,accounting'; loadData(1)">
            <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Closing</p>
            <p class="text-lg font-bold text-amber-600" x-text="summary.closing ?? '-'"></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 flex items-center justify-between cursor-pointer card-hover"
             :class="statusFilter === 'closed' ? 'ring-2 ring-gray-400' : ''"
             @click="statusFilter = statusFilter === 'closed' ? '' : 'closed'; loadData(1)">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Closed</p>
                <p class="text-lg font-bold text-gray-500" x-text="summary.closed ?? '-'"></p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 flex items-center justify-between">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Overdue</p>
                <p class="text-lg font-bold text-red-600" x-text="summary.overdue_providers ?? '-'"></p>
            </div>
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
    </div>

    <!-- Top bar -->
    <div class="flex items-center justify-between gap-3 mb-3">
        <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                       placeholder="Search cases..."
                       class="w-64 pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold focus:border-gold outline-none">
            </div>

            <!-- Staff filter -->
            <select x-model="assignedFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Staff</option>
                <template x-for="u in staffList" :key="u.id">
                    <option :value="u.id" x-text="u.full_name"></option>
                </template>
            </select>
        </div>

        <!-- Create button -->
        <button @click="showCreateModal = true"
                class="bg-gold text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gold-hover transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Case
        </button>
    </div>

    <!-- Cases table -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border"
         x-init="initScrollContainer($el)">
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
                        <th>Progress</th>
                        <th>Issues</th>
                        <th class="cursor-pointer select-none" @click="sort('created_at')"><div class="flex items-center gap-1">Created <template x-if="sortBy==='created_at'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th x-show="$store.auth.isAdmin" class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="11" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="11" class="text-center text-v2-text-light py-8">No cases found</td></tr>
                    </template>
                    <template x-for="c in items" :key="c.id">
                        <tr class="cursor-pointer" :class="{ 'row-dimmed': $store.auth.isStaff && !c.assigned_name }" @click="window.location.href='/MRMS/frontend/pages/cases/detail.php?id='+c.id">
                            <td class="font-medium text-gold" x-text="c.case_number"></td>
                            <td class="font-medium" x-text="c.client_name"></td>
                            <td x-text="formatDate(c.client_dob)"></td>
                            <td x-text="formatDate(c.doi)"></td>
                            <td x-text="c.attorney_name || '-'"></td>
                            <td x-text="c.assigned_name || '-'"></td>
                            <td>
                                <span class="status-badge" :class="'status-' + c.status" x-text="getStatusLabel(c.status)"></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-v2-card-border rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" :style="'width:' + (c.provider_total > 0 ? Math.round(c.provider_done/c.provider_total*100) : 0) + '%'"></div>
                                    </div>
                                    <span class="text-xs text-v2-text-light" x-text="c.provider_done + '/' + c.provider_total"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <template x-if="c.provider_overdue > 0">
                                        <span class="text-xs text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full font-semibold" x-text="c.provider_overdue"></span>
                                    </template>
                                    <template x-if="c.provider_followup > 0">
                                        <span class="text-xs text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full font-semibold" x-text="c.provider_followup"></span>
                                    </template>
                                    <template x-if="c.provider_overdue == 0 && c.provider_followup == 0">
                                        <span class="text-emerald-500 text-xs">&#10003;</span>
                                    </template>
                                </div>
                            </td>
                            <td class="text-v2-text-light" x-text="formatDate(c.created_at)"></td>
                            <td x-show="$store.auth.isAdmin" class="px-2 py-3 text-center" @click.stop>
                                <button @click="deleteCase(c.id, c.case_number, c.client_name)"
                                        class="text-v2-text-light hover:text-red-600 transition-colors" title="Delete case">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

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

    <!-- Create Case Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showCreateModal = false"></div>
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <form @submit.prevent="createCase()">
                <div class="modal-v2-header">
                    <h3 class="modal-v2-title">New Case</h3>
                    <button type="button" class="modal-v2-close" @click="showCreateModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body space-y-4">
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Case Number <span class="text-red-500">*</span></label>
                            <input type="text" x-model="newCase.case_number" required
                                   class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Client Name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="newCase.client_name" required
                                   class="form-v2-input">
                        </div>
                    </div>
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" x-model="newCase.client_dob" required
                                   class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Date of Injury <span class="text-red-500">*</span></label>
                            <input type="date" x-model="newCase.doi" required
                                   class="form-v2-input">
                        </div>
                    </div>
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Attorney</label>
                            <input type="text" x-model="newCase.attorney_name"
                                   class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Assigned To <span class="text-red-500">*</span></label>
                            <select x-model="newCase.assigned_to" required
                                    class="form-v2-select">
                                <option value="">Select...</option>
                                <template x-for="u in users" :key="u.id">
                                    <option :value="u.id" x-text="u.full_name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-v2-label">Notes</label>
                        <textarea x-model="newCase.notes" rows="2"
                                  class="form-v2-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showCreateModal = false"
                            class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="btn-v2-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="saving ? 'Creating...' : 'Create Case'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
