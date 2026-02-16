<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Health Tracker';
$currentPage = 'health-ledger';
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
            <select x-model="assignedFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Staff</option>
                <template x-for="s in staffList" :key="s.id">
                    <option :value="s.id" x-text="s.full_name"></option>
                </template>
            </select>
            <button @click="resetFilters()"
                    class="px-3 py-2 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg"
                    x-show="search || statusFilter || assignedFilter">
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
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden">
            <div class="overflow-x-auto">
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
        </div>
    </template>

    <!-- Add/Edit Item Modal -->
    <template x-if="showAddModal || showEditModal">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="closeModals()">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                    <h3 class="text-lg font-semibold" x-text="showEditModal ? 'Edit Item' : 'Add Item'"></h3>
                    <button @click="closeModals()" class="text-v2-text-light hover:text-v2-text"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Case Search (Add mode only) -->
                    <template x-if="showAddModal">
                        <div>
                            <label class="block text-sm font-medium text-v2-text-mid mb-1">Search Case</label>
                            <div class="relative">
                                <input type="text" x-model="caseSearch" @input.debounce.300ms="searchCases()"
                                       @focus="showCaseDropdown = caseResults.length > 0"
                                       @click.away="showCaseDropdown = false"
                                       placeholder="Type client name or case #..."
                                       class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
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
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Client Name *</label>
                                <input type="text" x-model="form.client_name" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"></div>
                            <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Case #</label>
                                <input type="text" x-model="form.case_number" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none bg-v2-bg" readonly></div>
                        </div>
                    </template>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Insurance Carrier *</label>
                            <input type="text" x-model="form.insurance_carrier" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"></div>
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Assigned To</label>
                            <select x-model="form.assigned_to" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                                <option value="">Select...</option>
                                <template x-for="s in staffList" :key="s.id"><option :value="s.id" x-text="s.full_name"></option></template>
                            </select></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Carrier Email</label>
                            <input type="email" x-model="form.carrier_contact_email" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none" placeholder="claims@carrier.com"></div>
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Carrier Fax</label>
                            <input type="text" x-model="form.carrier_contact_fax" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none" placeholder="(xxx) xxx-xxxx"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Note</label>
                        <textarea x-model="form.note" rows="2" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"></textarea></div>
                </div>
                <div class="px-6 py-4 border-t border-v2-card-border flex justify-end gap-3">
                    <button @click="closeModals()" class="px-4 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button @click="saveItem()" :disabled="saving" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- New Request Modal -->
    <template x-if="showRequestModal">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showRequestModal = false">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
                <div class="px-6 py-4 border-b border-v2-card-border">
                    <h3 class="text-lg font-semibold">New Request</h3>
                    <p class="text-sm text-v2-text-light" x-text="reqForm._carrierLabel"></p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Request Date *</label>
                            <input type="date" x-model="reqForm.request_date" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"></div>
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Method *</label>
                            <select x-model="reqForm.request_method" @change="updateRecipient()" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                                <option value="">Select...</option>
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                                <option value="portal">Portal</option>
                                <option value="phone">Phone</option>
                                <option value="mail">Mail</option>
                            </select></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Type</label>
                            <select x-model="reqForm.request_type" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                                <option value="initial">Initial</option>
                                <option value="follow_up">Follow Up</option>
                                <option value="re_request">Re-Request</option>
                            </select></div>
                        <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Send To</label>
                            <input type="text" x-model="reqForm.sent_to" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none" placeholder="Email or fax #"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-v2-text-mid mb-1">Notes</label>
                        <textarea x-model="reqForm.notes" rows="2" class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"></textarea></div>
                </div>
                <div class="px-6 py-4 border-t border-v2-card-border flex justify-end gap-3">
                    <button @click="showRequestModal = false" class="px-4 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button @click="submitRequest()" :disabled="saving" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90 disabled:opacity-50">
                        <span x-text="saving ? 'Creating...' : 'Create Request'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Preview & Send Modal -->
    <template x-if="showSendModal">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showSendModal = false">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Preview & Send</h3>
                        <p class="text-sm text-v2-text-light" x-text="previewData.carrier + ' via ' + previewData.method"></p>
                    </div>
                    <button @click="showSendModal = false" class="text-v2-text-light hover:text-v2-text"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="flex-1 overflow-auto p-6">
                    <iframe :srcdoc="previewData.letter_html" class="w-full border rounded-lg" style="height: 500px;"></iframe>
                </div>
                <div class="px-6 py-4 border-t border-v2-card-border">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-v2-text-mid">Recipient:</label>
                        <input type="text" x-model="previewData.recipient" class="flex-1 px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                        <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient"
                                class="px-6 py-2 text-sm font-semibold rounded-lg disabled:opacity-50"
                                :class="previewData.method === 'email' ? 'bg-teal-600 text-white hover:bg-teal-700' : 'bg-purple-600 text-white hover:bg-purple-700'">
                            <span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Import Modal -->
    <template x-if="showImportModal">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showImportModal = false">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
                <div class="px-6 py-4 border-b border-v2-card-border"><h3 class="text-lg font-semibold">Import CSV</h3></div>
                <div class="p-6">
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
                        <div class="mt-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm">
                            <p x-text="'Items: ' + importResult.items_created + ', Requests: ' + importResult.requests_created + ', Skipped: ' + importResult.skipped"></p>
                        </div>
                    </template>
                </div>
                <div class="px-6 py-4 border-t border-v2-card-border flex justify-end gap-3">
                    <button @click="showImportModal = false; importFile = null; importResult = null;" class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Close</button>
                    <button @click="doImport()" :disabled="!importFile || importing" class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg disabled:opacity-50">
                        <span x-text="importing ? 'Importing...' : 'Import'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    .hl-row-followup { background-color: #fffbeb; border-left: 3px solid #f59e0b; }
</style>

<script>
function healthLedgerPage() {
    return {
        items: [], pagination: null, loading: true, saving: false, sending: false,
        summary: {},
        search: '', statusFilter: '', assignedFilter: '',
        sortBy: 'created_at', sortDir: 'desc',
        staffList: [],
        expandedId: null, requestHistory: [],
        showAddModal: false, showEditModal: false, showRequestModal: false, showSendModal: false, showImportModal: false,
        editId: null,
        form: {},
        reqForm: {},
        previewData: {},
        importFile: null, importResult: null, importing: false, dragover: false,
        caseSearch: '', caseResults: [], showCaseDropdown: false,

        async init() {
            this.form = this.getEmptyForm();
            this.loadStaff();
            await this.loadData(1);
        },

        getEmptyForm() {
            return { client_name: '', case_number: '', insurance_carrier: '', carrier_contact_email: '', carrier_contact_fax: '', assigned_to: '', note: '' };
        },

        async loadStaff() {
            try { const r = await api.get('users?active_only=1'); this.staffList = r.data || []; } catch(e) {}
        },

        async loadData(page) {
            this.loading = true;
            try {
                let p = `?per_page=99999`;
                if (this.search) p += `&search=${encodeURIComponent(this.search)}`;
                if (this.statusFilter) p += `&status=${this.statusFilter}`;
                if (this.assignedFilter) p += `&assigned_to=${this.assignedFilter}`;
                p += `&sort_by=${this.sortBy}&sort_dir=${this.sortDir}`;
                const r = await api.get('health-ledger/list' + p);
                this.items = r.data || [];
                this.pagination = r.pagination || null;
                if (r.summary) this.summary = r.summary;
            } catch(e) { showToast('Failed to load data', 'error'); }
            this.loading = false;
        },

        sort(col) {
            if (this.sortBy === col) { this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; }
            else { this.sortBy = col; this.sortDir = 'asc'; }
            this.loadData(1);
        },

        toggleStatusFilter(s) { this.statusFilter = this.statusFilter === s ? '' : s; this.loadData(1); },
        resetFilters() { this.search = ''; this.statusFilter = ''; this.assignedFilter = ''; this.sortBy = 'created_at'; this.sortDir = 'desc'; this.loadData(1); },

        async toggleExpand(id) {
            if (this.expandedId === id) { this.expandedId = null; return; }
            this.expandedId = id;
            this.requestHistory = [];
            try {
                const r = await api.get(`health-ledger/${id}/requests`);
                this.requestHistory = r.data || [];
            } catch(e) { showToast('Failed to load requests', 'error'); }
        },

        getStatusLabel(s) {
            const m = { not_started: 'Not Started', requesting: 'Requesting', follow_up: 'Follow Up', received: 'Received', done: 'Done' };
            return m[s] || s;
        },

        openAddModal() { this.form = this.getEmptyForm(); this.editId = null; this.caseSearch = ''; this.caseResults = []; this.showCaseDropdown = false; this.showEditModal = false; this.showAddModal = true; },
        openEditModal(item) {
            this.form = { client_name: item.client_name, case_number: item.case_number || '', insurance_carrier: item.insurance_carrier, carrier_contact_email: item.carrier_contact_email || '', carrier_contact_fax: item.carrier_contact_fax || '', assigned_to: item.assigned_to || '', note: item.note || '' };
            this.editId = item.id; this.showAddModal = false; this.showEditModal = true;
        },
        closeModals() { this.showAddModal = false; this.showEditModal = false; this.editId = null; this.showCaseDropdown = false; },

        async searchCases() {
            if (this.caseSearch.length < 2) { this.caseResults = []; this.showCaseDropdown = false; return; }
            try {
                const r = await api.get('cases?search=' + encodeURIComponent(this.caseSearch) + '&per_page=10');
                this.caseResults = r.data || [];
                this.showCaseDropdown = this.caseResults.length > 0;
            } catch(e) { this.caseResults = []; }
        },
        selectCase(c) {
            this.form.client_name = c.client_name;
            this.form.case_number = c.case_number;
            this.caseSearch = '';
            this.caseResults = [];
            this.showCaseDropdown = false;
        },
        clearCaseSelection() {
            this.form.client_name = '';
            this.form.case_number = '';
            this.caseSearch = '';
        },

        async saveItem() {
            if (!this.form.client_name || !this.form.insurance_carrier) { showToast('Client name and carrier required', 'error'); return; }
            this.saving = true;
            try {
                if (this.showEditModal && this.editId) { await api.put('health-ledger/' + this.editId, this.form); showToast('Updated'); }
                else { await api.post('health-ledger', this.form); showToast('Created'); }
                this.closeModals(); this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Error', 'error'); }
            this.saving = false;
        },

        async deleteItem(id) {
            if (!await confirmAction('Delete this item and all its requests?')) return;
            try { await api.delete('health-ledger/' + id); showToast('Deleted'); this.loadData(this.pagination?.page || 1); }
            catch(e) { showToast('Delete failed', 'error'); }
        },

        async updateStatus(id, status) {
            try { await api.put('health-ledger/' + id, { overall_status: status }); showToast('Status updated'); this.loadData(this.pagination?.page || 1); }
            catch(e) { showToast('Update failed', 'error'); }
        },

        openRequestModal(item) {
            this.reqForm = {
                item_id: item.id,
                request_date: new Date().toISOString().split('T')[0],
                request_method: '', request_type: item.request_count > 0 ? 'follow_up' : 'initial',
                sent_to: '', notes: '',
                _carrierLabel: item.client_name + ' - ' + item.insurance_carrier,
                _email: item.carrier_contact_email || '', _fax: item.carrier_contact_fax || ''
            };
            this.showRequestModal = true;
        },

        updateRecipient() {
            if (this.reqForm.request_method === 'email') this.reqForm.sent_to = this.reqForm._email;
            else if (this.reqForm.request_method === 'fax') this.reqForm.sent_to = this.reqForm._fax;
            else this.reqForm.sent_to = '';
        },

        async submitRequest() {
            if (!this.reqForm.request_date || !this.reqForm.request_method) { showToast('Date and method required', 'error'); return; }
            this.saving = true;
            try {
                await api.post('health-ledger/request', this.reqForm);
                showToast('Request created');
                this.showRequestModal = false;
                if (this.expandedId === this.reqForm.item_id) { this.toggleExpand(this.reqForm.item_id); this.toggleExpand(this.reqForm.item_id); }
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Error', 'error'); }
            this.saving = false;
        },

        async openSendModal(req) {
            try {
                const r = await api.get(`health-ledger/${req.id}/preview`);
                this.previewData = r.data;
                this.showSendModal = true;
            } catch(e) { showToast('Failed to load preview', 'error'); }
        },

        async confirmAndSend() {
            if (!this.previewData.recipient) { showToast('Recipient required', 'error'); return; }
            this.sending = true;
            try {
                await api.post(`health-ledger/${this.previewData.request_id}/send`, { recipient: this.previewData.recipient });
                showToast('Sent successfully!');
                this.showSendModal = false;
                if (this.expandedId) { const id = this.expandedId; this.expandedId = null; this.toggleExpand(id); }
                this.loadData(this.pagination?.page || 1);
            } catch(e) { showToast(e.data?.message || 'Send failed', 'error'); }
            this.sending = false;
        },

        async doImport() {
            if (!this.importFile) return;
            this.importing = true; this.importResult = null;
            try {
                const fd = new FormData(); fd.append('file', this.importFile);
                const r = await api.upload('health-ledger/import', fd);
                this.importResult = r.data; this.importFile = null;
                showToast(r.message || 'Import complete'); this.loadData(1);
            } catch(e) { showToast(e.data?.message || 'Import failed', 'error'); }
            this.importing = false;
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
