<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requirePermission('expense_report');
$pageTitle = 'Expense Report';
$currentPage = 'reports';
$pageScripts = ['/MRMS/frontend/assets/js/pages/expense-report.js'];
ob_start();
?>

<div x-data="expenseReportPage()" x-init="init()">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-v2-text">Expense Report</h1>
            <p class="text-sm text-v2-text-light mt-1">All MR fee payments across cases</p>
        </div>
        <button @click="exportCSV()"
                class="flex items-center gap-2 px-4 py-2 bg-gold text-white rounded-lg hover:bg-gold-hover transition-colors font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Total Payments</p>
            <p class="text-2xl font-bold text-v2-text mt-1" x-text="summary.total_count ?? '-'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Total Billed</p>
            <p class="text-2xl font-bold text-v2-text mt-1" x-text="formatMoney(summary.total_billed)"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Total Paid</p>
            <p class="text-2xl font-bold text-green-600 mt-1" x-text="formatMoney(summary.total_paid)"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Outstanding</p>
            <p class="text-2xl font-bold mt-1"
               :class="(summary.total_billed - summary.total_paid) > 0 ? 'text-red-600' : 'text-green-600'"
               x-text="formatMoney((summary.total_billed || 0) - (summary.total_paid || 0))"></p>
        </div>
    </div>

    <!-- Breakdown Cards Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- By Category -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <h3 class="text-xs text-v2-text-light uppercase tracking-wide mb-3">By Category</h3>
            <template x-if="summary.by_category && summary.by_category.length > 0">
                <div class="space-y-2">
                    <template x-for="cat in summary.by_category" :key="cat.expense_category">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full"
                                      :class="{
                                          'bg-blue-500': cat.expense_category === 'mr_cost',
                                          'bg-purple-500': cat.expense_category === 'litigation',
                                          'bg-gray-400': cat.expense_category === 'other'
                                      }"></span>
                                <span class="text-sm text-v2-text" x-text="getCategoryLabel(cat.expense_category)"></span>
                                <span class="text-xs text-v2-text-light" x-text="'(' + cat.count + ')'"></span>
                            </div>
                            <span class="text-sm font-mono font-medium" x-text="formatMoney(cat.total_paid)"></span>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!summary.by_category || summary.by_category.length === 0">
                <p class="text-sm text-v2-text-light">No data</p>
            </template>
        </div>

        <!-- By Staff -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <h3 class="text-xs text-v2-text-light uppercase tracking-wide mb-3">By Staff</h3>
            <template x-if="summary.by_staff && summary.by_staff.length > 0">
                <div class="space-y-2">
                    <template x-for="s in summary.by_staff" :key="s.paid_by">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-navy flex items-center justify-center text-white text-xs font-semibold"
                                     x-text="(s.staff_name || '?').charAt(0)"></div>
                                <span class="text-sm text-v2-text" x-text="s.staff_name || 'Unknown'"></span>
                                <span class="text-xs text-v2-text-light" x-text="'(' + s.count + ')'"></span>
                            </div>
                            <span class="text-sm font-mono font-medium" x-text="formatMoney(s.total_paid)"></span>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!summary.by_staff || summary.by_staff.length === 0">
                <p class="text-sm text-v2-text-light">No data</p>
            </template>
        </div>

        <!-- By Payment Type -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5">
            <h3 class="text-xs text-v2-text-light uppercase tracking-wide mb-3">By Payment Type</h3>
            <template x-if="summary.by_payment_type && summary.by_payment_type.length > 0">
                <div class="space-y-2">
                    <template x-for="t in summary.by_payment_type" :key="t.payment_type">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full"
                                      :class="{
                                          'bg-green-500': t.payment_type === 'check',
                                          'bg-blue-500': t.payment_type === 'card',
                                          'bg-amber-500': t.payment_type === 'cash',
                                          'bg-indigo-500': t.payment_type === 'wire',
                                          'bg-gray-400': t.payment_type === 'other'
                                      }"></span>
                                <span class="text-sm text-v2-text" x-text="getPaymentTypeLabel(t.payment_type)"></span>
                                <span class="text-xs text-v2-text-light" x-text="'(' + t.count + ')'"></span>
                            </div>
                            <span class="text-sm font-mono font-medium" x-text="formatMoney(t.total_paid)"></span>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!summary.by_payment_type || summary.by_payment_type.length === 0">
                <p class="text-sm text-v2-text-light">No data</p>
            </template>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                       placeholder="Search case #, client, provider, check #..."
                       class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>

            <!-- Date From -->
            <div class="flex items-center gap-1">
                <label class="text-xs text-v2-text-light">From</label>
                <input type="date" x-model="dateFrom" @change="loadData(1)"
                       class="px-2 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>

            <!-- Date To -->
            <div class="flex items-center gap-1">
                <label class="text-xs text-v2-text-light">To</label>
                <input type="date" x-model="dateTo" @change="loadData(1)"
                       class="px-2 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>

            <!-- Category filter -->
            <select x-model="categoryFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Categories</option>
                <option value="mr_cost">MR Cost</option>
                <option value="litigation">Litigation</option>
                <option value="other">Other</option>
            </select>

            <!-- Payment Type filter -->
            <select x-model="paymentTypeFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Types</option>
                <option value="check">Check</option>
                <option value="card">Card</option>
                <option value="cash">Cash</option>
                <option value="wire">Wire</option>
                <option value="other">Other</option>
            </select>

            <!-- Staff filter -->
            <select x-model="staffFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Staff</option>
                <template x-for="s in staffList" :key="s.id">
                    <option :value="s.id" x-text="s.full_name"></option>
                </template>
            </select>

            <!-- Reset -->
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
                        <th class="cursor-pointer select-none" @click="sort('payment_date')">
                            <div class="flex items-center gap-1">Date
                                <template x-if="sortBy === 'payment_date'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('case_number')">
                            <div class="flex items-center gap-1">Case #
                                <template x-if="sortBy === 'case_number'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th>Client</th>
                        <th class="cursor-pointer select-none" @click="sort('provider_name')">
                            <div class="flex items-center gap-1">Provider
                                <template x-if="sortBy === 'provider_name'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th>Description</th>
                        <th class="cursor-pointer select-none" @click="sort('expense_category')">
                            <div class="flex items-center gap-1">Category
                                <template x-if="sortBy === 'expense_category'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th class="cursor-pointer select-none text-right" @click="sort('billed_amount')">
                            <div class="flex items-center justify-end gap-1">Billed
                                <template x-if="sortBy === 'billed_amount'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th class="cursor-pointer select-none text-right" @click="sort('paid_amount')">
                            <div class="flex items-center justify-end gap-1">Paid
                                <template x-if="sortBy === 'paid_amount'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th class="cursor-pointer select-none" @click="sort('payment_type')">
                            <div class="flex items-center gap-1">Type
                                <template x-if="sortBy === 'payment_type'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                            </div>
                        </th>
                        <th>Check #</th>
                        <th>Paid By</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="items.length === 0">
                        <tr><td colspan="11" class="text-center text-v2-text-light py-12">No payments found</td></tr>
                    </template>
                    <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-v2-bg/50 transition-colors cursor-pointer"
                            @click="goToCase(item.case_id)">
                            <td class="whitespace-nowrap text-sm" x-text="formatDate(item.payment_date)"></td>
                            <td>
                                <span class="text-gold font-medium" x-text="item.case_number"></span>
                            </td>
                            <td class="max-w-[140px] truncate text-sm" x-text="item.client_name"></td>
                            <td class="max-w-[180px] truncate text-sm" x-text="item.provider_name || item.linked_provider_name || '-'"></td>
                            <td class="max-w-[140px] truncate text-sm text-v2-text-mid" x-text="item.description || '-'"></td>
                            <td>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-blue-100 text-blue-700': item.expense_category === 'mr_cost',
                                          'bg-purple-100 text-purple-700': item.expense_category === 'litigation',
                                          'bg-gray-100 text-gray-600': item.expense_category === 'other'
                                      }"
                                      x-text="getCategoryLabel(item.expense_category)"></span>
                            </td>
                            <td class="text-right font-mono text-sm" x-text="formatMoney(item.billed_amount)"></td>
                            <td class="text-right font-mono text-sm font-medium text-green-700" x-text="formatMoney(item.paid_amount)"></td>
                            <td class="text-sm" x-text="getPaymentTypeLabel(item.payment_type)"></td>
                            <td class="text-sm text-v2-text-mid" x-text="item.check_number || '-'"></td>
                            <td class="text-sm text-v2-text-mid max-w-[100px] truncate" x-text="item.paid_by_name || '-'"></td>
                        </tr>
                    </template>
                </tbody>
                <!-- Footer totals row -->
                <template x-if="items.length > 0">
                    <tfoot>
                        <tr class="bg-v2-bg font-semibold border-t-2 border-v2-card-border">
                            <td colspan="6" class="text-right text-sm text-v2-text-mid">Page Totals:</td>
                            <td class="text-right font-mono text-sm" x-text="formatMoney(pageTotalBilled)"></td>
                            <td class="text-right font-mono text-sm text-green-700" x-text="formatMoney(pageTotalPaid)"></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </template>
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
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
