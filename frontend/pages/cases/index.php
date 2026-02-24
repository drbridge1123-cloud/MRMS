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
                       placeholder="Search cases..." autocomplete="off"
                       class="w-[32rem] pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold focus:border-gold outline-none">
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
                                        class="icon-btn icon-btn-danger icon-btn-sm" title="Delete case">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
    <style>
    .ncm { width: 540px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; }
    .ncm-header { background: #0F1B2D; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .ncm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; }
    .ncm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
    .ncm-close:hover { color: rgba(255,255,255,.75); }
    .ncm-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
    .ncm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
    .ncm-req { color: var(--gold, #C9A84C); }
    .ncm-input, .ncm-select {
        width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
        padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
    }
    .ncm-input:focus, .ncm-select:focus { border-color: var(--gold, #C9A84C); background: #fff; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
    .ncm-input::placeholder { color: #c5c5c5; }
    .ncm-input.ncm-mono { font-family: 'IBM Plex Mono', monospace; font-weight: 600; }
    .ncm-input.ncm-date { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; }
    .ncm-select { appearance: none; cursor: pointer; padding-right: 30px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
    .ncm-textarea { width: 100%; background: #fafafa; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit; resize: vertical; min-height: 70px; line-height: 1.5; }
    .ncm-textarea:focus { border-color: var(--gold, #C9A84C); background: #fff; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
    .ncm-textarea::placeholder { color: #c5c5c5; }
    .ncm-section { display: flex; align-items: center; gap: 10px; margin: 0; }
    .ncm-section::before, .ncm-section::after { content: ''; flex: 1; height: 1px; background: var(--border, #d0cdc5); }
    .ncm-section span { font-size: 9px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .1em; white-space: nowrap; }
    .ncm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: flex-end; gap: 10px; }
    .ncm-btn-cancel { background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px; padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s; }
    .ncm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
    .ncm-btn-submit { background: var(--gold, #C9A84C); color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 8px rgba(201,168,76,.35); display: flex; align-items: center; gap: 6px; transition: all .15s; }
    .ncm-btn-submit:hover { filter: brightness(1.05); box-shadow: 0 4px 12px rgba(201,168,76,.45); }
    .ncm-btn-submit:disabled { opacity: .55; cursor: not-allowed; }
    </style>
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="showCreateModal && (showCreateModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showCreateModal = false"></div>
        <form @submit.prevent="createCase()" class="ncm relative z-10" @click.stop>
            <div class="ncm-header">
                <h3>New Case</h3>
                <button type="button" class="ncm-close" @click="showCreateModal = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="ncm-body">
                <div class="ncm-section"><span>Case Info</span></div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ncm-label">Case Number <span class="ncm-req">*</span></label>
                        <input type="text" x-model="newCase.case_number" required class="ncm-input ncm-mono">
                    </div>
                    <div style="flex:1;">
                        <label class="ncm-label">Client Name <span class="ncm-req">*</span></label>
                        <input type="text" x-model="newCase.client_name" required class="ncm-input">
                    </div>
                </div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ncm-label">Date of Birth <span class="ncm-req">*</span></label>
                        <input type="date" x-model="newCase.client_dob" required class="ncm-input ncm-date">
                    </div>
                    <div style="flex:1;">
                        <label class="ncm-label">Date of Injury <span class="ncm-req">*</span></label>
                        <input type="date" x-model="newCase.doi" required class="ncm-input ncm-date">
                    </div>
                </div>
                <div class="ncm-section"><span>Assignment</span></div>
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="ncm-label">Attorney</label>
                        <input type="text" x-model="newCase.attorney_name" class="ncm-input" placeholder="Attorney name...">
                    </div>
                    <div style="flex:1;">
                        <label class="ncm-label">Assigned To <span class="ncm-req">*</span></label>
                        <select x-model="newCase.assigned_to" required class="ncm-select">
                            <option value="">Select...</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.full_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="ncm-section"><span>Notes</span></div>
                <div>
                    <textarea x-model="newCase.notes" class="ncm-textarea" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="ncm-footer">
                <button type="button" @click="showCreateModal = false" class="ncm-btn-cancel">Cancel</button>
                <button type="submit" :disabled="saving" class="ncm-btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="saving ? 'Creating...' : 'Create Case'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
