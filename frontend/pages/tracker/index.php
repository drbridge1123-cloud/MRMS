<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Records Tracker';
$currentPage = 'tracker';
ob_start();
?>

<div x-data="trackerPage()" x-init="init()">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Records Tracker</h1>
            <p class="text-sm text-gray-500 mt-1">All medical record requests across active cases</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div @click="toggleFilter('')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer card-hover"
             :class="activeFilter === '' ? 'ring-2 ring-blue-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" x-text="summary.total ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div @click="toggleFilter('overdue')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer card-hover"
             :class="activeFilter === 'overdue' ? 'ring-2 ring-red-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Overdue</p>
                    <p class="text-2xl font-bold text-red-600 mt-1" x-text="summary.overdue ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div @click="toggleFilter('followup_due')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer card-hover"
             :class="activeFilter === 'followup_due' ? 'ring-2 ring-amber-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Follow-up Due</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1" x-text="summary.followup_due ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
        </div>

        <div @click="toggleFilter('no_request')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer card-hover"
             :class="activeFilter === 'no_request' ? 'ring-2 ring-gray-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Not Started</p>
                    <p class="text-2xl font-bold text-gray-500 mt-1" x-text="summary.not_started ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                       placeholder="Search case #, client, or provider..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <!-- Status filter -->
            <select x-model="statusFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All Statuses</option>
                <option value="not_started">Not Started</option>
                <option value="requesting">Requesting</option>
                <option value="follow_up">Follow Up</option>
                <option value="received_partial">Partial</option>
                <option value="received_complete">Complete</option>
                <option value="verified">Verified</option>
            </select>

            <!-- Reset -->
            <button @click="resetFilters()"
                    class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
                    x-show="search || statusFilter || activeFilter">
                Reset
            </button>
        </div>
    </div>

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex justify-center py-20">
            <div class="spinner"></div>
        </div>
    </template>

    <!-- Table -->
    <template x-if="!loading">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="cursor-pointer select-none" @click="sort('case_number')">
                                <div class="flex items-center gap-1">
                                    Case #
                                    <template x-if="sortBy === 'case_number'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('client_name')">
                                <div class="flex items-center gap-1">
                                    Client
                                    <template x-if="sortBy === 'client_name'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('provider_name')">
                                <div class="flex items-center gap-1">
                                    Provider
                                    <template x-if="sortBy === 'provider_name'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('overall_status')">
                                <div class="flex items-center gap-1">
                                    Status
                                    <template x-if="sortBy === 'overall_status'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('last_request_date')">
                                <div class="flex items-center gap-1">
                                    Last Request
                                    <template x-if="sortBy === 'last_request_date'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('request_count')">
                                <div class="flex items-center gap-1">
                                    #
                                    <template x-if="sortBy === 'request_count'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('next_followup_date')">
                                <div class="flex items-center gap-1">
                                    Follow-up Due
                                    <template x-if="sortBy === 'next_followup_date'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('deadline')">
                                <div class="flex items-center gap-1">
                                    Deadline
                                    <template x-if="sortBy === 'deadline'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th class="cursor-pointer select-none" @click="sort('days_since_request')">
                                <div class="flex items-center gap-1">
                                    Days Since
                                    <template x-if="sortBy === 'days_since_request'">
                                        <svg class="w-3 h-3" :class="sortDir === 'asc' ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                    </template>
                                </div>
                            </th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="items.length === 0">
                            <tr><td colspan="10" class="text-center text-gray-400 py-12">No records found</td></tr>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <tr @click="goToCase(item.case_id)" class="cursor-pointer"
                                :class="{
                                    'tracker-row-overdue': item.is_overdue,
                                    'tracker-row-followup': !item.is_overdue && item.is_followup_due
                                }">
                                <td class="font-medium text-blue-600 whitespace-nowrap" x-text="item.case_number"></td>
                                <td class="max-w-[150px] truncate" x-text="item.client_name"></td>
                                <td class="max-w-[180px] truncate" x-text="item.provider_name"></td>
                                <td>
                                    <span class="status-badge" :class="'status-' + item.overall_status" x-text="getStatusLabel(item.overall_status)"></span>
                                </td>
                                <td class="whitespace-nowrap">
                                    <template x-if="item.last_request_date">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm" x-text="formatDate(item.last_request_date)"></span>
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600" x-text="getMethodLabel(item.last_request_method)"></span>
                                        </div>
                                    </template>
                                    <template x-if="!item.last_request_date">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="text-center" x-text="item.request_count || '-'"></td>
                                <td class="whitespace-nowrap">
                                    <template x-if="item.next_followup_date">
                                        <span :class="item.is_followup_due ? 'text-amber-600 font-medium' : 'text-gray-600'" x-text="formatDate(item.next_followup_date)"></span>
                                    </template>
                                    <template x-if="!item.next_followup_date">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="whitespace-nowrap">
                                    <template x-if="item.deadline">
                                        <span :class="{
                                            'text-red-600 font-semibold': item.days_until_deadline < 0,
                                            'text-amber-600 font-medium': item.days_until_deadline >= 0 && item.days_until_deadline <= 7,
                                            'text-gray-600': item.days_until_deadline > 7
                                        }" x-text="formatDate(item.deadline)"></span>
                                    </template>
                                    <template x-if="!item.deadline">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="text-center">
                                    <template x-if="item.days_since_request !== null">
                                        <span :class="item.days_since_request > 30 ? 'text-red-500 font-medium' : item.days_since_request > 14 ? 'text-amber-600' : 'text-gray-600'"
                                              x-text="item.days_since_request + 'd'"></span>
                                    </template>
                                    <template x-if="item.days_since_request === null">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="text-sm text-gray-600 max-w-[100px] truncate" x-text="item.assigned_name || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <template x-if="pagination && pagination.total_pages > 1">
                <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-sm text-gray-500"
                          x-text="'Showing ' + ((pagination.page - 1) * pagination.per_page + 1) + '-' + Math.min(pagination.page * pagination.per_page, pagination.total) + ' of ' + pagination.total"></span>
                    <div class="flex gap-2">
                        <button @click="loadData(pagination.page - 1)" :disabled="pagination.page <= 1"
                                class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            Prev
                        </button>
                        <button @click="loadData(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages"
                                class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

<style>
    .tracker-row-overdue {
        background-color: #fef2f2;
        border-left: 4px solid #ef4444;
    }
    .tracker-row-overdue:hover {
        background-color: #fee2e2 !important;
    }
    .tracker-row-followup {
        background-color: #fffbeb;
        border-left: 4px solid #f59e0b;
    }
    .tracker-row-followup:hover {
        background-color: #fef3c7 !important;
    }
</style>

<script>
function trackerPage() {
    return {
        items: [],
        pagination: null,
        summary: { total: 0, overdue: 0, followup_due: 0, not_started: 0 },
        loading: true,

        // Filters
        search: '',
        statusFilter: '',
        activeFilter: '',

        // Sorting
        sortBy: 'deadline',
        sortDir: 'asc',

        async init() {
            await this.loadData(1);
        },

        async loadData(page) {
            this.loading = true;
            try {
                let params = `?page=${page}&per_page=50`;
                if (this.search) params += `&search=${encodeURIComponent(this.search)}`;
                if (this.statusFilter) params += `&status=${this.statusFilter}`;
                if (this.activeFilter) params += `&filter=${this.activeFilter}`;
                params += `&sort_by=${this.sortBy}&sort_dir=${this.sortDir}`;

                const res = await api.get('tracker/list' + params);
                this.items = res.data || [];
                this.pagination = res.pagination || null;
                if (res.summary) this.summary = res.summary;
            } catch (e) {
                showToast('Failed to load tracker data', 'error');
            }
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

        toggleFilter(filter) {
            this.activeFilter = this.activeFilter === filter ? '' : filter;
            this.loadData(1);
        },

        resetFilters() {
            this.search = '';
            this.statusFilter = '';
            this.activeFilter = '';
            this.sortBy = 'deadline';
            this.sortDir = 'asc';
            this.loadData(1);
        },

        goToCase(caseId) {
            window.location.href = '/MRMS/frontend/pages/cases/detail.php?id=' + caseId;
        },

        getMethodLabel(method) {
            const labels = { email: 'Email', fax: 'Fax', portal: 'Portal', phone: 'Phone', mail: 'Mail' };
            return labels[method] || method || '';
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
