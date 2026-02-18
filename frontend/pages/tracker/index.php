<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'MR Tracker';
$currentPage = 'tracker';
ob_start();
?>

<div x-data="trackerPage()" x-init="init()">

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-v2-text">MR Tracker</h1>
            <p class="text-sm text-v2-text-light mt-1">All medical record requests across open cases</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div @click="toggleFilter('')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="activeFilter === '' ? 'ring-2 ring-gold' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-v2-text-light uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-v2-text mt-1" x-text="summary.total ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-v2-bg rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div @click="toggleFilter('overdue')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="activeFilter === 'overdue' ? 'ring-2 ring-red-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-v2-text-light uppercase tracking-wide">Overdue</p>
                    <p class="text-2xl font-bold text-red-600 mt-1" x-text="summary.overdue ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div @click="toggleFilter('followup_due')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="activeFilter === 'followup_due' ? 'ring-2 ring-amber-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-v2-text-light uppercase tracking-wide">Follow-up Due</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1" x-text="summary.followup_due ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
        </div>

        <div @click="toggleFilter('no_request')" class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 cursor-pointer card-hover"
             :class="activeFilter === 'no_request' ? 'ring-2 ring-gray-400' : ''">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-v2-text-light uppercase tracking-wide">Not Started</p>
                    <p class="text-2xl font-bold text-v2-text-light mt-1" x-text="summary.not_started ?? '-'"></p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                       placeholder="Search case #, client, or provider..."
                       class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>

            <!-- Status filter -->
            <select x-model="statusFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                <option value="">All Statuses</option>
                <option value="not_started">Not Started</option>
                <option value="requesting">Requesting</option>
                <option value="follow_up">Follow Up</option>
                <option value="received_partial">Partial</option>
                <option value="received_complete">Complete</option>
                <option value="verified">Verified</option>
            </select>

            <!-- Tier filter -->
            <select x-model="tierFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold focus:border-gold outline-none">
                <option value="">All Tiers</option>
                <option value="admin">Admin Escalation (60d+)</option>
                <option value="manager">Manager Review (42d+)</option>
                <option value="action">Action Needed (30d+)</option>
            </select>

            <!-- Assigned Staff filter -->
            <select x-model="assignedFilter" @change="loadData(1)"
                    class="px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold focus:border-gold outline-none">
                <option value="">All Staff</option>
                <template x-for="staff in staffList" :key="staff.id">
                    <option :value="staff.id" x-text="staff.full_name"></option>
                </template>
            </select>

            <!-- Reset -->
            <button @click="resetFilters()"
                    class="px-3 py-2 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg"
                    x-show="search || statusFilter || activeFilter || tierFilter || assignedFilter">
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
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border"
             x-init="$nextTick(() => { const t = $el.getBoundingClientRect().top; $el.style.maxHeight = (window.innerHeight - t - 16) + 'px'; $el.style.overflowY = 'auto'; })"
             @resize.window.debounce.100ms="const t = $el.getBoundingClientRect().top; $el.style.maxHeight = (window.innerHeight - t - 16) + 'px';">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox"
                                       @change="toggleSelectAll()"
                                       :checked="allSelected"
                                       class="cursor-pointer">
                            </th>
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
                            <th>Escalation</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="items.length === 0">
                            <tr><td colspan="12" class="text-center text-v2-text-light py-12">No records found</td></tr>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <tr class="cursor-pointer"
                                :class="{
                                    'bg-blue-50': selectedItems.includes(item.id),
                                    'tracker-row-overdue': item.is_overdue && !selectedItems.includes(item.id),
                                    'tracker-row-followup': !item.is_overdue && item.is_followup_due && !selectedItems.includes(item.id)
                                }">
                                <td @click.stop>
                                    <input type="checkbox"
                                           :checked="selectedItems.includes(item.id)"
                                           @click="toggleSelect(item.id, $event)"
                                           class="cursor-pointer">
                                </td>
                                <td @click="goToCase(item.case_id, item.id)" class="font-medium text-gold whitespace-nowrap" x-text="item.case_number"></td>
                                <td @click="goToCase(item.case_id, item.id)" class="max-w-[150px] truncate" x-text="item.client_name"></td>
                                <td @click="goToCase(item.case_id, item.id)" class="max-w-[180px] truncate" x-text="item.provider_name"></td>
                                <td @click="goToCase(item.case_id, item.id)">
                                    <span class="status-badge" :class="'status-' + item.overall_status" x-text="getStatusLabel(item.overall_status)"></span>
                                </td>
                                <td @click="goToCase(item.case_id, item.id)" class="whitespace-nowrap">
                                    <template x-if="item.last_request_date">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm" x-text="formatDate(item.last_request_date)"></span>
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-v2-bg text-v2-text-mid" x-text="getMethodLabel(item.last_request_method)"></span>
                                        </div>
                                    </template>
                                    <template x-if="!item.last_request_date">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="text-center" x-text="item.request_count || '-'"></td>
                                <td class="whitespace-nowrap">
                                    <template x-if="item.next_followup_date">
                                        <span :class="item.is_followup_due ? 'text-amber-600 font-medium' : 'text-v2-text-mid'" x-text="formatDate(item.next_followup_date)"></span>
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
                                            'text-v2-text-mid': item.days_until_deadline > 7
                                        }" x-text="formatDate(item.deadline)"></span>
                                    </template>
                                    <template x-if="!item.deadline">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="text-center">
                                    <template x-if="item.days_since_request !== null">
                                        <span :class="item.days_since_request > 30 ? 'text-red-500 font-medium' : item.days_since_request > 14 ? 'text-amber-600' : 'text-v2-text-mid'"
                                              x-text="item.days_since_request + 'd'"></span>
                                    </template>
                                    <template x-if="item.days_since_request === null">
                                        <span class="text-gray-300">-</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="item.escalation_tier !== 'normal'">
                                        <span class="escalation-badge" :class="item.escalation_css" x-text="item.escalation_label"></span>
                                    </template>
                                    <template x-if="item.escalation_tier === 'normal'">
                                        <span class="text-xs text-v2-text-light">&mdash;</span>
                                    </template>
                                </td>
                                <td class="text-sm text-v2-text-mid max-w-[100px] truncate" x-text="item.assigned_name || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
        </div>
    </template>

    <!-- Bulk Action Bar (Fixed Bottom) -->
    <div x-show="selectedItems.length > 0"
         x-transition
         class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gold shadow-lg z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-sm font-semibold text-v2-text">
                    <span x-text="selectedItems.length"></span> item(s) selected
                </span>
                <button @click="clearSelections()"
                        class="text-sm text-v2-text-mid hover:text-v2-text underline">
                    Clear Selection
                </button>
            </div>
            <div class="flex gap-3">
                <button @click="openBulkRequestModal()"
                        class="px-4 py-2 bg-gold text-white rounded-lg hover:bg-gold-dark transition-colors font-medium">
                    Bulk Request
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Request Modal -->
    <div x-show="showBulkRequestModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="closeBulkRequestModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between sticky top-0 bg-white">
                <h2 class="text-xl font-bold text-v2-text">Bulk Follow-Up Request</h2>
                <button @click="closeBulkRequestModal()" class="text-v2-text-light hover:text-v2-text">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <!-- Provider Info Alert -->
                <template x-if="bulkRequestProviderName">
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm">
                            Creating <strong x-text="bulkRequestForm.request_type"></strong> requests for
                            <strong x-text="bulkRequestCases.length"></strong> case(s) from
                            <strong x-text="bulkRequestProviderName" class="text-gold"></strong>
                        </p>
                    </div>
                </template>

                <!-- Error Alert -->
                <template x-if="bulkRequestError">
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600" x-text="bulkRequestError"></p>
                    </div>
                </template>

                <!-- Request Configuration -->
                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Request Date</label>
                        <input type="date" x-model="bulkRequestForm.request_date"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Request Method</label>
                        <select x-model="bulkRequestForm.request_method"
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold outline-none">
                            <option value="email">Email</option>
                            <option value="fax">Fax</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Follow-up Date (applies to all)</label>
                        <input type="date" x-model="bulkRequestForm.followup_date"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Request Type</label>
                        <select x-model="bulkRequestForm.request_type"
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold outline-none">
                            <option value="follow_up">Follow-Up</option>
                            <option value="re_request">Re-Request</option>
                            <option value="initial">Initial</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-v2-text mb-1">Notes (optional, applies to all)</label>
                    <textarea x-model="bulkRequestForm.notes" rows="2"
                              class="w-full px-3 py-2 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold outline-none"
                              placeholder="Additional notes for all requests..."></textarea>
                </div>

                <!-- Case List with Recipient Editing -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-v2-text mb-3">Cases & Recipients</h3>
                    <div class="border border-v2-card-border rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-v2-bg">
                                <tr>
                                    <th class="px-3 py-2 text-left">Case #</th>
                                    <th class="px-3 py-2 text-left">Client</th>
                                    <th class="px-3 py-2 text-left">Recipient (Email/Fax)</th>
                                    <th class="px-3 py-2 w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(caseItem, index) in bulkRequestCases" :key="caseItem.id">
                                    <tr class="border-t border-v2-bg">
                                        <td class="px-3 py-2 font-medium text-gold" x-text="caseItem.case_number"></td>
                                        <td class="px-3 py-2" x-text="caseItem.client_name"></td>
                                        <td class="px-3 py-2">
                                            <input type="text" x-model="caseItem.recipient"
                                                   class="w-full px-2 py-1 border border-v2-card-border rounded text-sm focus:ring-1 focus:ring-gold outline-none"
                                                   placeholder="Auto-detect from provider">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button @click="removeFromBulk(index)"
                                                    class="text-red-600 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="px-6 py-4 border-t border-v2-card-border flex justify-between bg-v2-bg">
                <button @click="closeBulkRequestModal()"
                        class="px-4 py-2 text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-white transition-colors">
                    Cancel
                </button>
                <div class="flex gap-3">
                    <button @click="previewBulkRequests()"
                            class="px-4 py-2 border border-gold text-gold rounded-lg hover:bg-gold hover:text-white transition-colors">
                        Preview All
                    </button>
                    <button @click="createAndSendBulkRequests()"
                            :disabled="bulkRequestCases.length === 0"
                            class="px-4 py-2 bg-gold text-white rounded-lg hover:bg-gold-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Create & Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Preview Modal -->
    <div x-show="showBulkPreviewModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.self="closeBulkPreviewModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                <h2 class="text-xl font-bold text-v2-text">Preview Bulk Requests</h2>
                <button @click="closeBulkPreviewModal()" class="text-v2-text-light hover:text-v2-text">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Preview Info -->
            <div class="border-b border-v2-card-border bg-v2-bg px-6 py-3">
                <p class="text-sm text-v2-text-mid">
                    Combined letter for <span class="font-semibold text-v2-text" x-text="bulkPreviewCaseCount"></span> case(s) to <span class="font-semibold text-v2-text" x-text="bulkPreviewProviderName"></span>
                </p>
            </div>

            <!-- Preview Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div x-html="bulkPreviewHtml"></div>
            </div>

            <!-- Preview Actions -->
            <div class="px-6 py-4 border-t border-v2-card-border flex justify-between bg-v2-bg">
                <button @click="closeBulkPreviewModal()"
                        class="px-4 py-2 text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-white transition-colors">
                    Close
                </button>
                <button @click="confirmAndSendBulk()"
                        class="px-4 py-2 bg-gold text-white rounded-lg hover:bg-gold-dark transition-colors">
                    Send All
                </button>
            </div>
        </div>
    </div>
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

    /* Escalation badges */
    .escalation-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .escalation-admin {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .escalation-manager {
        background-color: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
    }
    .escalation-action {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
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
        tierFilter: '',
        assignedFilter: '',

        // Staff list for assigned filter
        staffList: [],

        // Sorting
        sortBy: 'deadline',
        sortDir: 'asc',

        // Bulk selection
        selectedItems: [],
        allSelected: false,
        lastClickedIndex: null,

        // Bulk request modal
        showBulkRequestModal: false,
        bulkRequestForm: {
            request_date: new Date().toISOString().split('T')[0],
            request_method: 'email',
            request_type: 'follow_up',
            followup_date: '',
            notes: ''
        },
        bulkRequestCases: [],
        bulkRequestProviderName: '',
        bulkRequestError: '',

        // Bulk preview modal
        showBulkPreviewModal: false,
        bulkPreviewHtml: '',
        bulkPreviewProviderName: '',
        bulkPreviewCaseCount: 0,

        async init() {
            this.loadStaff();
            await this.loadData(1);
        },

        async loadStaff() {
            try {
                const res = await api.get('users?active_only=1');
                this.staffList = res.data || [];
            } catch(e) { this.staffList = []; }
        },

        async loadData(page) {
            this.loading = true;
            try {
                let params = `?per_page=99999`;
                if (this.search) params += `&search=${encodeURIComponent(this.search)}`;
                if (this.statusFilter) params += `&status=${this.statusFilter}`;
                if (this.activeFilter) params += `&filter=${this.activeFilter}`;
                if (this.tierFilter) params += `&tier=${this.tierFilter}`;
                if (this.assignedFilter) params += `&assigned_to=${this.assignedFilter}`;
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
            this.tierFilter = '';
            this.assignedFilter = '';
            this.sortBy = 'deadline';
            this.sortDir = 'asc';
            this.loadData(1);
        },

        goToCase(caseId, cpId) {
            let url = '/MRMS/frontend/pages/cases/detail.php?id=' + caseId;
            if (cpId) url += '&cp=' + cpId;
            window.location.href = url;
        },

        getMethodLabel(method) {
            const labels = { email: 'Email', fax: 'Fax', portal: 'Portal', phone: 'Phone', mail: 'Mail' };
            return labels[method] || method || '';
        },

        // Bulk selection methods
        toggleSelect(id, event) {
            const currentIndex = this.items.findIndex(item => item.id === id);

            // Shift-click range selection
            if (event && event.shiftKey && this.lastClickedIndex !== null) {
                event.preventDefault(); // Prevent default checkbox behavior

                const start = Math.min(this.lastClickedIndex, currentIndex);
                const end = Math.max(this.lastClickedIndex, currentIndex);

                // Select all items in range
                for (let i = start; i <= end; i++) {
                    const itemId = this.items[i].id;
                    if (!this.selectedItems.includes(itemId)) {
                        this.selectedItems.push(itemId);
                    }
                }
            } else {
                // Normal toggle
                const index = this.selectedItems.indexOf(id);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                } else {
                    this.selectedItems.push(id);
                }
            }

            this.lastClickedIndex = currentIndex;
            this.updateAllSelected();
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.items.map(item => item.id);
            }
            this.updateAllSelected();
        },

        updateAllSelected() {
            this.allSelected = this.items.length > 0 && this.selectedItems.length === this.items.length;
        },

        clearSelections() {
            this.selectedItems = [];
            this.allSelected = false;
        },

        // Bulk request modal methods
        async openBulkRequestModal() {
            if (this.selectedItems.length === 0) {
                showToast('Please select at least one case', 'error');
                return;
            }

            // Reset form
            this.bulkRequestForm.request_date = new Date().toISOString().split('T')[0];
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            this.bulkRequestForm.followup_date = nextWeek.toISOString().split('T')[0];
            this.bulkRequestForm.notes = '';
            this.bulkRequestError = '';

            // Get selected items details
            const selectedCases = this.items.filter(item => this.selectedItems.includes(item.id));

            // Validate same provider
            const providers = [...new Set(selectedCases.map(c => c.provider_name))];
            if (providers.length > 1) {
                this.bulkRequestError = 'Selected cases must be from the same provider. Found: ' + providers.join(', ');
                this.showBulkRequestModal = true;
                return;
            }

            this.bulkRequestProviderName = providers[0];

            // Populate cases with default recipients
            this.bulkRequestCases = selectedCases.map(c => ({
                id: c.id,
                case_number: c.case_number,
                client_name: c.client_name,
                provider_name: c.provider_name,
                recipient: '' // Will auto-detect from provider
            }));

            this.showBulkRequestModal = true;
        },

        closeBulkRequestModal() {
            this.showBulkRequestModal = false;
            this.bulkRequestCases = [];
            this.bulkRequestProviderName = '';
            this.bulkRequestError = '';
        },

        removeFromBulk(index) {
            this.bulkRequestCases.splice(index, 1);
            if (this.bulkRequestCases.length === 0) {
                this.closeBulkRequestModal();
            }
        },

        async previewBulkRequests() {
            if (this.bulkRequestCases.length === 0) {
                showToast('No cases to preview', 'error');
                return;
            }

            try {
                const payload = {
                    requests: this.bulkRequestCases.map(c => ({
                        case_provider_id: c.id,
                        recipient: c.recipient || undefined
                    })),
                    request_date: this.bulkRequestForm.request_date,
                    request_method: this.bulkRequestForm.request_method,
                    request_type: this.bulkRequestForm.request_type,
                    next_followup_date: this.bulkRequestForm.followup_date,
                    notes: this.bulkRequestForm.notes
                };

                const res = await api.post('requests/preview-bulk', payload);
                this.bulkPreviewHtml = res.data.letter_html || '';
                this.bulkPreviewProviderName = res.data.provider_name || '';
                this.bulkPreviewCaseCount = res.data.case_count || 0;
                this.showBulkPreviewModal = true;
            } catch (e) {
                showToast('Failed to generate preview: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closeBulkPreviewModal() {
            this.showBulkPreviewModal = false;
            this.bulkPreviewHtml = '';
            this.bulkPreviewProviderName = '';
            this.bulkPreviewCaseCount = 0;
        },

        async createAndSendBulkRequests() {
            if (this.bulkRequestCases.length === 0) {
                showToast('No cases to process', 'error');
                return;
            }

            if (!confirm(`Create and send ${this.bulkRequestCases.length} request(s) to ${this.bulkRequestProviderName}?`)) {
                return;
            }

            try {
                const payload = {
                    requests: this.bulkRequestCases.map(c => ({
                        case_provider_id: c.id,
                        recipient: c.recipient || undefined
                    })),
                    request_date: this.bulkRequestForm.request_date,
                    request_method: this.bulkRequestForm.request_method,
                    request_type: this.bulkRequestForm.request_type,
                    next_followup_date: this.bulkRequestForm.followup_date,
                    notes: this.bulkRequestForm.notes,
                    auto_send: true
                };

                const res = await api.post('requests/bulk-create', payload);
                showToast(res.message || 'Bulk requests created and sent successfully', 'success');

                // Close modal and refresh
                this.closeBulkRequestModal();
                this.clearSelections();
                await this.loadData(this.pagination?.page || 1);
            } catch (e) {
                showToast('Failed to create bulk requests: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async confirmAndSendBulk() {
            this.closeBulkPreviewModal();
            await this.createAndSendBulkRequests();
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
