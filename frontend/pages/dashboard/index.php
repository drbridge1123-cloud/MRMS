<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$pageScripts = ['/MRMS/frontend/assets/js/pages/dashboard.js'];
ob_start();
?>

<div x-data="dashboardPage()" x-init="init()">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Cases -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-6 card-hover stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-v2-text-light">Open Cases</p>
                    <p class="v2-stat-number text-v2-text mt-1" x-text="summary.active_cases ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-v2-bg rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Requesting -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-6 card-hover stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-v2-text-light">Providers Requesting</p>
                    <p class="v2-stat-number text-yellow-600 mt-1" x-text="summary.requesting_count ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Follow-ups Due -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-6 card-hover stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-v2-text-light">Follow-ups Due</p>
                    <p class="v2-stat-number text-orange-600 mt-1" x-text="summary.followup_due ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-6 card-hover stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-v2-text-light">Overdue Items</p>
                    <p class="v2-stat-number text-red-600 mt-1" x-text="summary.overdue_count ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Escalation Alert Banner -->
    <template x-if="escalations.length > 0">
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-6">
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <h2 class="font-semibold text-v2-text">Escalated Items</h2>
                    <span class="text-xs text-v2-text-light ml-2"
                          x-text="$store.auth.isStaff ? 'Your items — manager & admin have been notified' : ($store.auth.isManager ? 'All escalations — you\'re notified when deadline is reached' : 'All escalations — you\'re notified at deadline+14 days')"></span>
                </div>
                <div class="flex gap-2">
                    <template x-if="summary.escalation_admin > 0">
                        <span class="escalation-badge escalation-admin escalation-pulse" x-text="summary.escalation_admin + ' Admin'"></span>
                    </template>
                    <template x-if="summary.escalation_action_needed > 0">
                        <span class="escalation-badge escalation-action-needed" x-text="summary.escalation_action_needed + ' Action Needed'"></span>
                    </template>
                </div>
            </div>
            <div class="divide-y divide-v2-bg">
                <template x-for="item in escalations" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-6 py-3 hover:bg-v2-bg transition-colors esc-row"
                       :class="'esc-row-' + item.escalation_tier">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="escalation-badge" :class="item.escalation_css"
                                      x-text="item.escalation_label"></span>
                                <span class="text-sm font-medium text-v2-text" x-text="item.provider_name"></span>
                                <span class="text-xs text-v2-text-light" x-text="item.case_number + ' - ' + item.client_name"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-v2-text-light" x-text="item.assigned_name || 'Unassigned'"></span>
                                <span class="text-xs font-semibold" :class="item.escalation_tier === 'admin' ? 'text-red-600' : 'text-orange-600'"
                                      x-text="item.days_past_deadline + 'd past deadline'"></span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </template>

    <!-- Staff Workload & System Health Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Staff Workload Card -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h2 class="font-semibold text-v2-text">
                    <span x-show="staffMetrics.view_type === 'personal'">My Workload</span>
                    <span x-show="staffMetrics.view_type === 'team'">Team Workload</span>
                </h2>
            </div>
            <div class="p-6">
                <!-- Personal View (Staff) -->
                <template x-if="staffMetrics.view_type === 'personal'">
                    <div>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-v2-text" x-text="staffMetrics.my_metrics?.my_cases || 0"></p>
                                <p class="text-xs text-v2-text-light mt-1">My Cases</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-orange-600" x-text="staffMetrics.my_metrics?.my_followup || 0"></p>
                                <p class="text-xs text-v2-text-light mt-1">Followups Due</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-red-600" x-text="staffMetrics.my_metrics?.my_overdue || 0"></p>
                                <p class="text-xs text-v2-text-light mt-1">Overdue</p>
                            </div>
                        </div>
                        <div class="border-t border-v2-card-border pt-3 mt-3">
                            <p class="text-xs text-v2-text-light mb-2">Team Average</p>
                            <div class="flex gap-4 text-xs">
                                <div>Cases: <span class="font-medium" x-text="staffMetrics.team_avg?.avg_cases || 0"></span></div>
                                <div>Followups: <span class="font-medium" x-text="staffMetrics.team_avg?.avg_followup || 0"></span></div>
                                <div>Overdue: <span class="font-medium" x-text="staffMetrics.team_avg?.avg_overdue || 0"></span></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Team View (Manager/Admin) -->
                <template x-if="staffMetrics.view_type === 'team'">
                    <div>
                        <div class="mb-4 grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-v2-text" x-text="staffMetrics.totals?.total_cases || 0"></p>
                                <p class="text-xs text-v2-text-light mt-1">Total Cases</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-orange-600" x-text="staffMetrics.totals?.total_followup || 0"></p>
                                <p class="text-xs text-v2-text-light mt-1">Total Followups</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-red-600" x-text="staffMetrics.totals?.total_overdue || 0"></p>
                                <p class="text-xs text-v2-text-light mt-1">Total Overdue</p>
                            </div>
                        </div>
                        <div class="border-t border-v2-card-border pt-3 mt-3 max-h-48 overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 bg-white">
                                    <tr class="border-b border-v2-card-border">
                                        <th class="text-left py-1">Staff</th>
                                        <th class="text-center py-1">Cases</th>
                                        <th class="text-center py-1">F/U</th>
                                        <th class="text-center py-1">Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="staff in staffMetrics.staff_metrics || []" :key="staff.id">
                                        <tr class="border-b border-v2-bg">
                                            <td class="py-1.5" x-text="staff.full_name"></td>
                                            <td class="text-center" x-text="staff.case_count"></td>
                                            <td class="text-center">
                                                <span :class="staff.followup_count > 0 ? 'text-orange-600 font-semibold' : ''" x-text="staff.followup_count"></span>
                                            </td>
                                            <td class="text-center">
                                                <span :class="staff.overdue_count > 0 ? 'text-red-600 font-semibold' : ''" x-text="staff.overdue_count"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- System Health Card -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h2 class="font-semibold text-v2-text">System Health</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Communication Success -->
                    <div class="text-center p-3 bg-v2-bg rounded-lg">
                        <p class="text-2xl font-bold" :class="(systemHealth.communication?.overall_rate || 0) >= 90 ? 'text-emerald-600' : ((systemHealth.communication?.overall_rate || 0) >= 75 ? 'text-yellow-600' : 'text-red-600')">
                            <span x-text="(systemHealth.communication?.overall_rate || 0) + '%'"></span>
                        </p>
                        <p class="text-xs text-v2-text-light mt-1">Comm Success</p>
                        <div class="flex gap-2 justify-center mt-2 text-[10px]">
                            <span>Email: <span x-text="(systemHealth.communication?.email?.rate || 0) + '%'"></span></span>
                            <span>Fax: <span x-text="(systemHealth.communication?.fax?.rate || 0) + '%'"></span></span>
                        </div>
                    </div>

                    <!-- Cases on Hold -->
                    <div class="text-center p-3 bg-v2-bg rounded-lg">
                        <p class="text-2xl font-bold text-v2-text" x-text="systemHealth.on_hold?.total_providers || 0"></p>
                        <p class="text-xs text-v2-text-light mt-1">Providers on Hold</p>
                        <p class="text-[10px] text-v2-text-light mt-2">
                            <span x-text="(systemHealth.on_hold?.cases_affected || 0) + ' cases'"></span>
                        </p>
                    </div>

                    <!-- Health Ledger -->
                    <div class="text-center p-3 bg-v2-bg rounded-lg">
                        <p class="text-2xl font-bold text-yellow-600" x-text="systemHealth.health_ledger?.active || 0"></p>
                        <p class="text-xs text-v2-text-light mt-1">HL Active</p>
                        <p class="text-[10px] text-v2-text-light mt-2">
                            <span x-text="(systemHealth.health_ledger?.total || 0) + ' total'"></span>
                        </p>
                    </div>

                    <!-- Treatment Status -->
                    <div class="text-center p-3 bg-v2-bg rounded-lg">
                        <div class="flex gap-1 justify-center items-center mb-1">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-emerald-600" x-text="systemHealth.treatment_status?.in_treatment || 0"></p>
                                <p class="text-[9px] text-v2-text-light">Treating</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-v2-text" x-text="systemHealth.treatment_status?.treatment_done || 0"></p>
                                <p class="text-[9px] text-v2-text-light">Done</p>
                            </div>
                        </div>
                        <p class="text-xs text-v2-text-light">Treatment Status</p>
                    </div>
                </div>

                <!-- Top Hold Reasons -->
                <template x-if="(systemHealth.on_hold?.top_reasons || []).length > 0">
                    <div class="mt-4 pt-4 border-t border-v2-card-border">
                        <p class="text-xs font-semibold text-v2-text-light mb-2">Top Hold Reasons:</p>
                        <div class="space-y-1">
                            <template x-for="reason in (systemHealth.on_hold?.top_reasons || []).slice(0, 3)" :key="reason.hold_reason">
                                <div class="flex justify-between text-xs">
                                    <span class="text-v2-text-light truncate" x-text="reason.hold_reason"></span>
                                    <span class="font-medium text-v2-text ml-2" x-text="reason.count"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Follow-ups Due -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                <h2 class="font-semibold text-v2-text">Follow-ups Due</h2>
                <span class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full font-medium" x-text="followups.length + ' items'"></span>
            </div>
            <div class="divide-y divide-v2-bg">
                <template x-if="followups.length === 0">
                    <div class="px-6 py-8 text-center text-v2-text-light text-sm">No follow-ups due</div>
                </template>
                <template x-for="item in followups" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-6 py-3 hover:bg-v2-bg transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-v2-text" x-text="item.provider_name"></span>
                                <span class="text-xs text-v2-text-light ml-2" x-text="item.case_number"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-orange-600 font-medium" x-text="item.days_since_request + ' days ago'"></span>
                                <p class="text-[10px] text-v2-text-light" x-text="'Due: ' + formatDate(item.next_followup_date)"></p>
                            </div>
                        </div>
                        <p class="text-xs text-v2-text-light mt-1" x-text="item.client_name"></p>
                    </a>
                </template>
            </div>
        </div>

        <!-- Overdue Items -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                <h2 class="font-semibold text-v2-text">Overdue Items</h2>
                <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium" x-text="overdueItems.length + ' items'"></span>
            </div>
            <div class="divide-y divide-v2-bg">
                <template x-if="overdueItems.length === 0">
                    <div class="px-6 py-8 text-center text-v2-text-light text-sm">No overdue items</div>
                </template>
                <template x-for="item in overdueItems" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-6 py-3 hover:bg-v2-bg transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-v2-text" x-text="item.provider_name"></span>
                                <span class="text-xs text-v2-text-light ml-2" x-text="item.case_number"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-red-600 font-medium" x-text="item.days_overdue + ' days overdue'"></span>
                                <p class="text-[10px] text-v2-text-light" x-text="'Deadline: ' + formatDate(item.deadline)"></p>
                            </div>
                        </div>
                        <p class="text-xs text-v2-text-light mt-1" x-text="item.client_name"></p>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Provider Insights (Expandable) -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-6">
        <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between cursor-pointer hover:bg-v2-bg transition-colors" @click="toggleProviderInsights()">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-v2-bg rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-v2-text">Provider Insights</h2>
                <span class="text-xs text-v2-text-light" x-text="'Top ' + ((providerAnalytics.top_difficult || []).length) + ' difficult providers'"></span>
            </div>
            <svg class="w-5 h-5 text-v2-text-light transition-transform" :class="providerInsightsExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

        <div x-show="providerInsightsExpanded" x-collapse>
            <div class="p-6">
                <!-- Difficulty Distribution Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <template x-for="diff in (providerAnalytics.difficulty_distribution || [])" :key="diff.difficulty_level">
                        <div class="text-center p-3 rounded-lg" :class="{
                            'bg-red-50': diff.difficulty_level === 'hard',
                            'bg-yellow-50': diff.difficulty_level === 'medium',
                            'bg-emerald-50': diff.difficulty_level === 'easy'
                        }">
                            <p class="text-lg font-bold" :class="{
                                'text-red-600': diff.difficulty_level === 'hard',
                                'text-yellow-600': diff.difficulty_level === 'medium',
                                'text-emerald-600': diff.difficulty_level === 'easy'
                            }" x-text="diff.request_count"></p>
                            <p class="text-xs text-v2-text-light mt-1 capitalize" x-text="diff.difficulty_level + ' Providers'"></p>
                            <p class="text-[10px] text-v2-text-light mt-1" x-text="diff.provider_count + ' providers, ' + (diff.avg_response ? Math.round(diff.avg_response) + 'd avg' : 'N/A')"></p>
                        </div>
                    </template>
                </div>

                <!-- Top Difficult Providers Table -->
                <div class="border-t border-v2-card-border pt-4">
                    <h3 class="text-sm font-semibold text-v2-text mb-3">Active Difficult Providers</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-v2-card-border">
                                    <th class="text-left py-2">Provider</th>
                                    <th class="text-left py-2">Type</th>
                                    <th class="text-center py-2">Difficulty</th>
                                    <th class="text-center py-2">Active</th>
                                    <th class="text-center py-2">Overdue</th>
                                    <th class="text-center py-2">Avg Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="(providerAnalytics.top_difficult || []).length === 0">
                                    <tr><td colspan="6" class="text-center text-v2-text-light py-6 text-xs">No difficult providers with active requests</td></tr>
                                </template>
                                <template x-for="provider in (providerAnalytics.top_difficult || [])" :key="provider.id">
                                    <tr class="border-b border-v2-bg hover:bg-v2-bg transition-colors">
                                        <td class="py-2.5">
                                            <a :href="'/MRMS/frontend/pages/providers/index.php?highlight=' + provider.id"
                                               class="font-medium text-v2-text hover:text-gold"
                                               x-text="provider.name"></a>
                                        </td>
                                        <td class="py-2.5 text-xs text-v2-text-light capitalize" x-text="provider.type?.replace('_', ' ')"></td>
                                        <td class="text-center py-2.5">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="{
                                                'bg-red-100 text-red-600': provider.difficulty_level === 'hard',
                                                'bg-yellow-100 text-yellow-600': provider.difficulty_level === 'medium',
                                                'bg-emerald-100 text-emerald-600': provider.difficulty_level === 'easy'
                                            }" x-text="provider.difficulty_level"></span>
                                        </td>
                                        <td class="text-center py-2.5 font-medium" x-text="provider.active_requests"></td>
                                        <td class="text-center py-2.5">
                                            <span :class="provider.overdue_count > 0 ? 'text-red-600 font-semibold' : 'text-v2-text-light'" x-text="provider.overdue_count"></span>
                                        </td>
                                        <td class="text-center py-2.5 text-xs text-v2-text-light" x-text="provider.avg_response_days ? provider.avg_response_days + 'd' : '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cases -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mt-6">
        <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
            <h2 class="font-semibold text-v2-text" x-text="$store.auth.isStaff ? 'My Open Cases' : 'All Open Cases'">My Open Cases</h2>
            <a href="/MRMS/frontend/pages/cases/index.php" class="text-sm text-gold hover:text-gold">View All</a>
        </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Client</th>
                        <th>DOI</th>
                        <th>Attorney</th>
                        <th>Providers</th>
                        <th>Progress</th>
                        <th>Issues</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="cases.length === 0">
                        <tr><td colspan="8" class="text-center text-v2-text-light py-8">No open cases</td></tr>
                    </template>
                    <template x-for="c in cases" :key="c.id">
                        <tr class="cursor-pointer" @click="window.location.href='/MRMS/frontend/pages/cases/detail.php?id='+c.id">
                            <td class="font-medium text-gold" x-text="c.case_number"></td>
                            <td x-text="c.client_name"></td>
                            <td x-text="formatDate(c.doi)"></td>
                            <td x-text="c.attorney_name || '-'"></td>
                            <td x-text="c.provider_count ?? '-'"></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-v2-card-border rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" :style="'width:' + (c.provider_total > 0 ? Math.round(c.provider_done/c.provider_total*100) : 0) + '%'"></div>
                                    </div>
                                    <span class="text-xs" x-text="c.provider_done + '/' + c.provider_total"></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <template x-if="c.provider_overdue > 0">
                                        <span class="text-xs text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full font-semibold" x-text="c.provider_overdue + ' overdue'"></span>
                                    </template>
                                    <template x-if="c.provider_followup > 0">
                                        <span class="text-xs text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full font-semibold" x-text="c.provider_followup + ' f/u'"></span>
                                    </template>
                                    <template x-if="c.provider_overdue === 0 && c.provider_followup === 0">
                                        <span class="text-emerald-500 text-xs">&#10003;</span>
                                    </template>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge" :class="'status-' + c.status" x-text="getStatusLabel(c.status)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
