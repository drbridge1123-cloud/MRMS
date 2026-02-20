<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'MBDS Reports';
$currentPage = 'mbds';
$pageScripts = ['/MRMS/frontend/assets/js/pages/mbds.js'];
ob_start();
?>

<div x-data="mbdsListPage()" x-init="init()">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-v2-text">MBDS Reports</h1>
            <p class="text-sm text-v2-text-light mt-1">Medical Bills Summary reports across all cases</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div @click="toggleFilter('')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === '' ? 'ring-2 ring-gold' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Total</p>
            <p class="text-2xl font-bold text-v2-text mt-1" x-text="summary.total ?? '-'"></p>
        </div>
        <div @click="toggleFilter('draft')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === 'draft' ? 'ring-2 ring-gray-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Draft</p>
            <p class="text-2xl font-bold text-gray-500 mt-1" x-text="summary.draft ?? '-'"></p>
        </div>
        <div @click="toggleFilter('completed')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === 'completed' ? 'ring-2 ring-blue-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Completed</p>
            <p class="text-2xl font-bold text-blue-600 mt-1" x-text="summary.completed ?? '-'"></p>
        </div>
        <div @click="toggleFilter('approved')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="statusFilter === 'approved' ? 'ring-2 ring-green-400' : ''">
            <p class="text-xs text-v2-text-light uppercase tracking-wide">Approved</p>
            <p class="text-2xl font-bold text-green-600 mt-1" x-text="summary.approved ?? '-'"></p>
        </div>
    </div>

    <!-- Search & Actions -->
    <div class="flex items-center gap-3 mb-4">
        <div class="w-64">
            <input type="text" x-model="search" @input.debounce.300ms="loadData()"
                   placeholder="Search case # or client..."
                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none bg-white">
        </div>
        <button @click="resetFilters()"
                class="px-3 py-2 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg bg-white"
                x-show="search || statusFilter">
            Reset
        </button>
        <div class="flex-1"></div>
        <div class="flex items-center gap-2 text-sm text-v2-text-light">
            <span x-text="items.length"></span> report(s)
        </div>
        <button @click="exportCSV()"
                class="px-3 py-2 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg bg-white">
            Export CSV
        </button>
    </div>

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex justify-center py-20"><div class="spinner"></div></div>
    </template>

    <!-- Table -->
    <template x-if="!loading">
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="cursor-pointer select-none" @click="sort('case_number')">
                                <div class="flex items-center gap-1">Case #
                                    <template x-if="sortBy === 'case_number'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('client_name')">
                                <div class="flex items-center gap-1">Client
                                    <template x-if="sortBy === 'client_name'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th>DOI</th>
                            <th>Insurance</th>
                            <th class="cursor-pointer select-none text-right" @click="sort('total_charges')">
                                <div class="flex items-center justify-end gap-1">Charges
                                    <template x-if="sortBy === 'total_charges'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none text-right" @click="sort('total_balance')">
                                <div class="flex items-center justify-end gap-1">Balance
                                    <template x-if="sortBy === 'total_balance'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="text-center">Lines</th>
                            <th class="cursor-pointer select-none text-center" @click="sort('status')">
                                <div class="flex items-center justify-center gap-1">Status
                                    <template x-if="sortBy === 'status'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('updated_at')">
                                <div class="flex items-center gap-1">Updated
                                    <template x-if="sortBy === 'updated_at'"><svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="items.length === 0">
                            <tr><td colspan="9" class="text-center text-v2-text-light py-12">No MBDS reports found</td></tr>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-v2-bg/50 transition-colors cursor-pointer"
                                @click="window.location.href='/MRMS/frontend/pages/mbds/edit.php?case_id=' + item.case_id">
                                <td>
                                    <span class="text-gold font-medium" x-text="item.case_number"></span>
                                </td>
                                <td x-text="item.client_name"></td>
                                <td class="whitespace-nowrap" x-text="formatDate(item.doi)"></td>
                                <td>
                                    <div class="text-xs space-y-0.5">
                                        <template x-if="item.pip1_name"><div><span class="text-v2-text-light">PIP:</span> <span x-text="item.pip1_name"></span></div></template>
                                        <template x-if="item.health1_name"><div><span class="text-v2-text-light">Health:</span> <span x-text="item.health1_name"></span></div></template>
                                        <template x-if="!item.pip1_name && !item.health1_name"><span class="text-gray-300">-</span></template>
                                    </div>
                                </td>
                                <td class="text-right font-mono" x-text="'$' + item.total_charges.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="text-right font-mono"
                                    :class="{
                                        'text-red-600': item.total_balance < 0,
                                        'text-amber-600': item.total_balance > 0,
                                        'text-green-600': item.total_balance === 0
                                    }"
                                    x-text="'$' + item.total_balance.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="text-center" x-text="item.line_count"></td>
                                <td class="text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                          :class="{
                                              'bg-gray-100 text-gray-600': item.status === 'draft',
                                              'bg-blue-100 text-blue-700': item.status === 'completed',
                                              'bg-green-100 text-green-700': item.status === 'approved'
                                          }"
                                          x-text="item.status.charAt(0).toUpperCase() + item.status.slice(1)"></span>
                                </td>
                                <td class="whitespace-nowrap text-sm text-v2-text-light" x-text="formatDate(item.updated_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
        </div>
    </template>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
