
<style>
.disb-panel {
    --navy: var(--sidebar, #0F1B2D);
    --navy-light: var(--sidebar-light, #1A2A40);
    --navy-border: var(--sidebar-border, #243347);
    --gold: #C9A84C;
    --gold-hover: #B8973F;
    --gold-light: #E8D5A0;
    --off-white: #fdfdfb;
    --border: #e8e4dc;
    --border-soft: #f0ede6;
    --mbds-muted: #8a8a82;
    --mbds-text: #1a2535;
    --mbds-red: #b83232;
    --mbds-green: #2a6b4a;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 13px;
    color: var(--mbds-text);
    border: 1px solid var(--border);
    border-left: 3px solid var(--gold);
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(15,27,45,0.04);
    overflow: hidden;
    margin-bottom: 24px;
    background: #fff;
}
.disb-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}
.disb-header:hover { background: var(--off-white); }
.disb-header-left { display: flex; align-items: center; gap: 10px; }
.disb-header-title { color: var(--mbds-text); font-size: 14px; font-weight: 600; letter-spacing: 0.3px; }
.disb-chevron { color: var(--mbds-muted); transition: transform 0.2s; }
.disb-body { background: var(--off-white); padding: 16px 20px; }

/* Summary cards */
.disb-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.disb-summary-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 16px;
    text-align: center;
}
.disb-summary-card.highlight {
    background: var(--navy);
    border-color: var(--gold);
}
.disb-summary-label {
    font-size: 10px;
    font-weight: 600;
    color: var(--mbds-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.disb-summary-card.highlight .disb-summary-label { color: var(--gold); }
.disb-summary-value {
    font-size: 20px;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
    color: var(--mbds-text);
}
.disb-summary-card.highlight .disb-summary-value { color: #fff; }

/* Settings section */
.disb-settings {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}
.disb-settings-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
}
.disb-settings-row:last-child { margin-bottom: 0; }
.disb-settings-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--mbds-text);
    min-width: 120px;
}
.disb-fee-btn {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--mbds-muted);
    font-family: 'IBM Plex Sans', sans-serif;
    transition: all 0.15s;
}
.disb-fee-btn.active {
    background: var(--gold);
    color: #fff;
    border-color: var(--gold);
}
.disb-fee-btn:hover:not(.active) { border-color: var(--gold); color: var(--gold); }
.disb-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    font-size: 12px;
}
.disb-checkbox input { accent-color: var(--gold); }
.disb-amount-input {
    width: 140px;
    padding: 6px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-size: 13px;
    font-family: 'IBM Plex Mono', monospace;
    background: var(--off-white);
}
.disb-amount-input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 2px rgba(201,168,76,0.15);
}

/* Method comparison cards */
.disb-method-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}
.disb-method-card {
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.15s;
    background: #fff;
}
.disb-method-card:hover { border-color: var(--gold); }
.disb-method-card.selected {
    border-color: var(--gold);
    box-shadow: 0 0 0 2px rgba(201,168,76,0.2);
}
.disb-method-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 4px;
}
.disb-method-desc {
    font-size: 11px;
    color: var(--mbds-muted);
    margin-bottom: 12px;
}
.disb-method-detail {
    font-size: 11px;
    color: var(--mbds-muted);
    display: flex;
    justify-content: space-between;
    padding: 3px 0;
    border-bottom: 1px dotted var(--border);
}
.disb-method-detail:last-child { border-bottom: none; }
.disb-method-net {
    font-size: 16px;
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 2px solid var(--border);
}

/* Disbursement statement table */
.disb-statement {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}
.disb-statement-header {
    background: #b5a070;
    color: #fff;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.disb-statement table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.disb-statement td {
    padding: 6px 16px;
    border-bottom: 1px solid var(--border);
}
.disb-statement tr:last-child td { border-bottom: none; }
.disb-section-row td {
    background: var(--off-white);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--mbds-muted);
    padding: 6px 16px;
}
.disb-total-row td {
    background: var(--navy);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    padding: 10px 16px;
    font-family: 'IBM Plex Mono', monospace;
}
.disb-amount {
    text-align: right;
    font-family: 'IBM Plex Mono', monospace;
    white-space: nowrap;
}
.disb-amount.negative { color: var(--mbds-red); }
.disb-amount.positive { color: var(--mbds-green); }

/* Print button */
.disb-print-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--mbds-text);
    font-family: 'IBM Plex Sans', sans-serif;
}
.disb-print-btn:hover { border-color: var(--gold); color: var(--gold); }
.disb-method-print {
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--mbds-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    transition: all 0.15s;
}
.disb-method-print:hover { color: var(--gold); border-color: var(--gold); background: rgba(201,168,76,0.06); }
</style>

<div class="disb-panel" data-panel :class="{'panel-open': open}" x-data="disbursementPanel(caseId)" x-init="init()">
    <!-- Header -->
    <div class="disb-header" @click="open = !open; if(open) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="disb-header-left">
            <svg class="disb-chevron w-4 h-4" :style="open ? 'transform:rotate(90deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="disb-header-title">Settlement & Disbursement</span>
            <template x-if="calculated && calculated.clientNet !== 0">
                <span style="color:var(--gold); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:600;"
                    x-text="'Client Net: $' + calculated.clientNet.toLocaleString('en-US', {minimumFractionDigits:2})"></span>
            </template>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button @click.stop="printDisbursement()" class="disb-print-btn">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>
    </div>

    <!-- Body -->
    <div x-show="open" x-collapse>
        <div class="disb-body">
            <template x-if="loading">
                <div style="text-align:center; padding:24px; color:var(--mbds-muted);">Loading...</div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Summary Cards -->
                    <div class="disb-summary-grid">
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Settlement</div>
                            <div class="disb-summary-value" x-text="fmtMoney(calculated?.gross || 0)"></div>
                        </div>
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Attorney Fees</div>
                            <div class="disb-summary-value" style="color:var(--mbds-red);" x-text="fmtMoney(calculated?.fee || 0)"></div>
                        </div>
                        <div class="disb-summary-card">
                            <div class="disb-summary-label">Deductions</div>
                            <div class="disb-summary-value" style="color:var(--mbds-red);" x-text="fmtMoney(calculated?.totalDeductions || 0)"></div>
                        </div>
                        <div class="disb-summary-card highlight">
                            <div class="disb-summary-label">Client Net</div>
                            <div class="disb-summary-value" x-text="fmtMoney(calculated?.clientNet || 0)"></div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="disb-settings">
                        <div class="disb-settings-row">
                            <span class="disb-settings-label">Attorney Fee:</span>
                            <button class="disb-fee-btn" :class="settings.attorney_fee_percent < 0.34 ? 'active' : ''"
                                @click="setFeePercent(1/3)">&#8531; 33.33%</button>
                            <button class="disb-fee-btn" :class="settings.attorney_fee_percent >= 0.34 ? 'active' : ''"
                                @click="setFeePercent(0.4)">40%</button>
                        </div>
                        <div class="disb-settings-row">
                            <span class="disb-settings-label">Coverage:</span>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.coverage_3rd_party" @change="onSettingsChange()">
                                3rd Party
                            </label>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.coverage_um" @change="onSettingsChange()">
                                UM
                            </label>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.coverage_uim" @change="onSettingsChange()">
                                UIM
                            </label>
                        </div>
                        <div class="disb-settings-row">
                            <span class="disb-settings-label">Policy Limit:</span>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.policy_limit" @change="onSettingsChange()">
                                3rd Party Limit
                            </label>
                            <label class="disb-checkbox">
                                <input type="checkbox" x-model="settings.um_uim_limit" @change="onSettingsChange()">
                                UM/UIM Limit
                            </label>
                        </div>
                        <div class="disb-settings-row">
                            <span class="disb-settings-label">PIP Subrogation:</span>
                            <input type="number" step="0.01" class="disb-amount-input"
                                x-model.number="settings.pip_subrogation_amount"
                                @change="onSettingsChange()" placeholder="0.00">
                            <span style="font-size:12px; color:var(--mbds-muted);">via</span>
                            <input type="text" class="disb-amount-input" style="width:180px;"
                                x-model="settings.pip_insurance_company"
                                @change="onSettingsChange()" :placeholder="pipInfo?.pip1_name || 'PIP Insurance Company'">
                        </div>
                    </div>

                    <!-- Method Comparison -->
                    <template x-if="showMahler() || showHamm()">
                        <div class="disb-method-grid" :style="showMahler() && showHamm() ? '' : 'grid-template-columns:1fr'">
                            <!-- Mahler: 3rd Party + PIP -->
                            <template x-if="showMahler()">
                                <div class="disb-method-card" :class="settings.settlement_method === 'mahler' ? 'selected' : ''"
                                    @click="selectMethod('mahler')">
                                    <div class="disb-method-name">Mahler Method</div>
                                    <div class="disb-method-desc">3rd Party + PIP Subrogation</div>
                                    <div class="disb-method-detail">
                                        <span>Gross (3rd Party)</span>
                                        <span x-text="fmtMoney(mahlerCalc.gross)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Attorney Fee</span>
                                        <span x-text="'-' + fmtMoney(mahlerCalc.fee)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Costs</span>
                                        <span x-text="'-' + fmtMoney(mahlerCalc.costs)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Carrier Share</span>
                                        <span x-text="mahlerCalc.carrierShare > 0 ? '-' + fmtMoney(mahlerCalc.carrierShare) : 'Waived'"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Medical Balance</span>
                                        <span x-text="'-' + fmtMoney(mahlerCalc.medicalBalance)"></span>
                                    </div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
                                        <div class="disb-method-net" style="margin:0;padding:0;border:none;"
                                            :style="mahlerCalc.clientNet >= 0 ? 'color:var(--mbds-green)' : 'color:var(--mbds-red)'"
                                            x-text="'Client Net: ' + fmtMoney(mahlerCalc.clientNet)"></div>
                                        <button @click.stop="printMethod('mahler')" class="disb-method-print" title="Print Mahler">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Hamm: UM/UIM + PIP -->
                            <template x-if="showHamm()">
                                <div class="disb-method-card" :class="settings.settlement_method === 'hamm' ? 'selected' : ''"
                                    @click="selectMethod('hamm')">
                                    <div class="disb-method-name">Hamm Method</div>
                                    <div class="disb-method-desc">UM/UIM + PIP Subrogation</div>
                                    <div class="disb-method-detail">
                                        <span>Gross (UM/UIM + PIP)</span>
                                        <span x-text="fmtMoney(hammCalc.gross)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Attorney Fee</span>
                                        <span x-text="'-' + fmtMoney(hammCalc.fee)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Costs</span>
                                        <span x-text="'-' + fmtMoney(hammCalc.costs)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Hamm Fee (PIP Recovery)</span>
                                        <span x-text="'-' + fmtMoney(hammCalc.hammFee)"></span>
                                    </div>
                                    <div class="disb-method-detail">
                                        <span>Medical Balance</span>
                                        <span x-text="'-' + fmtMoney(hammCalc.medicalBalance)"></span>
                                    </div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
                                        <div class="disb-method-net" style="margin:0;padding:0;border:none;"
                                            :style="hammCalc.clientNet >= 0 ? 'color:var(--mbds-green)' : 'color:var(--mbds-red)'"
                                            x-text="'Client Net: ' + fmtMoney(hammCalc.clientNet)"></div>
                                        <button @click.stop="printMethod('hamm')" class="disb-method-print" title="Print Hamm">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Disbursement Statement -->
                    <div class="disb-statement">
                        <div class="disb-statement-header">Disbursement Statement</div>
                        <table>
                            <template x-for="(line, idx) in disbursementLines" :key="idx">
                                <tr :class="{
                                    'disb-section-row': line.section,
                                    'disb-total-row': line.isTotal,
                                }">
                                    <td x-text="line.label" :style="line.indent ? 'padding-left:32px' : ''"></td>
                                    <td class="disb-amount" style="width:160px;"
                                        :class="{
                                            'negative': line.amount < 0 && !line.isTotal,
                                            'positive': line.amount > 0 && !line.section && !line.isTotal,
                                        }"
                                        x-text="line.section ? '' : fmtMoney(line.amount)">
                                    </td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
