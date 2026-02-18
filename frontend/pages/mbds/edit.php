<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'MBDS Report';
$currentPage = 'mbds';
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

<script>
function mbdsEditPage() {
    return {
        caseId: getQueryParam('case_id'),
        caseData: null,
        report: null,
        lines: [],
        settings: {
            pip1_name: '', pip2_name: '', health1_name: '', health2_name: '',
            has_wage_loss: false, has_essential_service: false, has_health_subrogation: false,
            notes: ''
        },
        totals: { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 },
        loading: true,
        saving: false,
        _saveTimers: {},

        async init() {
            if (!this.caseId) {
                window.location.href = '/MRMS/frontend/pages/cases/index.php';
                return;
            }
            await this.loadReport();
            this.loading = false;
        },

        async loadReport() {
            try {
                const res = await api.get('mbds/' + this.caseId);
                this.report = res.data;
                this.caseData = {
                    case_number: res.data.case_number,
                    client_name: res.data.client_name,
                    doi: res.data.doi,
                    case_status: res.data.case_status
                };
                this.lines = res.data.lines || [];
                this.settings = {
                    pip1_name: res.data.pip1_name || '',
                    pip2_name: res.data.pip2_name || '',
                    health1_name: res.data.health1_name || '',
                    health2_name: res.data.health2_name || '',
                    has_wage_loss: !!res.data.has_wage_loss,
                    has_essential_service: !!res.data.has_essential_service,
                    has_health_subrogation: !!res.data.has_health_subrogation,
                    notes: res.data.notes || ''
                };
                this.recalcTotals();
            } catch (e) {
                // Report doesn't exist yet — create it
                if (e.response?.status === 404) {
                    await this.createReport();
                } else {
                    showToast('Failed to load report', 'error');
                }
            }
        },

        async createReport() {
            try {
                // First get case info
                const caseRes = await api.get('cases/' + this.caseId);
                this.caseData = {
                    case_number: caseRes.data.case_number,
                    client_name: caseRes.data.client_name,
                    doi: caseRes.data.doi,
                    case_status: caseRes.data.status
                };

                const res = await api.post('mbds/' + this.caseId);
                showToast('MBDS report created');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create report', 'error');
            }
        },

        async saveSettings() {
            if (!this.report) return;
            this.saving = true;
            try {
                await api.put('mbds/' + this.report.id, this.settings);
                // Reload to get updated lines (toggle may add/remove lines)
                await this.loadReport();
            } catch (e) {
                showToast('Failed to save settings', 'error');
            }
            this.saving = false;
        },

        formatDateInput(e, line) {
            const input = e.target;
            const digits = input.value.replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < digits.length && i < 12; i++) {
                if (i === 2 || i === 4 || i === 8 || i === 10) formatted += '/';
                if (i === 6) formatted += '-';
                formatted += digits[i];
            }
            line.treatment_dates = formatted;
            input.value = formatted;
        },

        calcBalance(line) {
            return Math.round(((line.charges || 0) - (line.pip1_amount || 0) - (line.pip2_amount || 0)
                - (line.health1_amount || 0) - (line.health2_amount || 0)
                - (line.discount || 0) - (line.office_paid || 0) - (line.client_paid || 0)) * 100) / 100;
        },

        recalcTotals() {
            const t = { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 };
            for (const l of this.lines) {
                t.charges += l.charges || 0;
                t.pip1 += l.pip1_amount || 0;
                t.pip2 += l.pip2_amount || 0;
                t.health1 += l.health1_amount || 0;
                t.health2 += l.health2_amount || 0;
                t.discount += l.discount || 0;
                t.officePaid += l.office_paid || 0;
                t.clientPaid += l.client_paid || 0;
            }
            t.balance = Math.round((t.charges - t.pip1 - t.pip2 - t.health1 - t.health2 - t.discount - t.officePaid - t.clientPaid) * 100) / 100;
            // Round all
            for (const k in t) t[k] = Math.round(t[k] * 100) / 100;
            this.totals = t;
        },

        debounceSaveLine(line) {
            this.recalcTotals();
            clearTimeout(this._saveTimers[line.id]);
            this._saveTimers[line.id] = setTimeout(() => this.saveLine(line), 500);
        },

        async saveLine(line) {
            this.saving = true;
            try {
                await api.put('mbds-lines/' + line.id, {
                    charges: line.charges || 0,
                    pip1_amount: line.pip1_amount || 0,
                    pip2_amount: line.pip2_amount || 0,
                    health1_amount: line.health1_amount || 0,
                    health2_amount: line.health2_amount || 0,
                    discount: line.discount || 0,
                    office_paid: line.office_paid || 0,
                    client_paid: line.client_paid || 0,
                    treatment_dates: line.treatment_dates || '',
                    visits: line.visits || '',
                    note: line.note || ''
                });
            } catch (e) {
                showToast('Failed to save', 'error');
            }
            this.saving = false;
        },

        async addLine(type) {
            try {
                const name = type === 'rx' ? 'RX' : prompt('Provider/line name:');
                if (!name) return;
                await api.post('mbds/' + this.report.id + '/lines', {
                    line_type: type,
                    provider_name: name
                });
                await this.loadReport();
            } catch (e) {
                showToast('Failed to add line', 'error');
            }
        },

        async deleteLine(line) {
            if (!confirm('Delete "' + line.provider_name + '"?')) return;
            try {
                await api.delete('mbds-lines/' + line.id);
                await this.loadReport();
                showToast('Line deleted');
            } catch (e) {
                showToast('Failed to delete', 'error');
            }
        },

        async markComplete() {
            if (!confirm('Mark this MBDS report as complete? This will move the case to Completed status.')) return;
            try {
                await api.post('mbds/' + this.report.id + '/complete');
                showToast('Report marked as completed');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        async approveReport() {
            if (!confirm('Approve this report and close the case?')) return;
            try {
                await api.post('mbds/' + this.report.id + '/approve');
                showToast('Report approved — case closed');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        async reopenDraft() {
            if (!confirm('Reopen this report as draft?')) return;
            try {
                await api.put('mbds/' + this.report.id, { status: 'draft' });
                await this.loadReport();
                showToast('Report reopened as draft');
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
