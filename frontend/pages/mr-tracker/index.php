<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Tracker';
$currentPage = 'tracker';
$pageScripts = [
    '/MRMS/frontend/components/document-selector.js',
    '/MRMS/frontend/assets/js/pages/mr-tracker.js',
    '/MRMS/frontend/assets/js/pages/health-tracker.js'
];
ob_start();
?>

<div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') === 'health' ? 'health' : 'mr' }">

    <!-- Page Header + Tabs -->
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-6">
            <h1 class="text-xl font-bold text-v2-text">Tracker</h1>
            <div class="flex gap-0 border-b border-v2-card-border">
                <button @click="activeTab = 'mr'"
                        class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                        :class="activeTab === 'mr'
                            ? 'text-gold border-gold'
                            : 'text-v2-text-light border-transparent hover:text-v2-text-mid'">
                    MR Tracker
                </button>
                <button @click="activeTab = 'health'"
                        class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                        :class="activeTab === 'health'
                            ? 'text-gold border-gold'
                            : 'text-v2-text-light border-transparent hover:text-v2-text-mid'">
                    Health Tracker
                </button>
            </div>
        </div>
        <!-- Health Tracker action buttons (only visible on health tab) -->
        <div class="flex gap-2" x-show="activeTab === 'health'" x-cloak>
            <button @click="$dispatch('hl-import')"
                    class="px-3 py-1.5 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg text-v2-text-mid">
                Import CSV
            </button>
            <button @click="$dispatch('hl-add')"
                    class="px-3 py-1.5 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90">
                + Add Item
            </button>
        </div>
    </div>

    <!-- ===================== MR TRACKER TAB ===================== -->
    <div x-show="activeTab === 'mr'" x-data="trackerPage()" x-init="init()">

        <!-- Case Filter Banner -->
        <div x-show="caseIdFilter" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 mb-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-blue-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span class="font-medium">Filtered by case</span>
                <span class="text-blue-600" x-text="items.length ? '#' + items[0].case_number : ''"></span>
                <span class="text-blue-500" x-text="'(' + items.length + ' provider' + (items.length !== 1 ? 's' : '') + ')'"></span>
            </div>
            <button @click="resetFilters()" class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">Show All</button>
        </div>

        <!-- New Assignments Panel -->
        <template x-if="pendingAssignments.length > 0">
            <div class="bg-amber-50 rounded-lg border border-amber-300 mb-3 overflow-hidden">
                <div class="px-4 py-2.5 bg-amber-100 border-b border-amber-300 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-sm font-semibold text-amber-800">New Assignments</span>
                        <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="pendingAssignments.length"></span>
                    </div>
                </div>
                <div class="divide-y divide-amber-200">
                    <template x-for="pa in pendingAssignments" :key="pa.id">
                        <div class="px-4 py-2.5 flex items-center justify-between hover:bg-amber-100/50">
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-v2-text" x-text="pa.provider_name"></span>
                                        <span class="text-xs text-v2-text-light">|</span>
                                        <span class="text-xs text-v2-text-mid" x-text="'Case #' + pa.case_number"></span>
                                        <span class="text-xs text-v2-text-light">|</span>
                                        <span class="text-xs text-v2-text-mid" x-text="pa.client_name"></span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-0.5">
                                        <span class="text-[11px] text-v2-text-light" x-text="'Deadline: ' + formatDate(pa.deadline)"></span>
                                        <template x-if="pa.activated_by_name">
                                            <span class="text-[11px] text-v2-text-light" x-text="'From: ' + pa.activated_by_name"></span>
                                        </template>
                                        <template x-if="pa.request_mr || pa.request_bill || pa.request_chart || pa.request_img || pa.request_op">
                                            <span class="text-[11px] text-v2-text-light">
                                                Records:
                                                <template x-if="pa.request_mr"><span class="font-medium">MR </span></template>
                                                <template x-if="pa.request_bill"><span class="font-medium">Bill </span></template>
                                                <template x-if="pa.request_chart"><span class="font-medium">Chart </span></template>
                                                <template x-if="pa.request_img"><span class="font-medium">Img </span></template>
                                                <template x-if="pa.request_op"><span class="font-medium">OP </span></template>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button @click="acceptAssignment(pa.id)"
                                        class="px-3 py-1 text-xs font-semibold text-white bg-emerald-500 rounded-md hover:bg-emerald-600 transition-colors">
                                    Accept
                                </button>
                                <button @click="declineAssignment(pa.id)"
                                        class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-md hover:bg-red-600 transition-colors">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-3 mb-3">
            <div @click="toggleFilter('')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 cursor-pointer card-hover flex items-center justify-between"
                 :class="activeFilter === '' ? 'ring-2 ring-gold' : ''">
                <div>
                    <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Total</p>
                    <p class="text-lg font-bold text-v2-text" x-text="summary.total ?? '-'"></p>
                </div>
                <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div @click="toggleFilter('overdue')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 cursor-pointer card-hover flex items-center justify-between"
                 :class="activeFilter === 'overdue' ? 'ring-2 ring-red-400' : ''">
                <div>
                    <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Overdue</p>
                    <p class="text-lg font-bold text-red-600" x-text="summary.overdue ?? '-'"></p>
                </div>
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div @click="toggleFilter('followup_due')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 cursor-pointer card-hover flex items-center justify-between"
                 :class="activeFilter === 'followup_due' ? 'ring-2 ring-amber-400' : ''">
                <div>
                    <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Follow-up Due</p>
                    <p class="text-lg font-bold text-amber-600" x-text="summary.followup_due ?? '-'"></p>
                </div>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div @click="toggleFilter('no_request')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 cursor-pointer card-hover flex items-center justify-between"
                 :class="activeFilter === 'no_request' ? 'ring-2 ring-gray-400' : ''">
                <div>
                    <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Not Started</p>
                    <p class="text-lg font-bold text-v2-text-light" x-text="summary.not_started ?? '-'"></p>
                </div>
                <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 mb-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                           placeholder="Search case #, client, or provider..."
                           class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <select x-model="statusFilter" @change="loadData(1)"
                        class="px-2 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    <option value="">All Statuses</option>
                    <option value="not_started">Not Started</option>
                    <option value="requesting">Requesting</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="action_needed">Action Needed</option>
                    <option value="received_partial">Partial</option>
                    <option value="on_hold">On Hold</option>
                    <option value="received_complete">Complete</option>
                    <option value="verified">Verified</option>
                </select>
                <select x-model="tierFilter" @change="loadData(1)"
                        class="px-2 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    <option value="">All Tiers</option>
                    <option value="admin">Admin Escalation (deadline+14d)</option>
                    <option value="action">Action Needed (past deadline)</option>
                </select>
                <select x-model="assignedFilter" @change="loadData(1)"
                        class="px-2 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    <option value="">All Staff</option>
                    <template x-for="staff in staffList" :key="staff.id">
                        <option :value="staff.id" x-text="staff.full_name"></option>
                    </template>
                </select>
                <button @click="resetFilters()"
                        class="px-2 py-1.5 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg"
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
                 x-init="initScrollContainer($el)">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="items.length === 0">
                            <tr><td colspan="13" class="text-center text-v2-text-light py-12">No records found</td></tr>
                        </tbody>
                            <template x-for="item in items" :key="item.id">
                                <tbody>
                                <tr class="cursor-pointer"
                                    :class="{
                                        'bg-blue-50': selectedItems.includes(item.id),
                                        'ring-2 ring-gold bg-white': expandedId === item.id,
                                        'tracker-row-overdue': item.is_overdue && !selectedItems.includes(item.id) && expandedId !== item.id,
                                        'tracker-row-followup': !item.is_overdue && item.is_followup_due && !selectedItems.includes(item.id) && expandedId !== item.id
                                    }"
                                    @click="toggleExpand(item.id)">
                                    <td @click.stop>
                                        <input type="checkbox"
                                               :checked="selectedItems.includes(item.id)"
                                               @click="toggleSelect(item.id, $event)"
                                               class="cursor-pointer">
                                    </td>
                                    <td class="font-medium text-gold whitespace-nowrap" x-text="item.case_number"></td>
                                    <td class="max-w-[150px] truncate" x-text="item.client_name"></td>
                                    <td class="max-w-[180px] truncate" x-text="item.provider_name"></td>
                                    <td>
                                        <span class="status-badge" :class="'status-' + item.overall_status" x-text="getStatusLabel(item.overall_status)"></span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <template x-if="item.last_request_date">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm" x-text="formatDate(item.last_request_date)"></span>
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-v2-bg text-v2-text-mid" x-text="getMethodLabel(item.last_request_method)"></span>
                                            </div>
                                        </template>
                                        <template x-if="!item.last_request_date"><span class="text-gray-300">-</span></template>
                                    </td>
                                    <td class="text-center" x-text="item.request_count || '-'"></td>
                                    <td class="whitespace-nowrap">
                                        <template x-if="item.next_followup_date">
                                            <span :class="item.is_followup_due ? 'text-amber-600 font-medium' : 'text-v2-text-mid'" x-text="formatDate(item.next_followup_date)"></span>
                                        </template>
                                        <template x-if="!item.next_followup_date"><span class="text-gray-300">-</span></template>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <template x-if="item.deadline">
                                            <span :class="{
                                                'text-red-600 font-semibold': item.days_until_deadline < 0,
                                                'text-amber-600 font-medium': item.days_until_deadline >= 0 && item.days_until_deadline <= 7,
                                                'text-v2-text-mid': item.days_until_deadline > 7
                                            }" x-text="formatDate(item.deadline)"></span>
                                        </template>
                                        <template x-if="!item.deadline"><span class="text-gray-300">-</span></template>
                                    </td>
                                    <td class="text-center">
                                        <template x-if="item.days_since_request !== null">
                                            <span :class="item.days_since_request > 30 ? 'text-red-500 font-medium' : item.days_since_request > 14 ? 'text-amber-600' : 'text-v2-text-mid'"
                                                  x-text="item.days_since_request + 'd'"></span>
                                        </template>
                                        <template x-if="item.days_since_request === null"><span class="text-gray-300">-</span></template>
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
                                    <td @click.stop class="whitespace-nowrap">
                                        <div class="flex gap-1 items-center">
                                            <button @click="openRequestModal(item)" class="px-2 py-1 text-xs bg-gold/10 text-gold rounded hover:bg-gold/20 font-medium" title="New Request">Request</button>
                                            <button @click="openReceiptModal(item)" class="p-1 text-v2-text-light hover:text-green-600 rounded" title="Log Receipt"
                                                    x-show="item.overall_status !== 'received_complete' && item.overall_status !== 'verified'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            <button @click="goToCase(item.case_id, item.id)" class="p-1 text-v2-text-light hover:text-blue-600 rounded" title="Open Case">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Expanded Request History -->
                                <tr x-show="expandedId === item.id">
                                    <td colspan="13" class="!p-0 !border-t-0">
                                        <div class="bg-stone-50 border-t border-b-2 border-gold/30 px-6 py-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-3">
                                                    <h4 class="text-sm font-bold text-v2-text">Request History</h4>
                                                    <span class="text-xs text-v2-text-light" x-text="requestHistory.length + ' request(s)'"></span>
                                                </div>
                                                <button @click="openRequestModal(item)" class="px-3 py-1.5 text-xs bg-gold text-white rounded-lg hover:bg-gold-dark font-semibold flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    New Request
                                                </button>
                                            </div>
                                            <template x-if="requestHistory.length === 0">
                                                <p class="text-center text-v2-text-light py-4 text-xs">No requests yet. Click "New Request" to create one.</p>
                                            </template>
                                            <template x-if="requestHistory.length > 0">
                                                <div class="space-y-1.5">
                                                    <template x-for="req in requestHistory" :key="req.id">
                                                        <div class="flex items-center justify-between bg-white rounded-lg border border-v2-card-border px-4 py-2.5">
                                                            <div class="flex items-center gap-4 text-sm">
                                                                <span class="font-medium" x-text="formatDate(req.request_date)"></span>
                                                                <span class="text-xs px-1.5 py-0.5 rounded bg-v2-bg text-v2-text-mid" x-text="getMethodLabel(req.request_method)"></span>
                                                                <span class="text-xs text-v2-text-light capitalize" x-text="(req.request_type || '').replace('_', ' ')"></span>
                                                                <span class="text-xs px-1.5 py-0.5 rounded font-medium"
                                                                      :class="{
                                                                          'bg-gray-100 text-gray-600': req.send_status === 'draft',
                                                                          'bg-green-100 text-green-700': req.send_status === 'sent',
                                                                          'bg-red-100 text-red-600': req.send_status === 'failed',
                                                                          'bg-blue-100 text-blue-600': req.send_status === 'sending'
                                                                      }"
                                                                      x-text="getSendStatusLabel(req.send_status)"></span>
                                                                <template x-if="req.sent_to">
                                                                    <span class="text-xs text-v2-text-light" x-text="'→ ' + req.sent_to"></span>
                                                                </template>
                                                            </div>
                                                            <div class="flex items-center gap-1" @click.stop>
                                                                <template x-if="(req.request_method === 'email' || req.request_method === 'fax')">
                                                                    <button @click="openPreviewModal(req)"
                                                                            class="px-2 py-1 text-xs rounded font-medium"
                                                                            :class="req.send_status === 'draft' || req.send_status === 'failed' ? 'bg-gold/10 text-gold hover:bg-gold/20' : 'bg-v2-bg text-v2-text-mid hover:bg-v2-bg/80'"
                                                                            x-text="req.send_status === 'draft' ? 'Preview & Send' : req.send_status === 'failed' ? 'Retry' : 'Preview'">
                                                                    </button>
                                                                </template>
                                                                <button @click="deleteRequest(req)" class="p-1 text-v2-text-light hover:text-red-500 rounded" title="Delete">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </template>
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

        <!-- ===== MRT Modal Styles ===== -->
        <style>
            .mrt-backdrop {
                background: rgba(0,0,0,.45);
            }
            .mrt-modal {
                border-radius: 12px;
                box-shadow: 0 24px 64px rgba(0,0,0,.24);
                overflow: hidden;
                background: #fff;
            }
            .mrt-header {
                background: #0F1B2D;
                padding: 18px 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .mrt-title {
                font-size: 15px;
                font-weight: 700;
                color: #fff;
            }
            .mrt-subtitle {
                font-size: 12px;
                font-weight: 500;
                color: var(--gold, #C9A84C);
                margin-top: 2px;
            }
            .mrt-close {
                color: rgba(255,255,255,.35);
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color .2s;
            }
            .mrt-close:hover {
                color: rgba(255,255,255,.75);
            }
            .mrt-body {
                padding: 24px;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .mrt-footer {
                padding: 14px 24px;
                border-top: 1px solid var(--border, #d0cdc5);
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
            .mrt-label {
                font-size: 9.5px;
                font-weight: 700;
                color: var(--muted, #8a8a82);
                text-transform: uppercase;
                letter-spacing: .08em;
                margin-bottom: 5px;
                display: block;
            }
            .mrt-label .mrt-req {
                color: var(--gold, #C9A84C);
            }
            .mrt-input,
            .mrt-select,
            .mrt-textarea {
                width: 100%;
                background: #fafafa;
                border: 1.5px solid var(--border, #d0cdc5);
                border-radius: 7px;
                padding: 9px 12px;
                font-size: 13px;
                outline: none;
                transition: border-color .2s, background .2s, box-shadow .2s;
            }
            .mrt-input:focus,
            .mrt-select:focus,
            .mrt-textarea:focus {
                border-color: var(--gold, #C9A84C);
                background: #fff;
                box-shadow: 0 0 0 3px rgba(201,168,76,.1);
            }
            .mrt-select {
                appearance: none;
                padding-right: 30px;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8a82' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 10px center;
            }
            .mrt-textarea {
                resize: vertical;
                min-height: 70px;
                line-height: 1.5;
            }
            .mrt-row {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            .mrt-divider {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .mrt-divider::before,
            .mrt-divider::after {
                content: '';
                flex: 1;
                height: 1px;
                background: var(--border, #d0cdc5);
            }
            .mrt-divider span {
                font-size: 9px;
                font-weight: 700;
                color: var(--muted, #8a8a82);
                text-transform: uppercase;
                letter-spacing: .1em;
            }
            .mrt-btn-cancel {
                background: #fff;
                border: 1.5px solid var(--border, #d0cdc5);
                border-radius: 7px;
                padding: 9px 18px;
                font-size: 13px;
                font-weight: 500;
                color: #5A6B82;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: background .15s;
            }
            .mrt-btn-cancel:hover {
                background: #f7f7f5;
            }
            .mrt-btn-primary {
                background: var(--gold, #C9A84C);
                color: #fff;
                border: none;
                border-radius: 7px;
                padding: 9px 22px;
                font-size: 13px;
                font-weight: 700;
                box-shadow: 0 2px 8px rgba(201,168,76,.35);
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: opacity .15s;
            }
            .mrt-btn-primary:hover {
                opacity: .92;
            }
            .mrt-btn-primary:disabled {
                opacity: .5;
                cursor: not-allowed;
            }
            .mrt-btn-send {
                background: #1a9e6a;
                color: #fff;
                border: none;
                border-radius: 7px;
                padding: 9px 22px;
                font-size: 13px;
                font-weight: 700;
                box-shadow: 0 2px 8px rgba(26,158,106,.3);
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: opacity .15s;
            }
            .mrt-btn-send:hover {
                opacity: .92;
            }
            .mrt-btn-send:disabled {
                opacity: .5;
                cursor: not-allowed;
            }
        </style>

        <!-- Bulk Request Modal -->
        <div x-show="showBulkRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display:none;" @keydown.escape.window="closeBulkRequestModal()">
            <div class="fixed inset-0 mrt-backdrop" @click="closeBulkRequestModal()"></div>
            <div class="mrt-modal relative w-full max-w-4xl z-10 max-h-[90vh] flex flex-col" @click.stop>
                <div class="mrt-header">
                    <div>
                        <div class="mrt-title">Bulk Follow-Up Request</div>
                    </div>
                    <button type="button" class="mrt-close" @click="closeBulkRequestModal()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mrt-body" style="overflow-y:auto;">
                    <template x-if="bulkRequestProviderName">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm">
                                Creating <strong x-text="bulkRequestForm.request_type"></strong> requests for
                                <strong x-text="bulkRequestCases.length"></strong> case(s) from
                                <strong x-text="bulkRequestProviderName" class="text-gold"></strong>
                            </p>
                        </div>
                    </template>

                    <template x-if="bulkRequestError">
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-600" x-text="bulkRequestError"></p>
                        </div>
                    </template>

                    <div class="mb-6 grid grid-cols-2 gap-4">
                        <div>
                            <label class="mrt-label">Request Date</label>
                            <input type="date" x-model="bulkRequestForm.request_date"
                                   class="mrt-input">
                        </div>
                        <div>
                            <label class="mrt-label">Request Method</label>
                            <select x-model="bulkRequestForm.request_method"
                                    class="mrt-select">
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                            </select>
                        </div>
                        <div>
                            <label class="mrt-label">Follow-up Date (applies to all)</label>
                            <input type="date" x-model="bulkRequestForm.followup_date"
                                   class="mrt-input">
                        </div>
                        <div>
                            <label class="mrt-label">Request Type</label>
                            <select x-model="bulkRequestForm.request_type"
                                    class="mrt-select">
                                <option value="follow_up">Follow-Up</option>
                                <option value="re_request">Re-Request</option>
                                <option value="initial">Initial</option>
                            </select>
                        </div>
                        <div class="col-span-2" x-show="bulkTemplates.length > 0">
                            <label class="mrt-label">Letter Template</label>
                            <select x-model="bulkRequestForm.template_id"
                                    class="mrt-select">
                                <option value="">Default (built-in)</option>
                                <template x-for="t in bulkTemplates" :key="t.id">
                                    <option :value="t.id" x-text="t.name + (t.is_default ? ' (Default)' : '')"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="mrt-label">Notes (optional, applies to all)</label>
                        <textarea x-model="bulkRequestForm.notes" rows="2"
                                  class="mrt-textarea"
                                  placeholder="Additional notes for all requests..."></textarea>
                    </div>

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
                                                       class="mrt-input"
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

                <div class="mrt-footer" style="justify-content:space-between;">
                    <button @click="closeBulkRequestModal()" class="mrt-btn-cancel">Cancel</button>
                    <div class="flex gap-3">
                        <button @click="previewBulkRequests()"
                                class="mrt-btn-cancel" style="border-color:var(--gold);color:var(--gold);">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview All
                        </button>
                        <button @click="createAndSendBulkRequests()"
                                :disabled="bulkRequestCases.length === 0"
                                class="mrt-btn-primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Create & Send
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Preview Modal -->
        <div x-show="showBulkPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display:none;" @keydown.escape.window="closeBulkPreviewModal()">
            <div class="fixed inset-0 mrt-backdrop" @click="closeBulkPreviewModal()"></div>
            <div class="mrt-modal relative w-full max-w-5xl z-10 max-h-[90vh] flex flex-col" @click.stop>
                <div class="mrt-header">
                    <div>
                        <div class="mrt-title">Preview Bulk Requests</div>
                        <div class="mrt-subtitle">
                            Combined letter for <span x-text="bulkPreviewCaseCount"></span> case(s) to <span x-text="bulkPreviewProviderName"></span>
                        </div>
                    </div>
                    <button type="button" class="mrt-close" @click="closeBulkPreviewModal()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mrt-body" style="flex:1;overflow-y:auto;">
                    <div x-html="bulkPreviewHtml"></div>
                </div>

                <div class="mrt-footer" style="justify-content:space-between;">
                    <button @click="closeBulkPreviewModal()" class="mrt-btn-cancel">Close</button>
                    <button @click="confirmAndSendBulk()" class="mrt-btn-send">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Send All
                    </button>
                </div>
            </div>
        </div>

        <!-- MR Request Modal -->
        <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none;" @keydown.escape.window="showRequestModal = false">
            <div class="fixed inset-0 mrt-backdrop" @click="showRequestModal = false"></div>
            <div class="mrt-modal relative w-full max-w-lg z-10" @click.stop>
                <div class="mrt-header">
                    <div>
                        <div class="mrt-title">New Request</div>
                        <div class="mrt-subtitle" x-text="reqForm._carrierLabel"></div>
                    </div>
                    <button type="button" class="mrt-close" @click="showRequestModal = false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mrt-body">
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Request Date <span class="mrt-req">*</span></label>
                            <input type="date" x-model="reqForm.request_date" class="mrt-input">
                        </div>
                        <div>
                            <label class="mrt-label">Method <span class="mrt-req">*</span></label>
                            <select x-model="reqForm.request_method" @change="updateRecipient()" class="mrt-select">
                                <option value="">Select...</option>
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                                <option value="portal">Portal</option>
                                <option value="phone">Phone</option>
                                <option value="mail">Mail</option>
                            </select>
                        </div>
                    </div>
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Type</label>
                            <select x-model="reqForm.request_type" class="mrt-select">
                                <option value="initial">Initial</option>
                                <option value="follow_up">Follow Up</option>
                                <option value="re_request">Re-Request</option>
                            </select>
                        </div>
                        <div>
                            <label class="mrt-label">Send To</label>
                            <input type="text" x-model="reqForm.sent_to" class="mrt-input" placeholder="Email or fax #">
                        </div>
                    </div>
                    <div>
                        <label class="mrt-label">Template</label>
                        <select x-model="reqForm.template_id" class="mrt-select">
                            <option value="">Default (no template)</option>
                            <template x-for="t in hlTemplates" :key="t.id">
                                <option :value="t.id" x-text="t.name + (t.is_default ? ' (Default)' : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mrt-label">Notes</label>
                        <textarea x-model="reqForm.notes" rows="2" class="mrt-textarea"></textarea>
                    </div>
                    <!-- Document Attachments (email/fax only) -->
                    <div x-show="reqForm.request_method === 'email' || reqForm.request_method === 'fax'"
                         x-data="{
                            docs: [],
                            selectedIds: [],
                            docLoading: false,
                            showDocs: false,
                            uploading: false,
                            async loadDocs() {
                                if (!reqForm._caseId) return;
                                this.docLoading = true;
                                try {
                                    const res = await api.get('documents?case_id=' + reqForm._caseId);
                                    this.docs = res.success ? (res.data.documents || []) : [];
                                } catch(e) { this.docs = []; }
                                this.docLoading = false;
                            },
                            toggleDoc(id) {
                                const i = this.selectedIds.indexOf(id);
                                if (i > -1) this.selectedIds.splice(i, 1);
                                else this.selectedIds.push(id);
                                reqForm.document_ids = [...this.selectedIds];
                            },
                            selectAllDocs() {
                                this.selectedIds = this.docs.map(d => d.id);
                                reqForm.document_ids = [...this.selectedIds];
                            },
                            clearDocs() {
                                this.selectedIds = [];
                                reqForm.document_ids = [];
                            },
                            async quickUpload(event) {
                                const file = event.target.files[0];
                                if (!file) return;
                                this.uploading = true;
                                try {
                                    const formData = new FormData();
                                    formData.append('file', file);
                                    formData.append('case_id', reqForm._caseId);
                                    formData.append('document_type', 'other');
                                    const res = await api.upload('documents/upload', formData);
                                    if (res.success && res.data) {
                                        await this.loadDocs();
                                        this.selectedIds.push(res.data.id);
                                        reqForm.document_ids = [...this.selectedIds];
                                        showToast('File uploaded & selected');
                                    }
                                } catch(e) { showToast(e.data?.message || 'Upload failed', 'error'); }
                                this.uploading = false;
                                event.target.value = '';
                            }
                         }"
                         x-effect="if (showRequestModal && reqForm._caseId && docs.length === 0 && !docLoading) loadDocs()">
                        <button type="button" @click="showDocs = !showDocs" class="w-full flex items-center justify-between py-2 text-left">
                            <div class="flex items-center gap-2">
                                <label class="mrt-label" style="margin:0; cursor:pointer;">Attachments</label>
                                <span x-show="selectedIds.length > 0" class="text-[10px] font-bold bg-gold/20 text-gold px-1.5 py-0.5 rounded-full" x-text="selectedIds.length + ' selected'"></span>
                            </div>
                            <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="showDocs ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="showDocs" x-collapse>
                            <!-- Toolbar: Upload + Select All / Clear -->
                            <div class="flex items-center justify-between mb-2">
                                <label class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gold bg-gold/10 rounded-lg cursor-pointer hover:bg-gold/20 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span x-text="uploading ? 'Uploading...' : 'Upload'"></span>
                                    <input type="file" class="hidden" @change="quickUpload($event)" :disabled="uploading"
                                           accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.tiff,.xls,.xlsx">
                                </label>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <button type="button" @click="selectAllDocs()" :disabled="docs.length === 0"
                                            class="font-bold text-gold hover:opacity-70 disabled:opacity-30">Select All</button>
                                    <span class="text-v2-text-light">|</span>
                                    <button type="button" @click="clearDocs()" :disabled="selectedIds.length === 0"
                                            class="font-bold text-gold hover:opacity-70 disabled:opacity-30">Clear</button>
                                </div>
                            </div>
                            <div x-show="docLoading" class="text-center py-3 text-xs text-v2-text-light">Loading documents...</div>
                            <div x-show="!docLoading && docs.length === 0" class="text-center py-3 text-xs text-v2-text-light">
                                No documents yet. Upload a file above.
                            </div>
                            <div x-show="!docLoading && docs.length > 0" class="space-y-1 max-h-[160px] overflow-y-auto">
                                <template x-for="doc in docs" :key="doc.id">
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors text-sm"
                                           :class="selectedIds.includes(doc.id) ? 'border-gold bg-gold/5' : 'border-v2-card-border hover:bg-v2-bg'">
                                        <input type="checkbox" :checked="selectedIds.includes(doc.id)" @change="toggleDoc(doc.id)" class="rounded">
                                        <div class="flex-1 min-w-0">
                                            <div class="truncate text-v2-text" x-text="doc.original_file_name"></div>
                                            <div class="text-[11px] text-v2-text-light" x-text="doc.file_size_formatted"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                            <div class="mt-2 text-[10px] text-v2-text-light">
                                For PDF field overlay, use <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + reqForm._caseId" class="text-gold underline" target="_blank">Case Detail</a> &rarr; Documents
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mrt-footer">
                    <button @click="showRequestModal = false" class="mrt-btn-cancel">Cancel</button>
                    <button @click="submitRequest()" :disabled="saving" class="mrt-btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span x-text="saving ? 'Creating...' : 'Create Request'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Receipt Modal -->
        <div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none;" @keydown.escape.window="showReceiptModal = false">
            <div class="fixed inset-0 mrt-backdrop" @click="showReceiptModal = false"></div>
            <div class="mrt-modal relative w-full max-w-lg z-10" @click.stop>
                <div class="mrt-header">
                    <div>
                        <div class="mrt-title">Log Receipt</div>
                        <div class="mrt-subtitle" x-text="receiptForm._label"></div>
                    </div>
                    <button type="button" class="mrt-close" @click="showReceiptModal = false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mrt-body">
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Received Date <span class="mrt-req">*</span></label>
                            <input type="date" x-model="receiptForm.received_date" class="mrt-input">
                        </div>
                        <div>
                            <label class="mrt-label">Received Via <span class="mrt-req">*</span></label>
                            <select x-model="receiptForm.received_method" class="mrt-select">
                                <option value="">Select...</option>
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                                <option value="portal">Portal</option>
                                <option value="mail">Mail</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Record Types Received -->
                    <div class="mb-4">
                        <label class="mrt-label mb-2">Records Received</label>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-if="receiptForm._needsMr">
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                                       :class="receiptForm.has_medical_records ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'">
                                    <input type="checkbox" x-model="receiptForm.has_medical_records" class="rounded">
                                    <span class="text-sm">Medical Records</span>
                                </label>
                            </template>
                            <template x-if="receiptForm._needsBill">
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                                       :class="receiptForm.has_billing ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'">
                                    <input type="checkbox" x-model="receiptForm.has_billing" class="rounded">
                                    <span class="text-sm">Billing</span>
                                </label>
                            </template>
                            <template x-if="receiptForm._needsChart">
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                                       :class="receiptForm.has_chart ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'">
                                    <input type="checkbox" x-model="receiptForm.has_chart" class="rounded">
                                    <span class="text-sm">Chart Notes</span>
                                </label>
                            </template>
                            <template x-if="receiptForm._needsImg">
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                                       :class="receiptForm.has_imaging ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'">
                                    <input type="checkbox" x-model="receiptForm.has_imaging" class="rounded">
                                    <span class="text-sm">Imaging</span>
                                </label>
                            </template>
                            <template x-if="receiptForm._needsOp">
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors"
                                       :class="receiptForm.has_op_report ? 'bg-green-50 border-green-300' : 'border-v2-card-border hover:bg-v2-bg'">
                                    <input type="checkbox" x-model="receiptForm.has_op_report" class="rounded">
                                    <span class="text-sm">OP Report</span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Mark as Complete -->
                    <div class="mb-4">
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border cursor-pointer transition-colors"
                               :class="receiptForm.is_complete ? 'bg-emerald-50 border-emerald-400' : 'border-v2-card-border hover:bg-v2-bg'">
                            <input type="checkbox" x-model="receiptForm.is_complete" class="rounded">
                            <span class="text-sm font-semibold" :class="receiptForm.is_complete ? 'text-emerald-700' : ''">All records received (mark complete)</span>
                        </label>
                    </div>

                    <!-- Incomplete reason -->
                    <template x-if="!receiptForm.is_complete">
                        <div class="mb-4">
                            <label class="mrt-label">What's still missing?</label>
                            <input type="text" x-model="receiptForm.incomplete_reason" class="mrt-input" placeholder="e.g., Still waiting for billing records">
                        </div>
                    </template>

                    <div>
                        <label class="mrt-label">Notes</label>
                        <textarea x-model="receiptForm.notes" rows="2" class="mrt-textarea" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="mrt-footer" style="justify-content:space-between;">
                    <button type="button" @click="setProviderOnHold()" :disabled="saving" class="rcm-btn-hold" style="background:#fff; border:1.5px solid #d0cdc5; border-radius:7px; padding:6px 14px; cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:2px;">
                        <span style="font-size:14px; line-height:1;">⏸</span>
                        <span style="font-size:9px; font-weight:700; color:#8a8a82; text-transform:uppercase; letter-spacing:.05em;">On Hold</span>
                    </button>
                    <div style="display:flex; gap:10px; align-items:center;">
                    <button @click="showReceiptModal = false" class="mrt-btn-cancel">Cancel</button>
                    <button @click="submitReceipt()" :disabled="saving" class="mrt-btn-primary" style="background:#059669;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Log Receipt'"></span>
                    </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview & Send Modal (PSM) Styles -->
        <style>
        .psm { width: 800px; border-radius: 12px; box-shadow: 0 24px 64px rgba(0,0,0,.24); overflow: hidden; background: #fff; max-height: 90vh; display: flex; flex-direction: column; }
        .psm-header { background: #0F1B2D; padding: 18px 24px 16px; display: flex; align-items: flex-start; justify-content: space-between; flex-shrink: 0; }
        .psm-header h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0; line-height: 1.3; }
        .psm-header .psm-subtitle { font-size: 12px; font-weight: 500; color: var(--gold, #C9A84C); margin-top: 2px; }
        .psm-header-actions { display: flex; align-items: center; gap: 10px; }
        .psm-edit-btn {
            padding: 5px 12px; font-size: 12px; font-weight: 600; border-radius: 6px;
            border: 1.5px solid rgba(255,255,255,.2); background: none; color: rgba(255,255,255,.6);
            cursor: pointer; display: flex; align-items: center; gap: 5px; transition: all .15s;
        }
        .psm-edit-btn:hover { color: #fff; background: rgba(255,255,255,.1); }
        .psm-edit-btn.active { color: #fff; background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.3); }
        .psm-close { background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 4px; transition: color .15s; }
        .psm-close:hover { color: rgba(255,255,255,.75); }
        .psm-toolbar { padding: 12px 24px; border-bottom: 1px solid var(--border, #d0cdc5); background: #fafafa; flex-shrink: 0; }
        .psm-label { display: block; font-size: 9.5px; font-weight: 700; color: var(--muted, #8a8a82); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 5px; }
        .psm-input {
            width: 100%; background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
            padding: 9px 12px; font-size: 13px; color: #1a2535; transition: all .15s; outline: none; font-family: inherit;
        }
        .psm-input:focus { border-color: var(--gold, #C9A84C); background: #fff; box-shadow: 0 0 0 3px rgba(201,168,76,.1); }
        .psm-input::placeholder { color: #c5c5c5; }
        .psm-input[readonly] { background: #f5f5f0; color: var(--muted, #8a8a82); cursor: default; }
        .psm-content { flex: 1; overflow-y: auto; padding: 16px 24px; }
        .psm-iframe-wrap {
            border: 1.5px solid var(--border, #d0cdc5); border-radius: 8px; background: #fff;
            box-shadow: inset 0 1px 3px rgba(0,0,0,.05); overflow: hidden; transition: border-color .2s;
        }
        .psm-iframe-wrap.editing { border-color: var(--gold, #C9A84C); box-shadow: inset 0 1px 3px rgba(0,0,0,.05), 0 0 0 3px rgba(201,168,76,.1); }
        .psm-iframe-wrap iframe { width: 100%; border: 0; min-height: 600px; }
        .psm-footer { padding: 14px 24px; border-top: 1px solid var(--border, #d0cdc5); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .psm-footer-info { font-size: 12px; color: var(--muted, #8a8a82); display: flex; align-items: center; gap: 10px; }
        .psm-footer-info .psm-reset-btn { text-decoration: underline; color: var(--muted, #8a8a82); background: none; border: none; cursor: pointer; font-size: 12px; }
        .psm-footer-info .psm-modified { display: inline-flex; align-items: center; gap: 4px; color: #d97706; font-size: 11px; font-weight: 500; }
        .psm-btn-cancel {
            background: #fff; border: 1.5px solid var(--border, #d0cdc5); border-radius: 7px;
            padding: 9px 18px; font-size: 13px; font-weight: 500; color: #5A6B82; cursor: pointer; transition: all .15s;
        }
        .psm-btn-cancel:hover { background: #f8f7f4; border-color: #ccc; }
        .psm-btn-send {
            background: #1a9e6a; color: #fff; border: none; border-radius: 7px;
            padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer;
            box-shadow: 0 2px 8px rgba(26,158,106,.3); display: flex; align-items: center; gap: 6px; transition: all .15s;
        }
        .psm-btn-send:hover { filter: brightness(1.08); box-shadow: 0 4px 12px rgba(26,158,106,.4); }
        .psm-btn-send:disabled { opacity: .55; cursor: not-allowed; }
        </style>

        <!-- Preview & Send Modal -->
        <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display:none;" @keydown.escape.window="showPreviewModal && closePreviewModal()">
            <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closePreviewModal()"></div>
            <div class="psm relative z-10" @click.stop>

                <!-- Header -->
                <div class="psm-header">
                    <div>
                        <h3 x-text="isEditingLetter ? 'Edit Request Letter' : 'Preview Request Letter'"></h3>
                        <div class="psm-subtitle">
                            Sending via <span style="font-weight:600;" x-text="previewData.method === 'email' ? 'Email' : 'Fax'"></span>
                            to <span style="font-weight:600;" x-text="previewData.provider_name"></span>
                        </div>
                    </div>
                    <div class="psm-header-actions">
                        <button @click="toggleLetterEdit()" class="psm-edit-btn" :class="isEditingLetter ? 'active' : ''">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span x-text="isEditingLetter ? 'Editing' : 'Edit Letter'"></span>
                        </button>
                        <button class="psm-close" @click="closePreviewModal()">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Toolbar (Recipient / Subject) -->
                <div class="psm-toolbar">
                    <div style="display:flex; gap:12px;">
                        <div style="flex:1;">
                            <label class="psm-label" x-text="previewData.method === 'email' ? 'Recipient Email' : 'Recipient Fax Number'"></label>
                            <input type="text" x-model="previewData.recipient" class="psm-input"
                                :placeholder="previewData.method === 'email' ? 'provider@example.com' : '(212) 555-1234'">
                        </div>
                        <div style="flex:1;" x-show="previewData.method === 'email'">
                            <label class="psm-label">Subject</label>
                            <input type="text" x-model="previewData.subject" :readonly="!isEditingLetter" class="psm-input">
                        </div>
                    </div>
                </div>

                <!-- Letter Content -->
                <div class="psm-content">
                    <div class="psm-iframe-wrap" :class="isEditingLetter ? 'editing' : ''">
                        <iframe x-ref="letterIframe" :srcdoc="previewData.letter_html"></iframe>
                    </div>
                </div>

                <!-- Footer -->
                <div class="psm-footer">
                    <div class="psm-footer-info">
                        <template x-if="previewData.send_status === 'failed'">
                            <span style="color:#dc2626;">Previous attempt failed. You can retry.</span>
                        </template>
                        <template x-if="isEditingLetter && originalLetterHtml">
                            <button @click="resetLetterToOriginal()" class="psm-reset-btn">Reset to Original</button>
                        </template>
                        <template x-if="originalLetterHtml && !isEditingLetter">
                            <span class="psm-modified">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/></svg>
                                Letter has been modified
                            </span>
                        </template>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button @click="closePreviewModal()" class="psm-btn-cancel">Cancel</button>
                        <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient" class="psm-btn-send">
                            <template x-if="sending">
                                <div class="spinner" style="width:15px;height:15px;border-width:2px;"></div>
                            </template>
                            <span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /MR Tracker Tab -->

    <!-- ===================== HEALTH TRACKER TAB ===================== -->
    <div x-show="activeTab === 'health'" x-cloak x-data="healthLedgerPage()" x-init="init()"
         @hl-import.window="showImportModal = true" @hl-add.window="openAddModal()">

        <!-- Summary Cards -->
        <div class="grid grid-cols-6 gap-3 mb-3">
            <div @click="toggleStatusFilter('')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
                 :class="statusFilter === '' ? 'ring-2 ring-gold' : ''">
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Total</p>
                <p class="text-lg font-bold text-v2-text" x-text="summary.total ?? '-'"></p>
            </div>
            <div @click="toggleStatusFilter('not_started')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
                 :class="statusFilter === 'not_started' ? 'ring-2 ring-gray-400' : ''">
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Not Started</p>
                <p class="text-lg font-bold text-gray-500" x-text="summary.not_started ?? '-'"></p>
            </div>
            <div @click="toggleStatusFilter('requesting')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
                 :class="statusFilter === 'requesting' ? 'ring-2 ring-blue-400' : ''">
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Requesting</p>
                <p class="text-lg font-bold text-blue-600" x-text="summary.requesting ?? '-'"></p>
            </div>
            <div @click="toggleStatusFilter('follow_up')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
                 :class="statusFilter === 'follow_up' ? 'ring-2 ring-amber-400' : ''">
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Follow Up</p>
                <p class="text-lg font-bold text-amber-600" x-text="summary.follow_up ?? '-'"></p>
            </div>
            <div @click="toggleStatusFilter('received')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
                 :class="statusFilter === 'received' ? 'ring-2 ring-green-400' : ''">
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Received</p>
                <p class="text-lg font-bold text-green-600" x-text="summary.received ?? '-'"></p>
            </div>
            <div @click="toggleStatusFilter('done')" class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 cursor-pointer card-hover"
                 :class="statusFilter === 'done' ? 'ring-2 ring-emerald-400' : ''">
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Done</p>
                <p class="text-lg font-bold text-emerald-600" x-text="summary.done ?? '-'"></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-3 py-2 mb-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
                           placeholder="Search client, case #, or carrier..."
                           class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <select x-model="statusFilter" @change="loadData(1)"
                        class="px-2 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    <option value="">All Statuses</option>
                    <option value="not_started">Not Started</option>
                    <option value="requesting">Requesting</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="received">Received</option>
                    <option value="done">Done</option>
                </select>
                <select x-model="assignedFilter" @change="loadData(1)"
                        class="px-2 py-1.5 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    <option value="">All Staff</option>
                    <template x-for="s in staffList" :key="s.id">
                        <option :value="s.id" x-text="s.full_name"></option>
                    </template>
                </select>
                <button @click="resetFilters()"
                        class="px-2 py-1.5 text-sm text-v2-text-mid border border-v2-card-border rounded-lg hover:bg-v2-bg"
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
                                    <td colspan="11" class="!p-0">
                                        <div :class="expandedId === item.id ? 'border-2 border-gold rounded-lg bg-white' : ''" class="transition-all">
                                            <div class="flex items-center cursor-pointer hover:bg-v2-bg/50 transition-colors"
                                                 :class="{ 'hl-row-followup': item.is_followup_due && expandedId !== item.id }"
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
                                                        <template x-if="item.case_id">
                                                            <button @click="window.location.href='/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id" class="p-1 text-v2-text-light hover:text-blue-600 rounded" title="Open Case">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            </button>
                                                        </template>
                                                        <button @click="updateStatus(item.id, 'received')" class="p-1 text-v2-text-light hover:text-green-600 rounded" title="Mark Received" x-show="item.overall_status !== 'received' && item.overall_status !== 'done'">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        </button>
                                                        <button @click="openEditModal(item)" class="p-1 text-v2-text-light hover:text-gold rounded" title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
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
             style="display:none;" @keydown.escape.window="closeModals()">
            <div class="fixed inset-0 mrt-backdrop" @click="closeModals()"></div>
            <div class="mrt-modal relative w-full max-w-2xl z-10" @click.stop>
                <div class="mrt-header">
                    <div class="mrt-title" x-text="showEditModal ? 'Edit Item' : 'Add Item'"></div>
                    <button type="button" class="mrt-close" @click="closeModals()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mrt-body" style="max-height:calc(90vh - 140px);overflow-y:auto;">
                    <template x-if="showAddModal">
                        <div>
                            <label class="mrt-label">Search Case</label>
                            <div class="relative">
                                <input type="text" x-model="caseSearch" @input.debounce.300ms="searchCases()"
                                       @focus="showCaseDropdown = caseResults.length > 0"
                                       @click.away="showCaseDropdown = false"
                                       placeholder="Type client name or case #..."
                                       class="mrt-input">
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
                    <template x-if="showEditModal">
                        <div class="mrt-row">
                            <div>
                                <label class="mrt-label">Client Name <span class="mrt-req">*</span></label>
                                <input type="text" x-model="form.client_name" class="mrt-input">
                            </div>
                            <div>
                                <label class="mrt-label">Case #</label>
                                <input type="text" x-model="form.case_number" class="mrt-input" style="background:var(--bg);" readonly>
                            </div>
                        </div>
                    </template>
                    <div class="mrt-row">
                        <div class="relative">
                            <label class="mrt-label">Insurance Carrier <span class="mrt-req">*</span></label>
                            <input type="text" x-model="form.insurance_carrier"
                                   @input.debounce.300ms="searchCarriers()"
                                   @focus="if(form.insurance_carrier.length >= 2) searchCarriers()"
                                   @click.away="showCarrierDropdown = false"
                                   autocomplete="off"
                                   class="mrt-input" placeholder="Type to search...">
                            <template x-if="showCarrierDropdown && carrierResults.length > 0">
                                <div class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="c in carrierResults" :key="c.id">
                                        <div @click="selectCarrier(c)" class="px-3 py-2 hover:bg-gold/10 cursor-pointer text-sm border-b border-v2-card-border last:border-0">
                                            <span class="font-medium" x-text="c.name"></span>
                                            <span class="text-xs text-v2-text-light ml-2" x-text="[c.email, c.fax].filter(Boolean).join(' | ') || ''"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="mrt-label">Assigned To</label>
                            <select x-model="form.assigned_to" class="mrt-select">
                                <option value="">Select...</option>
                                <template x-for="s in staffList" :key="s.id"><option :value="s.id" x-text="s.full_name"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Carrier Email</label>
                            <input type="email" x-model="form.carrier_contact_email" class="mrt-input" placeholder="claims@carrier.com">
                        </div>
                        <div>
                            <label class="mrt-label">Carrier Fax</label>
                            <input type="text" x-model="form.carrier_contact_fax" class="mrt-input" placeholder="(xxx) xxx-xxxx">
                        </div>
                    </div>
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Claim Number</label>
                            <input type="text" x-model="form.claim_number" class="mrt-input" placeholder="e.g., 123456789">
                        </div>
                        <div>
                            <label class="mrt-label">Member ID</label>
                            <input type="text" x-model="form.member_id" class="mrt-input" placeholder="e.g., UZ065914201">
                        </div>
                    </div>
                    <div>
                        <label class="mrt-label">Note</label>
                        <textarea x-model="form.note" rows="2" class="mrt-textarea"></textarea>
                    </div>
                </div>
                <div class="mrt-footer">
                    <button @click="closeModals()" class="mrt-btn-cancel">Cancel</button>
                    <button @click="saveItem()" :disabled="saving" class="mrt-btn-primary">
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
             style="display:none;" @keydown.escape.window="showRequestModal = false">
            <div class="fixed inset-0 mrt-backdrop" @click="showRequestModal = false"></div>
            <div class="mrt-modal relative w-full max-w-lg z-10" @click.stop>
                <div class="mrt-header">
                    <div>
                        <div class="mrt-title">New Request</div>
                        <div class="mrt-subtitle" x-text="reqForm._carrierLabel"></div>
                    </div>
                    <button type="button" class="mrt-close" @click="showRequestModal = false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mrt-body">
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Request Date <span class="mrt-req">*</span></label>
                            <input type="date" x-model="reqForm.request_date" class="mrt-input">
                        </div>
                        <div>
                            <label class="mrt-label">Method <span class="mrt-req">*</span></label>
                            <select x-model="reqForm.request_method" @change="updateRecipient()" class="mrt-select">
                                <option value="">Select...</option>
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                                <option value="portal">Portal</option>
                                <option value="phone">Phone</option>
                                <option value="mail">Mail</option>
                            </select>
                        </div>
                    </div>
                    <div class="mrt-row">
                        <div>
                            <label class="mrt-label">Type</label>
                            <select x-model="reqForm.request_type" class="mrt-select">
                                <option value="initial">Initial</option>
                                <option value="follow_up">Follow Up</option>
                                <option value="re_request">Re-Request</option>
                            </select>
                        </div>
                        <div>
                            <label class="mrt-label">Send To</label>
                            <input type="text" x-model="reqForm.sent_to" class="mrt-input" placeholder="Email or fax #">
                        </div>
                    </div>
                    <div>
                        <label class="mrt-label">Template</label>
                        <select x-model="reqForm.template_id" @change="onTemplateChange()" class="mrt-select">
                            <option value="">Default (no template)</option>
                            <template x-for="t in hlTemplates" :key="t.id">
                                <option :value="t.id" x-text="t.name + (t.is_default ? ' (Default)' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <template x-if="reqForm._showSettlement">
                        <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-lg space-y-3">
                            <p class="text-xs font-semibold text-amber-700 uppercase">Settlement Information</p>
                            <div class="mrt-row">
                                <div>
                                    <label class="mrt-label">Settlement Amount</label>
                                    <input type="number" step="0.01" x-model="reqForm.template_data.settlement_amount" class="mrt-input" placeholder="$0.00">
                                </div>
                                <div>
                                    <label class="mrt-label">Settlement Date</label>
                                    <input type="date" x-model="reqForm.template_data.settlement_date" class="mrt-input">
                                </div>
                            </div>
                            <div class="mrt-row">
                                <div>
                                    <label class="mrt-label">Attorney's Fees</label>
                                    <input type="number" step="0.01" x-model="reqForm.template_data.attorney_fees" class="mrt-input" placeholder="$0.00">
                                </div>
                                <div>
                                    <label class="mrt-label">Costs</label>
                                    <input type="number" step="0.01" x-model="reqForm.template_data.costs" class="mrt-input" placeholder="$0.00">
                                </div>
                            </div>
                        </div>
                    </template>

                    <div>
                        <label class="mrt-label">Notes</label>
                        <textarea x-model="reqForm.notes" rows="2" class="mrt-textarea"></textarea>
                    </div>
                </div>
                <div class="mrt-footer">
                    <button @click="showRequestModal = false" class="mrt-btn-cancel">Cancel</button>
                    <button @click="submitRequest()" :disabled="saving" class="mrt-btn-primary">
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
             style="display:none;" @keydown.escape.window="showSendModal = false">
            <div class="fixed inset-0 mrt-backdrop" @click="showSendModal = false"></div>
            <div class="mrt-modal relative w-full max-w-4xl z-10 flex flex-col" style="max-height:90vh;" @click.stop>
                <div class="mrt-header">
                    <div>
                        <div class="mrt-title">Preview & Send</div>
                        <div class="mrt-subtitle" x-text="previewData.carrier + ' via ' + previewData.method"></div>
                    </div>
                    <button type="button" class="mrt-close" @click="showSendModal = false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-6">
                    <iframe :srcdoc="previewData.letter_html" class="w-full border rounded-lg" style="height: 500px;"></iframe>
                </div>
                <div class="mrt-footer">
                    <div class="flex items-center gap-4 w-full">
                        <label class="mrt-label" style="margin-bottom:0;white-space:nowrap;">Recipient:</label>
                        <input type="text" x-model="previewData.recipient" class="mrt-input flex-1">
                        <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient"
                                class="mrt-btn-send"
                                :style="previewData.method === 'email' ? 'background:#0d9488;box-shadow:0 2px 8px rgba(13,148,136,.3);' : 'background:#7c3aed;box-shadow:0 2px 8px rgba(124,58,237,.3);'"
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
             style="display:none;" @keydown.escape.window="showImportModal = false; importFile = null; importResult = null;">
            <div class="fixed inset-0 mrt-backdrop" @click="showImportModal = false; importFile = null; importResult = null;"></div>
            <div class="mrt-modal relative w-full max-w-lg z-10" @click.stop>
                <div class="mrt-header">
                    <div class="mrt-title">Import CSV</div>
                    <button type="button" class="mrt-close" @click="showImportModal = false; importFile = null; importResult = null;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mrt-body">
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
                <div class="mrt-footer">
                    <button @click="showImportModal = false; importFile = null; importResult = null;" class="mrt-btn-cancel">Close</button>
                    <button @click="doImport()" :disabled="!importFile || importing" class="mrt-btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span x-text="importing ? 'Importing...' : 'Import'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /Health Tracker Tab -->

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

    /* Health tracker row highlight */
    .hl-row-followup { background-color: #fffbeb; border-left: 3px solid #f59e0b; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
