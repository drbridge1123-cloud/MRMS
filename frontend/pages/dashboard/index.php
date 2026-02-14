<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<div x-data="dashboardPage()" x-init="init()">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Cases -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Cases</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1" x-text="summary.active_cases ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Requesting -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Providers Requesting</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1" x-text="summary.requesting_count ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Follow-ups Due -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Follow-ups Due</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1" x-text="summary.followup_due ?? '-'"></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Overdue Items</p>
                    <p class="text-3xl font-bold text-red-600 mt-1" x-text="summary.overdue_count ?? '-'"></p>
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <h2 class="font-semibold text-gray-800">Escalated Items</h2>
                </div>
                <div class="flex gap-2">
                    <template x-if="summary.escalation_admin > 0">
                        <span class="escalation-badge escalation-admin escalation-pulse" x-text="summary.escalation_admin + ' Admin'"></span>
                    </template>
                    <template x-if="summary.escalation_manager > 0">
                        <span class="escalation-badge escalation-manager" x-text="summary.escalation_manager + ' Manager'"></span>
                    </template>
                    <template x-if="summary.escalation_action_needed > 0">
                        <span class="escalation-badge escalation-action-needed" x-text="summary.escalation_action_needed + ' Action Needed'"></span>
                    </template>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                <template x-for="item in escalations" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-6 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="escalation-badge" :class="item.escalation_css"
                                      x-text="item.escalation_label"></span>
                                <span class="text-sm font-medium text-gray-800" x-text="item.provider_name"></span>
                                <span class="text-xs text-gray-400" x-text="item.case_number + ' - ' + item.client_name"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-500" x-text="item.assigned_name || 'Unassigned'"></span>
                                <span class="text-xs font-semibold" :class="item.escalation_tier === 'admin' ? 'text-red-600' : (item.escalation_tier === 'manager' ? 'text-orange-600' : 'text-yellow-600')"
                                      x-text="item.days_since_first_request + ' days'"></span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Follow-ups Due -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Follow-ups Due</h2>
                <span class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full font-medium" x-text="followups.length + ' items'"></span>
            </div>
            <div class="divide-y divide-gray-50">
                <template x-if="followups.length === 0">
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">No follow-ups due</div>
                </template>
                <template x-for="item in followups" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-6 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-800" x-text="item.provider_name"></span>
                                <span class="text-xs text-gray-400 ml-2" x-text="item.case_number"></span>
                            </div>
                            <span class="text-xs text-orange-600 font-medium" x-text="item.days_since_request + ' days ago'"></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" x-text="item.client_name"></p>
                    </a>
                </template>
            </div>
        </div>

        <!-- Overdue Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Overdue Items</h2>
                <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium" x-text="overdueItems.length + ' items'"></span>
            </div>
            <div class="divide-y divide-gray-50">
                <template x-if="overdueItems.length === 0">
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">No overdue items</div>
                </template>
                <template x-for="item in overdueItems" :key="item.id">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + item.case_id"
                       class="block px-6 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-800" x-text="item.provider_name"></span>
                                <span class="text-xs text-gray-400 ml-2" x-text="item.case_number"></span>
                            </div>
                            <span class="text-xs text-red-600 font-medium" x-text="item.days_overdue + ' days overdue'"></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" x-text="item.client_name"></p>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Recent Cases -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">My Active Cases</h2>
            <a href="/MRMS/frontend/pages/cases/index.php" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Case #</th>
                        <th>Client</th>
                        <th>DOI</th>
                        <th>Attorney</th>
                        <th>Providers</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="cases.length === 0">
                        <tr><td colspan="6" class="text-center text-gray-400 py-8">No active cases</td></tr>
                    </template>
                    <template x-for="c in cases" :key="c.id">
                        <tr class="cursor-pointer" @click="window.location.href='/MRMS/frontend/pages/cases/detail.php?id='+c.id">
                            <td class="font-medium text-blue-600" x-text="c.case_number"></td>
                            <td x-text="c.client_name"></td>
                            <td x-text="formatDate(c.doi)"></td>
                            <td x-text="c.attorney_name || '-'"></td>
                            <td x-text="c.provider_count ?? '-'"></td>
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

<script>
function dashboardPage() {
    return {
        summary: {},
        followups: [],
        overdueItems: [],
        escalations: [],
        cases: [],
        loading: true,

        async init() {
            await Promise.all([
                this.loadSummary(),
                this.loadFollowups(),
                this.loadOverdue(),
                this.loadEscalations(),
                this.loadCases()
            ]);
            this.loading = false;
        },

        async loadSummary() {
            try {
                const res = await api.get('dashboard/summary');
                this.summary = res.data || {};
            } catch (e) {}
        },

        async loadFollowups() {
            try {
                const res = await api.get('dashboard/followup-due');
                this.followups = res.data || [];
            } catch (e) {}
        },

        async loadOverdue() {
            try {
                const res = await api.get('dashboard/overdue');
                this.overdueItems = res.data || [];
            } catch (e) {}
        },

        async loadEscalations() {
            try {
                const res = await api.get('dashboard/escalations');
                this.escalations = res.data || [];
            } catch (e) {}
        },

        async loadCases() {
            try {
                const res = await api.get('cases?status=active&per_page=10');
                this.cases = res.data || [];
            } catch (e) {}
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
