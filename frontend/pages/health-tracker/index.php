<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Health Tracker';
$currentPage = 'health-tracker';
$pageScripts = ['/MRMS/frontend/assets/js/pages/health-tracker.js'];
ob_start();
?>

<div x-data="healthLedgerPage()" x-init="init()">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-v2-text">Health Tracker</h1>
            <p class="text-sm text-v2-text-light mt-1">Insurance carrier record requests tracking</p>
        </div>
        <div class="flex gap-2">
            <button @click="showImportModal = true"
                    class="px-4 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg text-v2-text-mid">
                Import CSV
            </button>
            <button @click="openAddModal()"
                    class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90">
                + Add Item
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div @click="toggleStatusFilter('')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 cursor-pointer card-hover"
             :class="statusFilter === '' ? 'ring-2 ring-gold' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Total</p>
            <p class="text-2xl font-bold text-v2-text mt-1" x-text="summary.total ?? '-'"></p>
        </div>
        <div @click="toggleStatusFilter('not_started')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 cursor-pointer card-hover"
             :class="statusFilter === 'not_started' ? 'ring-2 ring-gray-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Not Started</p>
            <p class="text-2xl font-bold text-gray-500 mt-1" x-text="summary.not_started ?? '-'"></p>
        </div>
        <div @click="toggleStatusFilter('requesting')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 cursor-pointer card-hover"
             :class="statusFilter === 'requesting' ? 'ring-2 ring-blue-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Requesting</p>
            <p class="text-2xl font-bold text-blue-600 mt-1" x-text="summary.requesting ?? '-'"></p>
        </div>
        <div @click="toggleStatusFilter('follow_up')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 cursor-pointer card-hover"
             :class="statusFilter === 'follow_up' ? 'ring-2 ring-amber-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Follow Up</p>
            <p class="text-2xl font-bold text-amber-600 mt-1" x-text="summary.follow_up ?? '-'"></p>
        </div>
        <div @click="toggleStatusFilter('received')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 cursor-pointer card-hover"
             :class="statusFilter === 'received' ? 'ring-2 ring-green-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Received</p>
            <p class="text-2xl font-bold text-green-600 mt-1" x-text="summary.received ?? '-'"></p>
        </div>
        <div @click="toggleStatusFilter('done')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 cursor-pointer card-hover"
             :class="statusFilter === 'done' ? 'ring-2 ring-emerald-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Done</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1" x-text="summary.done ?? '-'"></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                       placeholder="Search client, case #, or carrier..."
                       class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>
            <select x-model="statusFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Statuses</option>
                <option value="not_started">Not Started</option>
                <option value="requesting">Requesting</option>
                <option value="follow_up">Follow Up</option>
                <option value="received">Received</option>
                <option value="done">Done</option>
            </select>
            <!-- No escalation tiers for health tracker -->
            <select x-model="assignedFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Staff</option>
                <template x-for="s in staffList" :key="s.id">
                    <option :value="s.id" x-text="s.full_name"></option>
                </template>
            </select>
            <button @click="resetFilters()"
                    class="px-3 py-2 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg"
                    x-show="search || statusFilter || tierFilter || assignedFilter">
                Reset
            </button>
        </div>
    </div>

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex justify-center py-20"><div class="spinner"></div></div>
    </template>

    <!-- Table -->
    <template x-if="!loading">
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border"
             x-init="initScrollContainer($el)">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            <th class="cursor-pointer select-none" @click="sort('client_name')">
                                <div class="flex items-center gap-1">Client
                                    <template x-if="sortBy === 'client_name'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('case_number')">
                                <div class="flex items-center gap-1">Case #
                                    <template x-if="sortBy === 'case_number'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('insurance_carrier')">
                                <div class="flex items-center gap-1">Carrier
                                    <template x-if="sortBy === 'insurance_carrier'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('overall_status')">
                                <div class="flex items-center gap-1">Status
                                    <template x-if="sortBy === 'overall_status'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('last_request_date')">
                                <div class="flex items-center gap-1">Last Request
                                    <template x-if="sortBy === 'last_request_date'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('request_count')">
                                <div class="flex items-center gap-1">#
                                    <template x-if="sortBy === 'request_count'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th>Follow-up</th>
                            <th>Days</th>
                            <th>Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="items.length === 0">
                            <tr><td colspan="11" class="text-center text-v2-text-light py-12">No records found</td></tr>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <tr>
                                <!-- Main row -->
                                <td colspan="11" class="!p-0">
                                    <div>
                                        <div class="flex items-center cursor-pointer hover:bg-v2-bg/50 transition-colors"
                                             :class="{ 'hl-row-followup': item.is_followup_due }"
                                             @click="toggleExpand(item.id)">
                                            <div class="w-10 px-3 py-3 flex-shrink-0">
                                                <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="expandedId === item.id ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 4.707a1 1 0 011.414 0L14.414 10l-5.707 5.707a1 1 0 01-1.414-1.414L11.586 10 7.293 5.707a1 1 0 010-1z"/></svg>
                                            </div>
                                            <div class="flex-1 grid grid-cols-10 gap-2 py-3 pr-3 items-center text-sm">
                                                <div class="col-span-1 truncate font-medium" x-text="item.client_name"></div>
                                                <div class="col-span-1">
                                                    <template x-if="item.case_id">
                                                        <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id" class="text-gold hover:underline" x-text="item.case_number" @click.stop></a>
                                                    </template>
                                                    <template x-if="!item.case_id">
                                                        <span class="text-v2-text-light" x-text="item.case_number || '-'"></span>
                                                    </template>
                                                </div>
                                                <div class="col-span-1 truncate" x-text="item.insurance_carrier"></div>
                                                <div class="col-span-1">
                                                    <span class="status-badge" :class="'status-' + item.overall_status" x-text="getStatusLabel(item.overall_status)"></span>
                                                </div>
                                                <div class="col-span-1 whitespace-nowrap">
                                                    <template x-if="item.last_request_date">
                                                        <div class="flex items-center gap-1">
                                                            <span x-text="formatDate(item.last_request_date)"></span>
                                                            <span class="text-xs px-1 py-0.5 rounded bg-v2-bg text-v2-text-mid" x-text="item.last_request_method || ''"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!item.last_request_date"><span class="text-gray-300">-</span></template>
                                                </div>
                                                <div class="col-span-1 text-center" x-text="item.request_count || '-'"></div>
                                                <div class="col-span-1 whitespace-nowrap">
                                                    <template x-if="item.next_followup_date">
                                                        <span :class="item.is_followup_due ? 'text-amber-600 font-medium' : ''" x-text="formatDate(item.next_followup_date)"></span>
                                                    </template>
                                                    <template x-if="!item.next_followup_date"><span class="text-gray-300">-</span></template>
                                                </div>
                                                <div class="col-span-1 text-center">
                                                    <template x-if="item.days_since_request !== null">
                                                        <span :class="item.days_since_request > 30 ? 'text-red-500 font-medium' : item.days_since_request > 14 ? 'text-amber-600' : ''" x-text="item.days_since_request + 'd'"></span>
                                                    </template>
                                                    <template x-if="item.days_since_request === null"><span class="text-gray-300">-</span></template>
                                                </div>
                                                <div class="col-span-1 truncate text-v2-text-mid" x-text="item.assigned_name || '-'"></div>
                                                <div class="col-span-1 flex gap-1" @click.stop>
                                                    <button @click="openRequestModal(item)" class="px-2 py-1 text-xs bg-gold/10 text-gold rounded hover:bg-gold/20" title="New Request">Request</button>
                                                    <button @click="updateStatus(item.id, 'received')" class="p-1 text-v2-text-light hover:text-green-600 rounded" title="Mark Received" x-show="item.overall_status !== 'received' && item.overall_status !== 'done'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                    <button @click="openEditModal(item)" class="p-1 text-v2-text-light hover:text-gold rounded" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    </button>
                                                    <button @click="deleteItem(item.id)" class="p-1 text-v2-text-light hover:text-red-500 rounded" title="Delete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Expanded: Request History -->
                                        <template x-if="expandedId === item.id">
                                            <div class="bg-slate-50 border-t border-v2-card-border px-6 py-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <h4 class="text-sm font-semibold text-v2-text">Request History</h4>
                                                    <button @click="openRequestModal(item)" class="px-3 py-1 text-xs bg-gold text-navy font-semibold rounded hover:bg-gold/90">+ New Request</button>
                                                </div>
                                                <template x-if="requestHistory.length === 0">
                                                    <p class="text-sm text-v2-text-light py-4 text-center">No requests yet</p>
                                                </template>
                                                <template x-if="requestHistory.length > 0">
                                                    <div class="space-y-2">
                                                        <template x-for="req in requestHistory" :key="req.id">
                                                            <div class="flex items-center gap-4 bg-white rounded-lg px-4 py-3 border border-v2-card-border text-sm">
                                                                <span class="font-medium whitespace-nowrap" x-text="formatDate(req.request_date)"></span>
                                                                <span class="px-2 py-0.5 rounded text-xs font-medium"
                                                                      :class="{
                                                                          'bg-teal-100 text-teal-700': req.request_method === 'email',
                                                                          'bg-purple-100 text-purple-700': req.request_method === 'fax',
                                                                          'bg-blue-100 text-blue-700': req.request_method === 'portal',
                                                                          'bg-amber-100 text-amber-700': req.request_method === 'phone',
                                                                          'bg-gray-100 text-gray-700': req.request_method === 'mail'
                                                                      }" x-text="req.request_method"></span>
                                                                <span class="text-xs text-v2-text-light capitalize" x-text="req.request_type.replace('_',' ')"></span>
                                                                <span class="text-xs text-v2-text-mid" x-text="req.sent_to ? 'To: ' + req.sent_to : ''"></span>
                                                                <div class="flex-1"></div>
                                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                                                      :class="{
                                                                          'bg-gray-100 text-gray-600': req.send_status === 'draft',
                                                                          'bg-yellow-100 text-yellow-700': req.send_status === 'sending',
                                                                          'bg-green-100 text-green-700': req.send_status === 'sent',
                                                                          'bg-red-100 text-red-700': req.send_status === 'failed'
                                                                      }" x-text="req.send_status"></span>
                                                                <template x-if="req.send_status === 'draft' && (req.request_method === 'email' || req.request_method === 'fax')">
                                                                    <button @click="openSendModal(req)" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Preview & Send</button>
                                                                </template>
                                                                <template x-if="req.send_status === 'failed' && (req.request_method === 'email' || req.request_method === 'fax')">
                                                                    <button @click="openSendModal(req)" class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">Retry</button>
                                                                </template>
                                                                <template x-if="req.send_status === 'sent'">
                                                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                </template>
                                                                <span class="text-xs text-v2-text-light" x-text="req.created_by_name || ''"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
        </div>
    </template>

    <!-- Add/Edit Item Modal -->
    <div x-show="showAddModal || showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="closeModals()"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10" @click.stop>
            <div class="modal-v2-header">
                <div class="modal-v2-title" x-text="showEditModal ? 'Edit Item' : 'Add Item'"></div>
                <button type="button" class="modal-v2-close" @click="closeModals()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-v2-body" style="max-height:calc(90vh - 140px);overflow-y:auto;">
                <!-- Case Search (Add mode only) -->
                <template x-if="showAddModal">
                    <div>
                        <label class="form-v2-label">Search Case</label>
                        <div class="relative">
                            <input type="text" x-model="caseSearch" @input.debounce.300ms="searchCases()"
                                   @focus="showCaseDropdown = caseResults.length > 0"
                                   @click.away="showCaseDropdown = false"
                                   placeholder="Type client name or case #..."
                                   class="form-v2-input">
                            <template x-if="form.case_number && form.client_name">
                                <div class="mt-2 px-3 py-2 bg-gold/10 border border-gold/30 rounded-lg flex items-center justify-between">
                                    <span class="text-sm"><span class="font-medium" x-text="form.client_name"></span> <span class="text-v2-text-light" x-text="'#' + form.case_number"></span></span>
                                    <button @click="clearCaseSelection()" class="text-xs text-red-500 hover:text-red-700">Clear</button>
                                </div>
                            </template>
                            <template x-if="showCaseDropdown && caseResults.length > 0">
                                <div class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="c in caseResults" :key="c.id">
                                        <div @click="selectCase(c)" class="px-3 py-2 hover:bg-gold/10 cursor-pointer text-sm border-b border-v2-card-border last:border-0">
                                            <span class="font-medium" x-text="c.client_name"></span>
                                            <span class="text-v2-text-light ml-2" x-text="'#' + c.case_number"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <!-- Case info (Edit mode - read only) -->
                <template x-if="showEditModal">
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Client Name *</label>
                            <input type="text" x-model="form.client_name" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Case #</label>
                            <input type="text" x-model="form.case_number" class="form-v2-input" style="background:var(--bg);" readonly>
                        </div>
                    </div>
                </template>
                <div class="form-v2-row">
                    <div>
                        <label class="form-v2-label">Insurance Carrier *</label>
                        <input type="text" x-model="form.insurance_carrier" class="form-v2-input">
                    </div>
                    <div>
                        <label class="form-v2-label">Assigned To</label>
                        <select x-model="form.assigned_to" class="form-v2-select">
                            <option value="">Select...</option>
                            <template x-for="s in staffList" :key="s.id"><option :value="s.id" x-text="s.full_name"></option></template>
                        </select>
                    </div>
                </div>
                <div class="form-v2-row">
                    <div>
                        <label class="form-v2-label">Carrier Email</label>
                        <input type="email" x-model="form.carrier_contact_email" class="form-v2-input" placeholder="claims@carrier.com">
                    </div>
                    <div>
                        <label class="form-v2-label">Carrier Fax</label>
                        <input type="text" x-model="form.carrier_contact_fax" class="form-v2-input" placeholder="(xxx) xxx-xxxx">
                    </div>
                </div>
                <div>
                    <label class="form-v2-label">Note</label>
                    <textarea x-model="form.note" rows="2" class="form-v2-textarea"></textarea>
                </div>
            </div>
            <div class="modal-v2-footer">
                <button @click="closeModals()" class="btn-v2-cancel">Cancel</button>
                <button @click="saveItem()" :disabled="saving" class="btn-v2-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- New Request Modal -->
    <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showRequestModal = false"></div>
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <div class="modal-v2-header">
                <div>
                    <div class="modal-v2-title">New Request</div>
                    <div class="modal-v2-subtitle" x-text="reqForm._carrierLabel"></div>
                </div>
                <button type="button" class="modal-v2-close" @click="showRequestModal = false">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-v2-body">
                <div class="form-v2-row">
                    <div>
                        <label class="form-v2-label">Request Date *</label>
                        <input type="date" x-model="reqForm.request_date" class="form-v2-input">
                    </div>
                    <div>
                        <label class="form-v2-label">Method *</label>
                        <select x-model="reqForm.request_method" @change="updateRecipient()" class="form-v2-select">
                            <option value="">Select...</option>
                            <option value="email">Email</option>
                            <option value="fax">Fax</option>
                            <option value="portal">Portal</option>
                            <option value="phone">Phone</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                </div>
                <div class="form-v2-row">
                    <div>
                        <label class="form-v2-label">Type</label>
                        <select x-model="reqForm.request_type" class="form-v2-select">
                            <option value="initial">Initial</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="re_request">Re-Request</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-v2-label">Send To</label>
                        <input type="text" x-model="reqForm.sent_to" class="form-v2-input" placeholder="Email or fax #">
                    </div>
                </div>
                <div>
                    <label class="form-v2-label">Notes</label>
                    <textarea x-model="reqForm.notes" rows="2" class="form-v2-textarea"></textarea>
                </div>
            </div>
            <div class="modal-v2-footer">
                <button @click="showRequestModal = false" class="btn-v2-cancel">Cancel</button>
                <button @click="submitRequest()" :disabled="saving" class="btn-v2-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span x-text="saving ? 'Creating...' : 'Create Request'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Preview & Send Modal -->
    <div x-show="showSendModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showSendModal = false"></div>
        <div class="modal-v2 relative w-full max-w-4xl z-10 flex flex-col" style="max-height:90vh;" @click.stop>
            <div class="modal-v2-header">
                <div>
                    <div class="modal-v2-title">Preview & Send</div>
                    <div class="modal-v2-subtitle" x-text="previewData.carrier + ' via ' + previewData.method"></div>
                </div>
                <button type="button" class="modal-v2-close" @click="showSendModal = false">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-6">
                <iframe :srcdoc="previewData.letter_html" class="w-full border rounded-lg" style="height: 500px;"></iframe>
            </div>
            <div class="modal-v2-footer">
                <div class="flex items-center gap-4 w-full">
                    <label class="form-v2-label" style="margin-bottom:0;white-space:nowrap;">Recipient:</label>
                    <input type="text" x-model="previewData.recipient" class="form-v2-input flex-1">
                    <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient"
                            class="btn-v2-primary"
                            :class="previewData.method === 'email' ? '!bg-teal-600 hover:!bg-teal-700' : '!bg-purple-600 hover:!bg-purple-700'"
                            style="flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showImportModal = false; importFile = null; importResult = null;"></div>
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <div class="modal-v2-header">
                <div class="modal-v2-title">Import CSV</div>
                <button type="button" class="modal-v2-close" @click="showImportModal = false; importFile = null; importResult = null;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-v2-body">
                <div class="border-2 border-dashed border-v2-card-border rounded-lg p-8 text-center"
                     :class="dragover ? 'border-gold bg-gold/5' : ''"
                     @dragover.prevent="dragover = true" @dragleave="dragover = false"
                     @drop.prevent="dragover = false; importFile = $event.dataTransfer.files[0]">
                    <template x-if="!importFile">
                        <div>
                            <p class="text-sm text-v2-text-mid mb-2">Drag & drop CSV, or</p>
                            <label class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg cursor-pointer hover:bg-gold/90">
                                Browse <input type="file" accept=".csv" class="hidden" @change="importFile = $event.target.files[0]">
                            </label>
                        </div>
                    </template>
                    <template x-if="importFile">
                        <div>
                            <p class="text-sm font-medium" x-text="importFile.name"></p>
                            <button @click="importFile = null" class="text-xs text-red-500 mt-1">Remove</button>
                        </div>
                    </template>
                </div>
                <template x-if="importResult">
                    <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-sm">
                        <p x-text="'Items: ' + importResult.items_created + ', Requests: ' + importResult.requests_created + ', Skipped: ' + importResult.skipped"></p>
                    </div>
                </template>
            </div>
            <div class="modal-v2-footer">
                <button @click="showImportModal = false; importFile = null; importResult = null;" class="btn-v2-cancel">Close</button>
                <button @click="doImport()" :disabled="!importFile || importing" class="btn-v2-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span x-text="importing ? 'Importing...' : 'Import'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .hl-row-followup { background-color: #fffbeb; border-left: 3px solid #f59e0b; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
