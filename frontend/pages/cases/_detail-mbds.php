            <!-- MBDS Report Section -->
            <div class="mbds-panel" data-panel :class="{'panel-open': mbdsOpen}" x-data="mbdsPanel(caseId)" x-init="init()">

                <!-- Report Header Bar -->
                <div class="mbds-header" @click="mbdsOpen = !mbdsOpen; if(mbdsOpen) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="mbds-header-left">
                        <svg class="mbds-collapse-chevron" :class="mbdsOpen ? 'open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="mbds-title">Medical Balance Report</span>
                        <template x-if="report">
                            <span class="mbds-badge"
                                :class="'mbds-badge-' + report.status"
                                x-text="getMbdsStatusLabel(report.status)"></span>
                        </template>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <template x-if="report && report.status === 'draft'">
                            <label @click.stop class="mbds-print-btn" style="cursor:pointer">
                                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import CSV
                                <input type="file" accept=".csv" style="display:none" @change="handleMbdsImportFile($event)">
                            </label>
                        </template>
                        <template x-if="report">
                            <button @click.stop="printMbds()" class="mbds-print-btn">
                                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Collapsible Body -->
                <div x-show="mbdsOpen" x-collapse>

                    <!-- Loading -->
                    <template x-if="loading">
                        <div class="mbds-loading">
                            <div class="spinner"></div>
                        </div>
                    </template>

                    <template x-if="!loading && report">
                        <div>

                            <!-- Insurance Settings -->
                            <div class="mbds-section-label">
                                <svg fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 0L12 4L8 8L4 4Z" transform="translate(0,4)"/>
                                </svg>
                                <span>Insurance Settings</span>
                            </div>
                            <div class="mbds-insurance-body">
                                <div class="mbds-insurance-grid">
                                    <div>
                                        <label class="mbds-field-label">PIP #1</label>
                                        <input type="text" x-model="settings.pip1_name" @change="saveSettings()"
                                            placeholder="Auto insurance carrier..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">PIP #2</label>
                                        <input type="text" x-model="settings.pip2_name" @change="saveSettings()"
                                            placeholder="Optional..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">Health #1</label>
                                        <input type="text" x-model="settings.health1_name" @change="saveSettings()"
                                            placeholder="Health insurance carrier..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">Health #2</label>
                                        <input type="text" x-model="settings.health2_name" @change="saveSettings()"
                                            placeholder="Optional..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                    <div>
                                        <label class="mbds-field-label">Health #3</label>
                                        <input type="text" x-model="settings.health3_name" @change="saveSettings()"
                                            placeholder="Optional..."
                                            class="mbds-field-input"
                                            :disabled="report?.status !== 'draft'">
                                    </div>
                                </div>
                                <div class="mbds-checkbox-row">
                                    <label class="mbds-checkbox-label" :class="settings.has_wage_loss ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_wage_loss" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Wage Loss
                                    </label>
                                    <label class="mbds-checkbox-label" :class="settings.has_essential_service ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_essential_service" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Essential Service
                                    </label>
                                    <label class="mbds-checkbox-label" :class="settings.has_health_subrogation ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_health_subrogation" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Health Subrogation #1
                                    </label>
                                    <label class="mbds-checkbox-label" :class="settings.has_health_subrogation2 ? 'checked' : ''">
                                        <input type="checkbox" x-model="settings.has_health_subrogation2" @change="saveSettings()"
                                            class="mbds-checkbox" :disabled="report?.status !== 'draft'">
                                        Health Subrogation #2
                                    </label>
                                </div>
                            </div>

                            <!-- MBDS Table -->
                            <div class="mbds-table-wrap">
                                <table class="mbds-table">
                                    <thead>
                                        <tr class="mbds-col-head">
                                            <th style="text-align:left; width:200px; min-width:200px; max-width:200px">Provider</th>
                                            <th class="mbds-th-r" style="width:115px; min-width:115px; max-width:115px">Charges</th>
                                            <th class="mbds-th-r" x-show="settings.pip1_name" style="width:115px; min-width:115px; max-width:115px">PIP #1</th>
                                            <th class="mbds-th-r" x-show="settings.pip2_name" style="width:115px; min-width:115px; max-width:115px">PIP #2</th>
                                            <th class="mbds-th-r" x-show="settings.health1_name" style="width:115px; min-width:115px; max-width:115px">Health #1</th>
                                            <th class="mbds-th-r" x-show="settings.health2_name" style="width:115px; min-width:115px; max-width:115px">Health #2</th>
                                            <th class="mbds-th-r" x-show="settings.health3_name" style="width:115px; min-width:115px; max-width:115px">Health #3</th>
                                            <th class="mbds-th-r" style="width:115px; min-width:115px; max-width:115px">Discount</th>
                                            <th class="mbds-th-r" style="width:115px; min-width:115px; max-width:115px">Office Paid</th>
                                            <th class="mbds-th-r" style="width:115px; min-width:115px; max-width:115px">Client Paid</th>
                                            <th class="mbds-th-balance" style="width:115px; min-width:115px; max-width:115px">Balance</th>
                                            <th class="mbds-th-c" style="width:160px; min-width:160px; max-width:160px">Dates</th>
                                            <th class="mbds-th-c" style="width:50px; min-width:50px; max-width:50px">Visits</th>
                                            <th style="text-align:left">Note</th>
                                            <th style="width:32px; min-width:32px; max-width:32px" x-show="report?.status === 'draft'"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="row in displayRows" :key="row._key">
                                            <tr :class="row._type === 'header' ? 'mbds-sec-row' : 'mbds-data-row'">

                                                <!-- Category Header -->
                                                <td x-show="row._type === 'header'" :colspan="totalCols">
                                                    <span x-text="row.label"></span>
                                                </td>

                                                <!-- Provider Name -->
                                                <td x-show="row._type === 'line'">
                                                    <span class="mbds-provider-name" x-text="row.provider_name"></span>
                                                </td>

                                                <!-- Charges -->
                                                <td x-show="row._type === 'line'" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'charges', row.charges)"
                                                        @focus="startCellEdit($el, row._lineRef, 'charges')"
                                                        @blur="endCellEdit($el, row._lineRef, 'charges')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.charges) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- PIP #1 -->
                                                <td x-show="row._type === 'line' && settings.pip1_name" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'pip1_amount', row.pip1_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'pip1_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'pip1_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.pip1_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- PIP #2 -->
                                                <td x-show="row._type === 'line' && settings.pip2_name" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'pip2_amount', row.pip2_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'pip2_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'pip2_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.pip2_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #1 -->
                                                <td x-show="row._type === 'line' && settings.health1_name" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health1_amount', row.health1_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health1_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health1_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.health1_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #2 -->
                                                <td x-show="row._type === 'line' && settings.health2_name" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health2_amount', row.health2_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health2_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health2_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.health2_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>
                                                <!-- Health #3 -->
                                                <td x-show="row._type === 'line' && settings.health3_name" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'health3_amount', row.health3_amount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'health3_amount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'health3_amount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.health3_amount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Discount -->
                                                <td x-show="row._type === 'line'" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'discount', row.discount)"
                                                        @focus="startCellEdit($el, row._lineRef, 'discount')"
                                                        @blur="endCellEdit($el, row._lineRef, 'discount')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.discount) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Office Paid -->
                                                <td x-show="row._type === 'line'" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'office_paid', row.office_paid)"
                                                        @focus="startCellEdit($el, row._lineRef, 'office_paid')"
                                                        @blur="endCellEdit($el, row._lineRef, 'office_paid')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.office_paid) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Client Paid -->
                                                <td x-show="row._type === 'line'" style="padding:4px 6px">
                                                    <input type="text"
                                                        :value="cellVal(row.id, 'client_paid', row.client_paid)"
                                                        @focus="startCellEdit($el, row._lineRef, 'client_paid')"
                                                        @blur="endCellEdit($el, row._lineRef, 'client_paid')"
                                                        @keyup.enter="$el.blur()"
                                                        class="mbds-cell-input"
                                                        :class="(Number(row.client_paid) || 0) === 0 ? 'mbds-zero-val' : ''"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Balance -->
                                                <td x-show="row._type === 'line'" style="padding:10px 14px;text-align:right">
                                                    <span class="mbds-balance"
                                                        :class="balanceColor(calcBalance(row))"
                                                        x-text="formatCurrency(calcBalance(row))">
                                                    </span>
                                                </td>

                                                <!-- Treatment Dates -->
                                                <td x-show="row._type === 'line'" style="padding:4px 2px">
                                                    <input type="text" :value="row.treatment_dates || ''"
                                                        @input="formatDateInput($event, row._lineRef)"
                                                        @change="saveLine(row._lineRef)"
                                                        placeholder="MM/DD/YY–MM/DD/YY"
                                                        class="mbds-date-input"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Visits -->
                                                <td x-show="row._type === 'line'" style="padding:4px 2px">
                                                    <input type="text" x-model="row._lineRef.visits"
                                                        @change="saveLine(row._lineRef)"
                                                        class="mbds-visits-input"
                                                        :disabled="report?.status !== 'draft'">
                                                </td>

                                                <!-- Note -->
                                                <td x-show="row._type === 'line'" style="padding:4px 16px 4px 6px">
                                                    <div @click="openNote($event, row.id)"
                                                        class="mbds-note-trigger"
                                                        :class="row.note ? 'has-note' : ''"
                                                        :title="row.note || ''"
                                                        x-text="row.note || '—'">
                                                    </div>
                                                </td>

                                                <!-- Delete -->
                                                <td x-show="row._type === 'line' && report?.status === 'draft'" style="padding:4px;text-align:center">
                                                    <button @click="deleteLine(row._lineRef)" class="icon-btn icon-btn-danger icon-btn-sm">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr class="mbds-total-row">
                                            <td class="mbds-total-label">TOTAL</td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.charges)"></td>
                                            <td class="mbds-total-r" x-show="settings.pip1_name" x-text="formatCurrency(totals.pip1)"></td>
                                            <td class="mbds-total-r" x-show="settings.pip2_name" x-text="formatCurrency(totals.pip2)"></td>
                                            <td class="mbds-total-r" x-show="settings.health1_name" x-text="formatCurrency(totals.health1)"></td>
                                            <td class="mbds-total-r" x-show="settings.health2_name" x-text="formatCurrency(totals.health2)"></td>
                                            <td class="mbds-total-r" x-show="settings.health3_name" x-text="formatCurrency(totals.health3)"></td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.discount)"></td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.officePaid)"></td>
                                            <td class="mbds-total-r" x-text="formatCurrency(totals.clientPaid)"></td>
                                            <td class="mbds-total-r mbds-total-balance" x-text="formatCurrency(totals.balance)"></td>
                                            <td colspan="4" style="background:var(--navy)"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Bottom Bar — Notes + Actions -->
                            <div class="mbds-bottom-bar">
                                <div style="flex:1">
                                    <div class="mbds-notes-label">Report Notes</div>
                                    <textarea x-model="settings.notes" @change="saveSettings()"
                                        class="mbds-notes-textarea"
                                        placeholder="General notes about this report..."
                                        :disabled="report?.status !== 'draft'"></textarea>
                                </div>
                                <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:2px">
                                    <template x-if="report?.status === 'draft'">
                                        <div style="display:flex;gap:8px">
                                            <button @click="addLine('rx')" class="mbds-btn-ghost">+ Add RX</button>
                                            <button @click="addLine('provider')" class="mbds-btn-ghost">+ Add Line</button>
                                            <button @click="markComplete()" class="mbds-btn-gold">Mark Complete</button>
                                        </div>
                                    </template>
                                    <template x-if="report?.status === 'completed'">
                                        <div style="display:flex;gap:8px">
                                            <button @click="reopenDraft()" class="mbds-btn-outline-red">Reopen as Draft</button>
                                            <button @click="approveReport()" class="mbds-btn-green">Approve & Close</button>
                                        </div>
                                    </template>
                                    <template x-if="report?.status === 'approved'">
                                        <span style="font-size:12px;color:var(--mbds-green);font-weight:500;padding:8px 0">
                                            Approved by <span x-text="report.approved_by_name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Note popover -->
                            <template x-for="row in displayRows.filter(r => r._type === 'line')" :key="'mbds_notepop_' + row._key">
                                <div x-show="expandedNote === row.id"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    @click.outside="expandedNote = null"
                                    class="mbds-note-popover"
                                    :style="{ top: notePopoverPos.top, right: notePopoverPos.right }"
                                    @click.stop>
                                    <div class="mbds-note-popover-header">
                                        <span>Note</span>
                                        <button @click="expandedNote = null" style="background:none;border:none;cursor:pointer;color:var(--mbds-muted);padding:2px">
                                            <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div style="padding:8px">
                                        <textarea x-model="row._lineRef.note"
                                            @input="debounceSaveLine(row._lineRef)"
                                            rows="4"
                                            placeholder="Add note..."
                                            :disabled="report?.status !== 'draft'"></textarea>
                                    </div>
                                </div>
                            </template>

                            <!-- Saving indicator -->
                            <div x-show="saving" x-transition class="mbds-saving">
                                <div class="mbds-saving-dot"></div>
                                Saving...
                            </div>
                        </div>
                    </template>

                    <!-- No report fallback -->
                    <template x-if="!loading && !report">
                        <div style="text-align:center;color:var(--mbds-muted);padding:32px 0;font-size:13px">Failed to load Medical Balance report</div>
                    </template>
                </div>

                <!-- MBDS Import Preview Modal -->
                <div x-show="showMbdsImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
                    <div class="modal-v2-backdrop fixed inset-0" @click="showMbdsImportModal = false"></div>
                    <div class="modal-v2 relative w-full max-w-4xl z-10" @click.stop>
                        <div class="modal-v2-header">
                            <h3 class="modal-v2-title">Import Medical Balance Preview</h3>
                            <button type="button" class="modal-v2-close" @click="showMbdsImportModal = false">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="modal-v2-body">
                            <div class="flex gap-4 mb-4">
                                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                                    <p class="text-lg font-bold text-v2-text" x-text="mbdsImportSummary.count || 0"></p>
                                    <p class="text-[10px] text-v2-text-light">Lines</p>
                                </div>
                                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                                    <p class="text-lg font-bold text-v2-text" x-text="formatCurrency(mbdsImportSummary.total_charges || 0)"></p>
                                    <p class="text-[10px] text-v2-text-light">Total Charges</p>
                                </div>
                                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                                    <p class="text-lg font-bold" x-text="formatCurrency(mbdsImportSummary.total_pip1 || 0)" style="color:var(--navy)"></p>
                                    <p class="text-[10px] text-v2-text-light">Total PIP #1</p>
                                </div>
                                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                                    <p class="text-lg font-bold" x-text="formatCurrency(mbdsImportSummary.total_balance || 0)"
                                        :class="(mbdsImportSummary.total_balance || 0) > 0 ? 'text-amber-600' : 'text-green-600'"></p>
                                    <p class="text-[10px] text-v2-text-light">Total Balance</p>
                                </div>
                            </div>

                            <template x-if="lines.length > 0">
                                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 mb-4 text-sm text-amber-800">
                                    <strong>Warning:</strong> This will replace all <span x-text="lines.length"></span> existing Medical Balance lines with the imported data.
                                </div>
                            </template>

                            <div class="max-h-80 overflow-y-auto border border-v2-card-border rounded-lg">
                                <table class="w-full text-xs">
                                    <thead class="sticky top-0 bg-white">
                                        <tr class="border-b border-v2-card-border">
                                            <th class="text-left px-3 py-2">Type</th>
                                            <th class="text-left px-3 py-2">Provider</th>
                                            <th class="text-right px-3 py-2">Charges</th>
                                            <th class="text-right px-3 py-2">PIP #1</th>
                                            <th class="text-right px-3 py-2">Discount</th>
                                            <th class="text-right px-3 py-2">Balance</th>
                                            <th class="text-left px-3 py-2">Dates</th>
                                            <th class="text-center px-3 py-2">Visits</th>
                                            <th class="text-center px-3 py-2">Matched</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, idx) in mbdsImportPreview" :key="idx">
                                            <tr class="border-b border-v2-bg">
                                                <td class="px-3 py-1.5">
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                        :class="row.line_type === 'provider' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                                        x-text="row.line_type.replace('_',' ').toUpperCase()"></span>
                                                </td>
                                                <td class="px-3 py-1.5 font-medium" x-text="row.provider_name"></td>
                                                <td class="px-3 py-1.5 text-right" x-text="formatCurrency(row.charges)"></td>
                                                <td class="px-3 py-1.5 text-right" x-text="formatCurrency(row.pip1_amount)"></td>
                                                <td class="px-3 py-1.5 text-right" x-text="formatCurrency(row.discount)"></td>
                                                <td class="px-3 py-1.5 text-right font-semibold"
                                                    :class="row.balance > 0 ? 'text-amber-600' : (row.balance < 0 ? 'text-red-600' : 'text-green-600')"
                                                    x-text="formatCurrency(row.balance)"></td>
                                                <td class="px-3 py-1.5 text-xs" x-text="row.treatment_dates || '-'"></td>
                                                <td class="px-3 py-1.5 text-center" x-text="row.visits || '-'"></td>
                                                <td class="px-3 py-1.5 text-center">
                                                    <span x-show="row.matched_provider" class="text-green-600">&#10003;</span>
                                                    <span x-show="!row.matched_provider && row.line_type === 'provider'" class="text-v2-text-light">-</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-v2-footer">
                            <button type="button" @click="showMbdsImportModal = false" class="btn-v2-cancel">Cancel</button>
                            <button type="button" @click="confirmMbdsImport()" :disabled="mbdsImporting"
                                    class="btn-v2-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span x-text="mbdsImporting ? 'Importing...' : 'Import ' + (mbdsImportSummary.count || 0) + ' Lines'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
