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
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 flex items-center justify-between card-hover">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Open Cases</p>
                <p class="text-lg font-bold text-v2-text" x-text="summary.active_cases ?? '-'"></p>
            </div>
            <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 flex items-center justify-between card-hover">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Requesting</p>
                <p class="text-lg font-bold text-yellow-600" x-text="summary.requesting_count ?? '-'"></p>
            </div>
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 flex items-center justify-between card-hover">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Follow-ups Due</p>
                <p class="text-lg font-bold text-orange-600" x-text="summary.followup_due ?? '-'"></p>
            </div>
            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border px-4 py-2.5 flex items-center justify-between card-hover">
            <div>
                <p class="text-[10px] text-v2-text-light uppercase tracking-wide">Overdue</p>
                <p class="text-lg font-bold text-red-600" x-text="summary.overdue_count ?? '-'"></p>
            </div>
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
    </div>

    <!-- Escalation Alert Banner -->
    <template x-if="escalations.length > 0">
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border mb-4">
            <div class="px-4 py-2.5 border-b border-v2-card-border flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <h2 class="text-sm font-semibold text-v2-text">Escalated Items</h2>
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
            <div class="divide-y divide-v2-bg max-h-40 overflow-y-auto">
                <template x-for="item in escalations" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-4 py-2 hover:bg-v2-bg transition-colors esc-row"
                       :class="'esc-row-' + item.escalation_tier">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="escalation-badge" :class="item.escalation_css"
                                      x-text="item.escalation_label"></span>
                                <span class="text-xs font-medium text-v2-text" x-text="item.provider_name"></span>
                                <span class="text-xs text-v2-text-light" x-text="item.case_number + ' - ' + item.client_name"></span>
                            </div>
                            <div class="flex items-center gap-2">
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Staff Workload Card -->
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border">
            <div class="px-4 py-2.5 border-b border-v2-card-border">
                <h2 class="text-sm font-semibold text-v2-text">
                    <span x-show="staffMetrics.view_type === 'personal'">My Workload</span>
                    <span x-show="staffMetrics.view_type === 'team'">Team Workload</span>
                </h2>
            </div>
            <div class="p-4">
                <!-- Personal View (Staff) -->
                <template x-if="staffMetrics.view_type === 'personal'">
                    <div>
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <div class="text-center">
                                <p class="text-lg font-bold text-v2-text" x-text="staffMetrics.my_metrics?.my_cases || 0"></p>
                                <p class="text-[10px] text-v2-text-light">My Cases</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-bold text-orange-600" x-text="staffMetrics.my_metrics?.my_followup || 0"></p>
                                <p class="text-[10px] text-v2-text-light">Followups</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-bold text-red-600" x-text="staffMetrics.my_metrics?.my_overdue || 0"></p>
                                <p class="text-[10px] text-v2-text-light">Overdue</p>
                            </div>
                        </div>
                        <div class="border-t border-v2-card-border pt-2">
                            <div class="flex gap-4 text-[10px] text-v2-text-light">
                                <span>Avg: <span class="font-medium" x-text="staffMetrics.team_avg?.avg_cases || 0"></span> cases</span>
                                <span><span class="font-medium" x-text="staffMetrics.team_avg?.avg_followup || 0"></span> f/u</span>
                                <span><span class="font-medium" x-text="staffMetrics.team_avg?.avg_overdue || 0"></span> overdue</span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Team View (Manager/Admin) -->
                <template x-if="staffMetrics.view_type === 'team'">
                    <div>
                        <div class="grid grid-cols-3 gap-3 mb-3 text-center">
                            <div>
                                <p class="text-lg font-bold text-v2-text" x-text="staffMetrics.totals?.total_cases || 0"></p>
                                <p class="text-[10px] text-v2-text-light">Total Cases</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-orange-600" x-text="staffMetrics.totals?.total_followup || 0"></p>
                                <p class="text-[10px] text-v2-text-light">Followups</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-red-600" x-text="staffMetrics.totals?.total_overdue || 0"></p>
                                <p class="text-[10px] text-v2-text-light">Overdue</p>
                            </div>
                        </div>
                        <div class="border-t border-v2-card-border pt-2 max-h-32 overflow-y-auto">
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
                                            <td class="py-1" x-text="staff.full_name"></td>
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
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border">
            <div class="px-4 py-2.5 border-b border-v2-card-border">
                <h2 class="text-sm font-semibold text-v2-text">System Health</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-4 gap-3">
                    <!-- Communication Success -->
                    <div class="text-center p-2 bg-v2-bg rounded-lg">
                        <p class="text-lg font-bold" :class="(systemHealth.communication?.overall_rate || 0) >= 90 ? 'text-emerald-600' : ((systemHealth.communication?.overall_rate || 0) >= 75 ? 'text-yellow-600' : 'text-red-600')">
                            <span x-text="(systemHealth.communication?.overall_rate || 0) + '%'"></span>
                        </p>
                        <p class="text-[10px] text-v2-text-light">Comm Success</p>
                    </div>

                    <!-- Cases on Hold -->
                    <div class="text-center p-2 bg-v2-bg rounded-lg">
                        <p class="text-lg font-bold text-v2-text" x-text="systemHealth.on_hold?.total_providers || 0"></p>
                        <p class="text-[10px] text-v2-text-light">On Hold</p>
                    </div>

                    <!-- Health Ledger -->
                    <div class="text-center p-2 bg-v2-bg rounded-lg">
                        <p class="text-lg font-bold text-yellow-600" x-text="systemHealth.health_ledger?.active || 0"></p>
                        <p class="text-[10px] text-v2-text-light">HL Active</p>
                    </div>

                    <!-- Treatment Status -->
                    <div class="text-center p-2 bg-v2-bg rounded-lg">
                        <div class="flex gap-1 justify-center items-center">
                            <span class="text-sm font-bold text-emerald-600" x-text="systemHealth.treatment_status?.in_treatment || 0"></span>
                            <span class="text-[10px] text-v2-text-light">/</span>
                            <span class="text-sm font-bold text-v2-text" x-text="systemHealth.treatment_status?.treatment_done || 0"></span>
                        </div>
                        <p class="text-[10px] text-v2-text-light">Treating/Done</p>
                    </div>
                </div>

                <!-- Top Hold Reasons -->
                <template x-if="(systemHealth.on_hold?.top_reasons || []).length > 0">
                    <div class="mt-3 pt-3 border-t border-v2-card-border">
                        <p class="text-[10px] font-semibold text-v2-text-light mb-1">Hold Reasons:</p>
                        <div class="flex gap-3 text-[10px]">
                            <template x-for="reason in (systemHealth.on_hold?.top_reasons || []).slice(0, 3)" :key="reason.hold_reason">
                                <span class="text-v2-text-light"><span class="font-medium text-v2-text" x-text="reason.count"></span> <span x-text="reason.hold_reason"></span></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Follow-ups Due -->
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border">
            <div class="px-4 py-2.5 border-b border-v2-card-border flex items-center justify-between">
                <h2 class="text-sm font-semibold text-v2-text">Follow-ups Due</h2>
                <span class="text-[10px] bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded-full font-medium" x-text="followups.length"></span>
            </div>
            <div class="divide-y divide-v2-bg max-h-52 overflow-y-auto">
                <template x-if="followups.length === 0">
                    <div class="px-4 py-6 text-center text-v2-text-light text-xs">No follow-ups due</div>
                </template>
                <template x-for="item in followups" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-4 py-2 hover:bg-v2-bg transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-medium text-v2-text" x-text="item.provider_name"></span>
                                <span class="text-[10px] text-v2-text-light ml-1" x-text="item.case_number + ' · ' + item.client_name"></span>
                            </div>
                            <span class="text-[10px] text-orange-600 font-medium" x-text="item.days_since_request + 'd ago'"></span>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        <!-- Overdue Items -->
        <div class="bg-white rounded-lg shadow-sm border border-v2-card-border">
            <div class="px-4 py-2.5 border-b border-v2-card-border flex items-center justify-between">
                <h2 class="text-sm font-semibold text-v2-text">Overdue Items</h2>
                <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full font-medium" x-text="overdueItems.length"></span>
            </div>
            <div class="divide-y divide-v2-bg max-h-52 overflow-y-auto">
                <template x-if="overdueItems.length === 0">
                    <div class="px-4 py-6 text-center text-v2-text-light text-xs">No overdue items</div>
                </template>
                <template x-for="item in overdueItems" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-4 py-2 hover:bg-v2-bg transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-medium text-v2-text" x-text="item.provider_name"></span>
                                <span class="text-[10px] text-v2-text-light ml-1" x-text="item.case_number + ' · ' + item.client_name"></span>
                            </div>
                            <span class="text-[10px] text-red-600 font-medium" x-text="item.days_overdue + 'd overdue'"></span>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Provider Insights (Expandable) -->
    <div class="bg-white rounded-lg shadow-sm border border-v2-card-border mb-4">
        <div class="px-4 py-2.5 border-b border-v2-card-border flex items-center justify-between cursor-pointer hover:bg-v2-bg transition-colors" @click="toggleProviderInsights()">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h2 class="text-sm font-semibold text-v2-text">Provider Insights</h2>
                <span class="text-[10px] text-v2-text-light" x-text="(providerAnalytics.top_difficult || []).length + ' difficult providers'"></span>
            </div>
            <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="providerInsightsExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

        <div x-show="providerInsightsExpanded" x-collapse>
            <div class="p-4">
                <!-- Difficulty Distribution + Table combined -->
                <div class="flex gap-4 mb-3">
                    <template x-for="diff in (providerAnalytics.difficulty_distribution || [])" :key="diff.difficulty_level">
                        <div class="text-center px-3 py-1.5 rounded-lg flex-1" :class="{
                            'bg-red-50': diff.difficulty_level === 'hard',
                            'bg-yellow-50': diff.difficulty_level === 'medium',
                            'bg-emerald-50': diff.difficulty_level === 'easy'
                        }">
                            <span class="text-sm font-bold" :class="{
                                'text-red-600': diff.difficulty_level === 'hard',
                                'text-yellow-600': diff.difficulty_level === 'medium',
                                'text-emerald-600': diff.difficulty_level === 'easy'
                            }" x-text="diff.request_count"></span>
                            <span class="text-[10px] text-v2-text-light capitalize ml-1" x-text="diff.difficulty_level"></span>
                        </div>
                    </template>
                </div>

                <!-- Top Difficult Providers Table -->
                <div class="max-h-40 overflow-y-auto">
                    <table class="w-full text-xs">
                        <thead class="sticky top-0 bg-white">
                            <tr class="border-b border-v2-card-border">
                                <th class="text-left py-1">Provider</th>
                                <th class="text-center py-1">Difficulty</th>
                                <th class="text-center py-1">Active</th>
                                <th class="text-center py-1">Overdue</th>
                                <th class="text-center py-1">Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="(providerAnalytics.top_difficult || []).length === 0">
                                <tr><td colspan="5" class="text-center text-v2-text-light py-4 text-xs">No difficult providers</td></tr>
                            </template>
                            <template x-for="provider in (providerAnalytics.top_difficult || [])" :key="provider.id">
                                <tr class="border-b border-v2-bg">
                                    <td class="py-1.5">
                                        <a :href="'/MRMS/frontend/pages/providers/index.php?highlight=' + provider.id"
                                           class="font-medium text-v2-text hover:text-gold"
                                           x-text="provider.name"></a>
                                    </td>
                                    <td class="text-center py-1.5">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium" :class="{
                                            'bg-red-100 text-red-600': provider.difficulty_level === 'hard',
                                            'bg-yellow-100 text-yellow-600': provider.difficulty_level === 'medium',
                                            'bg-emerald-100 text-emerald-600': provider.difficulty_level === 'easy'
                                        }" x-text="provider.difficulty_level"></span>
                                    </td>
                                    <td class="text-center py-1.5 font-medium" x-text="provider.active_requests"></td>
                                    <td class="text-center py-1.5">
                                        <span :class="provider.overdue_count > 0 ? 'text-red-600 font-semibold' : 'text-v2-text-light'" x-text="provider.overdue_count"></span>
                                    </td>
                                    <td class="text-center py-1.5 text-v2-text-light" x-text="provider.avg_response_days ? provider.avg_response_days + 'd' : '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cases -->
    <div class="bg-white rounded-lg shadow-sm border border-v2-card-border">
        <div class="px-4 py-2.5 border-b border-v2-card-border flex items-center justify-between">
            <h2 class="text-sm font-semibold text-v2-text" x-text="$store.auth.isStaff ? 'My Open Cases' : 'All Open Cases'">My Open Cases</h2>
            <a href="/MRMS/frontend/pages/cases/index.php" class="text-xs text-gold hover:text-gold">View All</a>
        </div>
        <div class="max-h-72 overflow-y-auto">
            <table class="data-table">
                <thead class="sticky top-0 bg-white z-10">
                    <tr>
                        <th>Case #</th>
                        <th>Client</th>
                        <th>Attorney</th>
                        <th>Progress</th>
                        <th>Issues</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="cases.length === 0">
                        <tr><td colspan="6" class="text-center text-v2-text-light py-6">No open cases</td></tr>
                    </template>
                    <template x-for="c in cases" :key="c.id">
                        <tr class="cursor-pointer" @click="window.location.href='/MRMS/frontend/pages/cases/detail.php?id='+c.id">
                            <td class="font-medium text-gold" x-text="c.case_number"></td>
                            <td x-text="c.client_name"></td>
                            <td x-text="c.attorney_name || '-'"></td>
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
                            <td>
                                <span class="status-badge" :class="'status-' + c.status" x-text="getStatusLabel(c.status)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
