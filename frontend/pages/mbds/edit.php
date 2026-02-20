<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'MBDS Report';
$currentPage = 'mbds';
$pageScripts = ['/MRMS/frontend/assets/js/pages/mbds-edit.js'];
ob_start();
?>

<div x-data="mbdsEditPage()">

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex items-center justify-center py-20">
            <div class="spinner"></div>
        </div>
    </template>

    <template x-if="!loading && caseData">
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <a :href="'/MRMS/frontend/pages/cases/detail.php?id=' + caseId" class="text-v2-text-light hover:text-v2-text">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-2xl font-bold text-v2-text">MBDS</h2>
                            <span class="status-badge text-xs px-2.5 py-1" :class="'status-' + caseData.case_status"
                                x-text="getStatusLabel(caseData.case_status)"></span>
                            <template x-if="report && report.status === 'draft'">
                                <span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full font-medium">Draft</span>
                            </template>
                            <template x-if="report && report.status === 'completed'">
                                <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">Completed</span>
                            </template>
                            <template x-if="report && report.status === 'approved'">
                                <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Approved</span>
                            </template>
                        </div>
                        <p class="text-sm text-v2-text-light">
                            <span x-text="caseData.case_number"></span> — <span x-text="caseData.client_name"></span>
                            <template x-if="caseData.doi">
                                <span> | DOI: <span x-text="formatDate(caseData.doi)"></span></span>
                            </template>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Insurance Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-5 mb-4">
                <h3 class="text-sm font-semibold text-v2-text-mid mb-3">Insurance Settings</h3>
                <div class="grid grid-cols-4 gap-4 mb-3">
                    <div>
                        <label class="block text-xs text-v2-text-light mb-1">PIP #1</label>
                        <input type="text" x-model="settings.pip1_name" @change="saveSettings()"
                            placeholder="Auto insurance carrier..."
                            class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm"
                            :disabled="report?.status !== 'draft'">
                    </div>
                    <div>
                        <label class="block text-xs text-v2-text-light mb-1">PIP #2</label>
                        <input type="text" x-model="settings.pip2_name" @change="saveSettings()"
                            placeholder="Optional..."
                            class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm"
                            :disabled="report?.status !== 'draft'">
                    </div>
                    <div>
                        <label class="block text-xs text-v2-text-light mb-1">Health #1</label>
                        <input type="text" x-model="settings.health1_name" @change="saveSettings()"
                            placeholder="Health insurance carrier..."
                            class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm"
                            :disabled="report?.status !== 'draft'">
                    </div>
                    <div>
                        <label class="block text-xs text-v2-text-light mb-1">Health #2</label>
                        <input type="text" x-model="settings.health2_name" @change="saveSettings()"
                            placeholder="Optional..."
                            class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm"
                            :disabled="report?.status !== 'draft'">
                    </div>
                </div>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" x-model="settings.has_wage_loss" @change="saveSettings()"
                            class="rounded" :disabled="report?.status !== 'draft'">
                        Wage Loss
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" x-model="settings.has_essential_service" @change="saveSettings()"
                            class="rounded" :disabled="report?.status !== 'draft'">
                        Essential Service
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" x-model="settings.has_health_subrogation" @change="saveSettings()"
                            class="rounded" :disabled="report?.status !== 'draft'">
                        Health Subrogation
                    </label>
                </div>
            </div>

            <!-- MBDS Table -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-v2-bg text-v2-text-mid text-xs uppercase">
                                <th class="px-3 py-2 text-left font-medium w-48">Provider</th>
                                <th class="px-2 py-2 text-right font-medium w-24">Charges</th>
                                <th class="px-2 py-2 text-right font-medium w-24" x-show="settings.pip1_name">PIP #1</th>
                                <th class="px-2 py-2 text-right font-medium w-24" x-show="settings.pip2_name">PIP #2</th>
                                <th class="px-2 py-2 text-right font-medium w-24" x-show="settings.health1_name">Health #1</th>
                                <th class="px-2 py-2 text-right font-medium w-24" x-show="settings.health2_name">Health #2</th>
                                <th class="px-2 py-2 text-right font-medium w-24">Discount</th>
                                <th class="px-2 py-2 text-right font-medium w-24">Office Paid</th>
                                <th class="px-2 py-2 text-right font-medium w-24">Client Paid</th>
                                <th class="px-2 py-2 text-right font-medium w-24">Balance</th>
                                <th class="px-0 py-2 text-center font-medium whitespace-nowrap" style="width:1%;white-space:nowrap">Dates</th>
                                <th class="px-1 py-2 text-center font-medium w-14">Visits</th>
                                <th class="px-2 py-2 text-left font-medium" style="min-width:200px">Note</th>
                                <th class="px-2 py-2 w-8" x-show="report?.status === 'draft'"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="line in lines" :key="line.id">
                                <tr class="border-t border-v2-card-border hover:bg-v2-bg/50"
                                    :class="{'bg-blue-50/50 font-medium': line.line_type !== 'provider' && line.line_type !== 'rx'}">
                                    <!-- Provider Name -->
                                    <td class="px-3 py-1">
                                        <span x-text="line.provider_name" class="text-sm"></span>
                                    </td>
                                    <!-- Charges -->
                                    <td class="px-1 py-1">
                                        <input type="number" step="0.01" x-model.number="line.charges"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- PIP #1 -->
                                    <td class="px-1 py-1" x-show="settings.pip1_name">
                                        <input type="number" step="0.01" x-model.number="line.pip1_amount"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- PIP #2 -->
                                    <td class="px-1 py-1" x-show="settings.pip2_name">
                                        <input type="number" step="0.01" x-model.number="line.pip2_amount"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Health #1 -->
                                    <td class="px-1 py-1" x-show="settings.health1_name">
                                        <input type="number" step="0.01" x-model.number="line.health1_amount"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Health #2 -->
                                    <td class="px-1 py-1" x-show="settings.health2_name">
                                        <input type="number" step="0.01" x-model.number="line.health2_amount"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Discount -->
                                    <td class="px-1 py-1">
                                        <input type="number" step="0.01" x-model.number="line.discount"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Office Paid -->
                                    <td class="px-1 py-1">
                                        <input type="number" step="0.01" x-model.number="line.office_paid"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Client Paid -->
                                    <td class="px-1 py-1">
                                        <input type="number" step="0.01" x-model.number="line.client_paid"
                                            @input="debounceSaveLine(line)"
                                            class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Balance (auto-calculated) -->
                                    <td class="px-2 py-1 text-right text-sm font-medium"
                                        :class="calcBalance(line) < 0 ? 'text-red-600' : (calcBalance(line) > 0 ? 'text-amber-600' : 'text-green-600')"
                                        x-text="'$' + calcBalance(line).toLocaleString('en-US', {minimumFractionDigits: 2})">
                                    </td>
                                    <!-- Treatment Dates -->
                                    <td class="px-0 py-1 whitespace-nowrap">
                                        <input type="text" x-model="line.treatment_dates"
                                            @input="formatDateInput($event, line)"
                                            @change="saveLine(line)"
                                            placeholder="MM/DD/YY-MM/DD/YY"
                                            class="px-0.5 py-1 text-center text-xs border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            style="width:160px"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Visits -->
                                    <td class="px-0 py-1">
                                        <input type="text" x-model="line.visits"
                                            @change="saveLine(line)"
                                            class="w-full px-1 py-1 text-center text-xs border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Note -->
                                    <td class="px-1 py-1">
                                        <input type="text" x-model="line.note"
                                            @change="saveLine(line)"
                                            placeholder=""
                                            class="w-full px-2 py-1 text-sm border border-transparent hover:border-v2-card-border focus:border-gold rounded outline-none"
                                            :disabled="report?.status !== 'draft'">
                                    </td>
                                    <!-- Delete -->
                                    <td class="px-1 py-1 text-center" x-show="report?.status === 'draft'">
                                        <button @click="deleteLine(line)" class="text-v2-text-light hover:text-red-500 p-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <!-- Total Row -->
                            <tr class="border-t-2 border-v2-card-border bg-v2-bg font-bold text-sm">
                                <td class="px-3 py-2">Total</td>
                                <td class="px-2 py-2 text-right" x-text="'$' + totals.charges.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-show="settings.pip1_name" x-text="'$' + totals.pip1.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-show="settings.pip2_name" x-text="'$' + totals.pip2.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-show="settings.health1_name" x-text="'$' + totals.health1.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-show="settings.health2_name" x-text="'$' + totals.health2.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-text="'$' + totals.discount.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-text="'$' + totals.officePaid.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right" x-text="'$' + totals.clientPaid.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td class="px-2 py-2 text-right"
                                    :class="totals.balance < 0 ? 'text-red-600' : (totals.balance > 0 ? 'text-amber-600' : 'text-green-600')"
                                    x-text="'$' + totals.balance.toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
            </div>

            <!-- Notes + Actions -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-v2-text-mid mb-1">Report Notes</label>
                    <textarea x-model="settings.notes" @change="saveSettings()" rows="2"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"
                        placeholder="General notes about this report..."
                        :disabled="report?.status !== 'draft'"></textarea>
                </div>
                <div class="flex gap-2 pt-5">
                    <template x-if="report?.status === 'draft'">
                        <div class="flex gap-2">
                            <button @click="addLine('rx')"
                                class="px-3 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg">
                                + Add RX
                            </button>
                            <button @click="addLine('provider')"
                                class="px-3 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg">
                                + Add Line
                            </button>
                            <button @click="markComplete()"
                                class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold/90">
                                Mark Complete
                            </button>
                        </div>
                    </template>
                    <template x-if="report?.status === 'completed'">
                        <div class="flex gap-2">
                            <button @click="reopenDraft()"
                                class="px-4 py-2 text-sm border border-orange-300 text-orange-600 rounded-lg hover:bg-orange-50">
                                Reopen as Draft
                            </button>
                            <button @click="approveReport()"
                                class="px-4 py-2 text-sm bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700">
                                Approve & Close
                            </button>
                        </div>
                    </template>
                    <template x-if="report?.status === 'approved'">
                        <span class="text-sm text-green-600 font-medium py-2">Approved by <span x-text="report.approved_by_name"></span></span>
                    </template>
                </div>
            </div>

            <!-- Saving indicator -->
            <div x-show="saving" x-transition class="fixed bottom-4 right-4 bg-navy text-white px-3 py-1.5 rounded-lg text-sm shadow-lg">
                Saving...
            </div>
        </div>
    </template>

    <!-- No report yet -->
    <template x-if="!loading && !caseData">
        <div class="text-center py-20 text-v2-text-light">Case not found</div>
    </template>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
