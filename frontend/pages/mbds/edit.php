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
                            <h2 class="text-2xl font-bold text-navy">MBDS</h2>
                            <span class="status-badge text-xs px-2.5 py-1" :class="'status-' + caseData.case_status"
                                x-text="getStatusLabel(caseData.case_status)"></span>
                            <template x-if="report && report.status === 'draft'">
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold" style="background:rgba(201,168,76,0.15); color:#9A7F2E;">Draft</span>
                            </template>
                            <template x-if="report && report.status === 'completed'">
                                <span class="text-xs px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-semibold">Completed</span>
                            </template>
                            <template x-if="report && report.status === 'approved'">
                                <span class="text-xs px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">Approved</span>
                            </template>
                        </div>
                        <p class="text-sm text-v2-text-light">
                            <span x-text="caseData.case_number"></span> &mdash; <span x-text="caseData.client_name"></span>
                            <template x-if="caseData.doi">
                                <span> | DOI: <span x-text="formatDate(caseData.doi)"></span></span>
                            </template>
                        </p>
                    </div>
                </div>
                <button @click="printMbds()" title="Print MBDS"
                    class="border border-v2-card-border text-v2-text-mid px-3 py-1.5 rounded-lg text-sm hover:bg-v2-bg flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>

            <!-- Insurance Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden mb-4">
                <div class="px-5 py-2.5 flex items-center gap-2 bg-navy/5 border-b border-v2-card-border">
                    <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-navy">Insurance Settings</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-5 gap-4 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-navy mb-1">PIP #1</label>
                            <input type="text" x-model="settings.pip1_name" @change="saveSettings()"
                                placeholder="Auto insurance carrier..."
                                class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition"
                                :disabled="report?.status !== 'draft'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy mb-1">PIP #2</label>
                            <input type="text" x-model="settings.pip2_name" @change="saveSettings()"
                                placeholder="Optional..."
                                class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition"
                                :disabled="report?.status !== 'draft'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy mb-1">Health #1</label>
                            <input type="text" x-model="settings.health1_name" @change="saveSettings()"
                                placeholder="Health insurance carrier..."
                                class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition"
                                :disabled="report?.status !== 'draft'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy mb-1">Health #2</label>
                            <input type="text" x-model="settings.health2_name" @change="saveSettings()"
                                placeholder="Optional..."
                                class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition"
                                :disabled="report?.status !== 'draft'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-navy mb-1">Health #3</label>
                            <input type="text" x-model="settings.health3_name" @change="saveSettings()"
                                placeholder="Optional..."
                                class="w-full px-3 py-1.5 border border-v2-card-border rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition"
                                :disabled="report?.status !== 'draft'">
                        </div>
                    </div>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="settings.has_wage_loss" @change="saveSettings()"
                                class="rounded border-gray-300 text-gold focus:ring-gold" :disabled="report?.status !== 'draft'">
                            Wage Loss
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="settings.has_essential_service" @change="saveSettings()"
                                class="rounded border-gray-300 text-gold focus:ring-gold" :disabled="report?.status !== 'draft'">
                            Essential Service
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="settings.has_health_subrogation" @change="saveSettings()"
                                class="rounded border-gray-300 text-gold focus:ring-gold" :disabled="report?.status !== 'draft'">
                            Health Subrogation #1
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="settings.has_health_subrogation2" @change="saveSettings()"
                                class="rounded border-gray-300 text-gold focus:ring-gold" :disabled="report?.status !== 'draft'">
                            Health Subrogation #2
                        </label>
                    </div>
                </div>
            </div>

            <!-- MBDS Table -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden mb-4">
                <table class="w-full text-sm">
                    <thead>
                        <!-- Row 1: Group headers -->
                        <tr>
                            <th rowspan="2" class="px-3 py-2 text-left text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-44">Provider</th>
                            <th rowspan="2" class="px-2 py-2 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24">Charges</th>
                            <th x-show="insuranceColspan > 0" :colspan="insuranceColspan"
                                class="px-2 py-1.5 text-center text-xs font-bold text-v2-text-light uppercase tracking-wider border-b border-r border-v2-card-border">
                                Insurance
                            </th>
                            <th class="px-2 py-1.5 text-center text-xs font-bold text-v2-text-light uppercase tracking-wider border-b border-r border-v2-card-border">
                                Adjustment
                            </th>
                            <th colspan="2" class="px-2 py-1.5 text-center text-xs font-bold text-v2-text-light uppercase tracking-wider border-b border-r border-v2-card-border">
                                Payments
                            </th>
                            <th rowspan="2" class="px-2 py-2 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24">Balance</th>
                            <th rowspan="2" class="px-0 py-2 text-center text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border whitespace-nowrap" style="width:1%">Dates</th>
                            <th rowspan="2" class="px-1 py-2 text-center text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-14">Visits</th>
                            <th rowspan="2" class="px-2 py-2 text-left text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border" style="width:140px;max-width:140px">Note</th>
                            <th rowspan="2" class="px-2 py-2 w-8 border-b-2 border-v2-card-border" x-show="report?.status === 'draft'"></th>
                        </tr>
                        <!-- Row 2: Individual column headers -->
                        <tr>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24" x-show="settings.pip1_name">PIP #1</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24" x-show="settings.pip2_name">PIP #2</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24" x-show="settings.health1_name">Health #1</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24" x-show="settings.health2_name">Health #2</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24" x-show="settings.health3_name">Health #3</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24">Discount</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24">Office Paid</th>
                            <th class="px-2 py-1.5 text-right text-xs font-bold text-v2-text-light uppercase tracking-wider border-b-2 border-r border-v2-card-border w-24">Client Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in displayRows" :key="row._key">
                            <tr :class="row._type === 'header'
                                ? 'bg-navy/5 border-t-2 border-navy/20'
                                : 'border-t border-v2-card-border hover:bg-gold/5 transition-colors'">

                                <!-- ===== Category Header Row ===== -->
                                <td x-show="row._type === 'header'" :colspan="totalCols"
                                    class="px-3 py-1.5 text-[11px] font-bold text-navy/70 uppercase tracking-wider">
                                    <span x-text="row.label"></span>
                                </td>

                                <!-- ===== Data Row: Provider Name ===== -->
                                <td x-show="row._type === 'line'" class="px-3 py-1 border-r border-v2-card-border">
                                    <span x-text="row.provider_name" class="text-sm font-medium text-navy"></span>
                                </td>

                                <!-- Charges -->
                                <td x-show="row._type === 'line'" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'charges', row.charges)"
                                        @focus="startCellEdit($el, row._lineRef, 'charges')"
                                        @blur="endCellEdit($el, row._lineRef, 'charges')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- PIP #1 -->
                                <td x-show="row._type === 'line' && settings.pip1_name" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'pip1_amount', row.pip1_amount)"
                                        @focus="startCellEdit($el, row._lineRef, 'pip1_amount')"
                                        @blur="endCellEdit($el, row._lineRef, 'pip1_amount')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>
                                <!-- PIP #2 -->
                                <td x-show="row._type === 'line' && settings.pip2_name" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'pip2_amount', row.pip2_amount)"
                                        @focus="startCellEdit($el, row._lineRef, 'pip2_amount')"
                                        @blur="endCellEdit($el, row._lineRef, 'pip2_amount')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>
                                <!-- Health #1 -->
                                <td x-show="row._type === 'line' && settings.health1_name" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'health1_amount', row.health1_amount)"
                                        @focus="startCellEdit($el, row._lineRef, 'health1_amount')"
                                        @blur="endCellEdit($el, row._lineRef, 'health1_amount')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>
                                <!-- Health #2 -->
                                <td x-show="row._type === 'line' && settings.health2_name" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'health2_amount', row.health2_amount)"
                                        @focus="startCellEdit($el, row._lineRef, 'health2_amount')"
                                        @blur="endCellEdit($el, row._lineRef, 'health2_amount')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>
                                <!-- Health #3 -->
                                <td x-show="row._type === 'line' && settings.health3_name" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'health3_amount', row.health3_amount)"
                                        @focus="startCellEdit($el, row._lineRef, 'health3_amount')"
                                        @blur="endCellEdit($el, row._lineRef, 'health3_amount')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- Discount -->
                                <td x-show="row._type === 'line'" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'discount', row.discount)"
                                        @focus="startCellEdit($el, row._lineRef, 'discount')"
                                        @blur="endCellEdit($el, row._lineRef, 'discount')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- Office Paid -->
                                <td x-show="row._type === 'line'" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'office_paid', row.office_paid)"
                                        @focus="startCellEdit($el, row._lineRef, 'office_paid')"
                                        @blur="endCellEdit($el, row._lineRef, 'office_paid')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- Client Paid -->
                                <td x-show="row._type === 'line'" class="px-1 py-1 border-r border-v2-card-border">
                                    <input type="text"
                                        :value="cellVal(row.id, 'client_paid', row.client_paid)"
                                        @focus="startCellEdit($el, row._lineRef, 'client_paid')"
                                        @blur="endCellEdit($el, row._lineRef, 'client_paid')"
                                        @keyup.enter="$el.blur()"
                                        class="w-full px-2 py-1 text-right text-sm border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- Balance (auto-calculated) -->
                                <td x-show="row._type === 'line'" class="px-2 py-1 text-right text-sm font-semibold border-r border-v2-card-border"
                                    :class="calcBalance(row) < 0 ? 'text-red-600' : (calcBalance(row) > 0 ? 'text-amber-600' : 'text-green-600')"
                                    x-text="formatCurrency(calcBalance(row))">
                                </td>

                                <!-- Treatment Dates -->
                                <td x-show="row._type === 'line'" class="px-0 py-1 whitespace-nowrap border-r border-v2-card-border">
                                    <input type="text" :value="row.treatment_dates || ''"
                                        @input="formatDateInput($event, row._lineRef)"
                                        @change="saveLine(row._lineRef)"
                                        placeholder="MM/DD/YY-MM/DD/YY"
                                        class="px-0.5 py-1 text-center text-xs border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        style="width:150px"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- Visits -->
                                <td x-show="row._type === 'line'" class="px-0 py-1 border-r border-v2-card-border">
                                    <input type="text" x-model="row._lineRef.visits"
                                        @change="saveLine(row._lineRef)"
                                        class="w-full px-1 py-1 text-center text-xs border border-transparent hover:border-v2-card-border focus:border-gold focus:ring-1 focus:ring-gold/30 rounded outline-none transition"
                                        :disabled="report?.status !== 'draft'">
                                </td>

                                <!-- Note (narrow + click-to-expand) -->
                                <td x-show="row._type === 'line'" class="px-1 py-1 border-r border-v2-card-border" style="max-width:140px;width:140px">
                                    <div @click="openNote($event, row.id)"
                                        class="truncate cursor-pointer text-sm px-2 py-1 rounded hover:bg-navy/5 transition-colors"
                                        :class="row.note ? 'text-v2-text' : 'text-v2-text-light'"
                                        :title="row.note || ''"
                                        x-text="row.note || '—'">
                                    </div>
                                </td>

                                <!-- Delete -->
                                <td x-show="row._type === 'line' && report?.status === 'draft'" class="px-1 py-1 text-center">
                                    <button @click="deleteLine(row._lineRef)" class="text-v2-text-light hover:text-red-500 p-1 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <!-- Total Row -->
                        <tr class="font-bold text-sm text-navy bg-navy/5 border-t-2 border-navy/20">
                            <td class="px-3 py-2.5 border-r border-v2-card-border">Total</td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-text="formatCurrency(totals.charges)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-show="settings.pip1_name" x-text="formatCurrency(totals.pip1)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-show="settings.pip2_name" x-text="formatCurrency(totals.pip2)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-show="settings.health1_name" x-text="formatCurrency(totals.health1)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-show="settings.health2_name" x-text="formatCurrency(totals.health2)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-show="settings.health3_name" x-text="formatCurrency(totals.health3)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-text="formatCurrency(totals.discount)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-text="formatCurrency(totals.officePaid)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border" x-text="formatCurrency(totals.clientPaid)"></td>
                            <td class="px-2 py-2.5 text-right border-r border-v2-card-border"
                                :class="totals.balance > 0 ? 'text-amber-600' : (totals.balance < 0 ? 'text-red-600' : 'text-green-600')"
                                x-text="formatCurrency(totals.balance)"></td>
                            <td colspan="4"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Notes + Actions -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-navy mb-1">Report Notes</label>
                    <textarea x-model="settings.notes" @change="saveSettings()" rows="2"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition"
                        placeholder="General notes about this report..."
                        :disabled="report?.status !== 'draft'"></textarea>
                </div>
                <div class="flex gap-2 pt-5">
                    <template x-if="report?.status === 'draft'">
                        <div class="flex gap-2">
                            <button @click="addLine('rx')"
                                class="px-3 py-2 text-sm font-medium border border-navy/20 text-navy rounded-lg hover:bg-navy/5 transition-colors">
                                + Add RX
                            </button>
                            <button @click="addLine('provider')"
                                class="px-3 py-2 text-sm font-medium border border-navy/20 text-navy rounded-lg hover:bg-navy/5 transition-colors">
                                + Add Line
                            </button>
                            <button @click="markComplete()"
                                class="px-4 py-2 text-sm bg-gold text-navy font-semibold rounded-lg hover:bg-gold-hover transition-colors">
                                Mark Complete
                            </button>
                        </div>
                    </template>
                    <template x-if="report?.status === 'completed'">
                        <div class="flex gap-2">
                            <button @click="reopenDraft()"
                                class="px-4 py-2 text-sm border border-orange-300 text-orange-600 rounded-lg hover:bg-orange-50 transition-colors">
                                Reopen as Draft
                            </button>
                            <button @click="approveReport()"
                                class="px-4 py-2 text-sm bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                Approve & Close
                            </button>
                        </div>
                    </template>
                    <template x-if="report?.status === 'approved'">
                        <span class="text-sm text-green-600 font-medium py-2">Approved by <span x-text="report.approved_by_name"></span></span>
                    </template>
                </div>
            </div>

            <!-- Note popover (fixed position, outside overflow-hidden) -->
            <template x-for="row in displayRows.filter(r => r._type === 'line')" :key="'notepop_' + row._key">
                <div x-show="expandedNote === row.id"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    @click.outside="expandedNote = null"
                    class="fixed z-50 w-80 bg-white border border-v2-card-border shadow-xl rounded-lg overflow-hidden"
                    :style="{ top: notePopoverPos.top, right: notePopoverPos.right }"
                    @click.stop>
                    <div class="px-3 py-2 bg-navy/5 border-b border-v2-card-border flex items-center justify-between">
                        <span class="text-xs font-semibold text-navy">Note</span>
                        <button @click="expandedNote = null" class="text-v2-text-light hover:text-navy">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-2">
                        <textarea x-model="row._lineRef.note"
                            @input="debounceSaveLine(row._lineRef)"
                            rows="4"
                            class="w-full text-sm border border-v2-card-border rounded-lg p-2 focus:border-gold focus:ring-1 focus:ring-gold/30 outline-none transition resize-y"
                            placeholder="Add note..."
                            :disabled="report?.status !== 'draft'"></textarea>
                    </div>
                </div>
            </template>

            <!-- Saving indicator -->
            <div x-show="saving" x-transition class="fixed bottom-4 right-4 bg-navy text-white px-3 py-1.5 rounded-lg text-sm shadow-lg flex items-center gap-2">
                <div class="w-3 h-3 border-2 border-white/30 border-t-gold rounded-full animate-spin"></div>
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
