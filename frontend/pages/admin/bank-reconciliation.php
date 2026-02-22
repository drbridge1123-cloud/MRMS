<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requirePermission('reconciliation');
$pageTitle = 'Bank Reconciliation';
$currentPage = 'admin-reconciliation';
$pageScripts = ['/MRMS/frontend/assets/js/pages/bank-reconciliation.js'];
ob_start();
?>

<div x-data="bankReconciliationPage()" x-init="init()">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-v2-text">Bank Reconciliation</h1>
            <p class="text-sm text-v2-text-light mt-1">Import bank statements and match with payment records</p>
        </div>
        <button @click="showImportModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-gold text-white rounded-lg hover:bg-gold-hover transition-colors font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            Import CSV
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div @click="toggleStatusFilter('')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === '' ? 'ring-2 ring-gold' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Total Entries</p>
            <p class="text-2xl font-bold text-v2-text mt-1" x-text="summary.total_count ?? '-'"></p>
            <p class="text-xs text-v2-text-light mt-1" x-text="formatMoney(summary.total_amount)"></p>
        </div>
        <div @click="toggleStatusFilter('matched')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === 'matched' ? 'ring-2 ring-green-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Matched</p>
            <p class="text-2xl font-bold text-green-600 mt-1" x-text="summary.matched_count ?? '-'"></p>
            <p class="text-xs text-green-600 mt-1" x-text="formatMoney(summary.matched_amount)"></p>
        </div>
        <div @click="toggleStatusFilter('unmatched')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === 'unmatched' ? 'ring-2 ring-red-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Unmatched</p>
            <p class="text-2xl font-bold text-red-600 mt-1" x-text="summary.unmatched_count ?? '-'"></p>
            <p class="text-xs text-red-600 mt-1" x-text="formatMoney(summary.unmatched_amount)"></p>
        </div>
        <div @click="toggleStatusFilter('ignored')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === 'ignored' ? 'ring-2 ring-gray-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Ignored</p>
            <p class="text-2xl font-bold text-gray-500 mt-1" x-text="summary.ignored_count ?? '-'"></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                       placeholder="Search description, check #, reference..."
                       class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>

            <div class="flex items-center gap-1">
                <label class="text-xs text-v2-text-light">From</label>
                <input type="date" x-model="dateFrom" @change="loadData(1)"
                       class="px-2 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>
            <div class="flex items-center gap-1">
                <label class="text-xs text-v2-text-light">To</label>
                <input type="date" x-model="dateTo" @change="loadData(1)"
                       class="px-2 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>

            <select x-model="staffFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Staff</option>
                <option value="Sunny">Sunny</option>
                <option value="Soyong">Soyong</option>
                <option value="Jimi">Jimi</option>
                <option value="Karl">Karl</option>
                <option value="Miki">Miki</option>
                <option value="Ella">Ella</option>
                <option value="Dave">Dave</option>
                <option value="Chloe">Chloe</option>
            </select>

            <select x-model="batchFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Imports</option>
                <template x-for="b in batches" :key="b.batch_id">
                    <option :value="b.batch_id" x-text="formatDate(b.imported_at) + ' (' + b.entry_count + ' entries)'"></option>
                </template>
            </select>

            <button @click="resetFilters()"
                    class="px-3 py-2 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg"
                    x-show="hasActiveFilters()">
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
                        <th class="w-10">
                            <input type="checkbox" @change="toggleSelectAll($event.target.checked)"
                                   :checked="isAllSelected()" :indeterminate.prop="isIndeterminate()"
                                   class="rounded border-gray-300 text-gold focus:ring-gold">
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('transaction_date')">
                            <div class="flex items-center gap-1">Date <span x-html="sortIcon('transaction_date')"></span></div>
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('description')">
                            <div class="flex items-center gap-1">Description <span x-html="sortIcon('description')"></span></div>
                        </th>
                        <th class="cursor-pointer select-none text-right" @click="sort('amount')">
                            <div class="flex items-center justify-end gap-1">Amount <span x-html="sortIcon('amount')"></span></div>
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('check_number')">
                            <div class="flex items-center gap-1">Check # <span x-html="sortIcon('check_number')"></span></div>
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('card_holder')">
                            <div class="flex items-center gap-1">Card Holder <span x-html="sortIcon('card_holder')"></span></div>
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('reconciliation_status')">
                            <div class="flex items-center gap-1">Status <span x-html="sortIcon('reconciliation_status')"></span></div>
                        </th>
                        <th>Matched Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="items.length === 0">
                        <tr><td colspan="8" class="text-center text-v2-text-light py-12">No entries found. Import a bank statement CSV to get started.</td></tr>
                    </template>
                    <template x-for="item in items" :key="item.id">
                        <tr class="transition-colors cursor-pointer"
                            :class="{
                                'bg-green-50/50 hover:bg-green-50': item.reconciliation_status === 'matched',
                                'bg-gray-50/50 hover:bg-gray-100/50': item.reconciliation_status === 'ignored',
                                'hover:bg-v2-bg/50': item.reconciliation_status === 'unmatched',
                                'bg-gold/5': selectedIds.includes(item.id)
                            }"
                            @click="onRowClick(item)">
                            <td class="w-10" @click.stop>
                                <input type="checkbox" :value="item.id" :checked="selectedIds.includes(item.id)"
                                       @change="toggleSelect(item.id)"
                                       class="rounded border-gray-300 text-gold focus:ring-gold">
                            </td>
                            <td class="whitespace-nowrap text-sm" x-text="formatDate(item.transaction_date)"></td>
                            <td class="max-w-[250px] truncate text-sm" x-text="item.description || '-'"></td>
                            <td class="text-right font-mono text-sm font-medium" x-text="formatMoney(item.amount)"></td>
                            <td class="text-sm" x-text="item.check_number || '-'"></td>
                            <td class="text-sm" x-text="item.card_holder || '-'"></td>
                            <td>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      :class="{
                                          'bg-green-100 text-green-700': item.reconciliation_status === 'matched',
                                          'bg-red-100 text-red-700': item.reconciliation_status === 'unmatched',
                                          'bg-gray-100 text-gray-500': item.reconciliation_status === 'ignored'
                                      }"
                                      x-text="item.reconciliation_status.charAt(0).toUpperCase() + item.reconciliation_status.slice(1)"></span>
                            </td>
                            <td class="text-sm">
                                <template x-if="item.reconciliation_status === 'matched' && item.matched_case_number">
                                    <div>
                                        <span class="text-gold font-medium cursor-pointer hover:underline"
                                              @click.stop="goToCase(item.matched_case_id)"
                                              x-text="item.matched_case_number"></span>
                                        <span class="text-v2-text-light"> &mdash; </span>
                                        <span x-text="item.matched_provider_name || item.matched_description || ''"></span>
                                        <span class="text-v2-text-light font-mono text-xs ml-1" x-text="formatMoney(item.matched_paid_amount)"></span>
                                    </div>
                                </template>
                                <template x-if="item.reconciliation_status !== 'matched'">
                                    <span class="text-gray-300">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    <!-- Pagination -->
    <template x-if="pagination && pagination.total_pages > 1">
        <div class="flex items-center justify-between mt-4">
            <div class="text-sm text-v2-text-light">
                Showing <span x-text="((pagination.page - 1) * pagination.per_page) + 1"></span>-<span x-text="Math.min(pagination.page * pagination.per_page, pagination.total)"></span> of <span x-text="pagination.total"></span>
            </div>
            <div class="flex gap-1">
                <button @click="loadData(pagination.page - 1)" :disabled="pagination.page <= 1"
                        class="px-3 py-1.5 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg disabled:opacity-40 disabled:cursor-not-allowed">
                    Prev
                </button>
                <template x-for="p in getPageNumbers()" :key="p">
                    <button @click="loadData(p)"
                            class="px-3 py-1.5 text-sm border rounded-lg"
                            :class="p === pagination.page ? 'bg-navy text-white border-navy' : 'border-v2-card-border hover:bg-v2-bg'"
                            x-text="p"></button>
                </template>
                <button @click="loadData(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages"
                        class="px-3 py-1.5 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg disabled:opacity-40 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </template>

    <!-- Floating Bulk Action Bar -->
    <div x-show="selectedIds.length > 0" x-transition
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-navy text-white rounded-xl shadow-2xl px-6 py-3 flex items-center gap-4">
        <span class="text-sm font-medium"><span x-text="selectedIds.length"></span> selected</span>
        <span class="w-px h-5 bg-white/30"></span>
        <button @click="bulkIgnore()" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
            Ignore
        </button>
        <button @click="bulkRestore()" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Restore
        </button>
        <button @click="bulkAutoMatch()" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-green-500/80 hover:bg-green-500 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.172 13.828a4 4 0 015.656 0l4-4a4 4 0 00-5.656-5.656l-1.102 1.101"/>
            </svg>
            Auto-Match
        </button>
        <span class="w-px h-5 bg-white/30"></span>
        <button @click="clearSelection()" class="text-sm text-white/70 hover:text-white transition-colors">Clear</button>
    </div>

    <!-- Import Modal -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showImportModal = false"></div>
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <div class="modal-v2-header">
                <div class="modal-v2-title">Import Bank Statement</div>
                <button type="button" class="modal-v2-close" @click="showImportModal = false">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-v2-body">
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                    <p class="font-medium text-blue-800 mb-2">CSV Format Requirements:</p>
                    <ul class="text-blue-700 space-y-1 text-xs">
                        <li><strong>Required:</strong> date, amount</li>
                        <li><strong>Optional:</strong> description, check_number, reference_number, category</li>
                        <li>Date formats: YYYY-MM-DD, MM/DD/YYYY, M/D/YYYY</li>
                        <li>Amount: numeric ($ and commas OK)</li>
                    </ul>
                </div>
                <div class="mb-4">
                    <label class="form-v2-label">CSV File</label>
                    <input type="file" accept=".csv" @change="importFile = $event.target.files[0]"
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                </div>
                <template x-if="importResult">
                    <div class="p-4 rounded-lg text-sm"
                         :class="importResult.skipped > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-green-50 border border-green-200'">
                        <p><strong x-text="importResult.imported"></strong> entries imported</p>
                        <p><strong x-text="importResult.auto_matched"></strong> auto-matched by check #</p>
                        <template x-if="importResult.skipped > 0">
                            <p class="text-amber-700"><strong x-text="importResult.skipped"></strong> skipped</p>
                        </template>
                        <template x-if="importResult.errors && importResult.errors.length > 0">
                            <div class="mt-2 max-h-24 overflow-y-auto">
                                <template x-for="err in importResult.errors" :key="err.row">
                                    <p class="text-xs text-red-600">Row <span x-text="err.row"></span>: <span x-text="err.message"></span></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="modal-v2-footer">
                <button @click="showImportModal = false; importResult = null;" class="btn-v2-cancel">Close</button>
                <button @click="doImport()" :disabled="!importFile || importing" class="btn-v2-primary">
                    <template x-if="importing"><span class="spinner-sm mr-2"></span></template>
                    Import
                </button>
            </div>
        </div>
    </div>

    <!-- Match Modal -->
    <div x-show="showMatchModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showMatchModal = false"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10 max-h-[80vh] flex flex-col" @click.stop>
            <div class="modal-v2-header">
                <div>
                    <div class="modal-v2-title">Match Bank Entry</div>
                    <template x-if="matchingEntry">
                        <div class="modal-v2-subtitle">
                            <span x-text="formatDate(matchingEntry.transaction_date)"></span> &mdash;
                            <span class="font-mono" x-text="formatMoney(matchingEntry.amount)"></span>
                            <template x-if="matchingEntry.check_number">
                                <span> &mdash; Check #<span x-text="matchingEntry.check_number"></span></span>
                            </template>
                        </div>
                    </template>
                </div>
                <button type="button" class="modal-v2-close" @click="showMatchModal = false">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-v2-body" style="flex:1;overflow-y:auto;">
                <div class="mb-4">
                    <input type="text" x-model="matchSearch" @input.debounce.300ms="searchPayments()"
                           placeholder="Search by case #, provider, check #, description..."
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <template x-if="matchSearching">
                    <div class="flex justify-center py-8"><div class="spinner"></div></div>
                </template>
                <template x-if="!matchSearching && matchResults.length === 0">
                    <div class="text-center text-v2-text-light py-8">No unmatched payments found</div>
                </template>
                <template x-if="!matchSearching && matchResults.length > 0">
                    <div class="space-y-2">
                        <template x-for="p in matchResults" :key="p.id">
                            <div class="border border-v2-card-border rounded-lg p-3 hover:bg-v2-bg/50 cursor-pointer transition-colors"
                                 :class="p.paid_amount == matchingEntry?.amount ? 'border-green-300 bg-green-50/30' : ''"
                                 @click="confirmMatch(p.id)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-gold font-medium" x-text="p.case_number"></span>
                                        <span class="text-v2-text-light mx-1">&mdash;</span>
                                        <span class="text-sm" x-text="p.provider_name || p.description || 'No description'"></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-mono font-medium" x-text="formatMoney(p.paid_amount)"></span>
                                        <template x-if="p.paid_amount == matchingEntry?.amount">
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Amount match</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-xs text-v2-text-light mt-1 flex gap-4">
                                    <span x-text="p.client_name"></span>
                                    <span x-text="formatDate(p.payment_date)"></span>
                                    <template x-if="p.check_number">
                                        <span>Check #<span x-text="p.check_number"></span></span>
                                    </template>
                                    <template x-if="p.paid_by_name">
                                        <span x-text="p.paid_by_name"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="modal-v2-footer">
                <button @click="showMatchModal = false" class="btn-v2-cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
