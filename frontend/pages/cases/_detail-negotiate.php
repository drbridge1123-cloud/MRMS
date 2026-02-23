
<style>
.neg-panel {
    --navy: var(--sidebar, #0F1B2D);
    --navy-light: var(--sidebar-light, #1A2A40);
    --navy-border: var(--sidebar-border, #243347);
    --gold: #C9A84C;
    --gold-hover: #B8973F;
    --gold-light: #E8D5A0;
    --off-white: #fdfdfb;
    --border: #e8e4dc;
    --border-soft: #f0ede6;
    --mbds-muted: #6b6b63;
    --mbds-text: #1a2535;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 14px;
    color: var(--mbds-text);
    border: 1px solid var(--border);
    border-left: 3px solid var(--gold);
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(15,27,45,0.04);
    overflow: hidden;
    margin-bottom: 24px;
    background: #fff;
}
.neg-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}
.neg-header:hover { background: var(--off-white); }
.neg-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.neg-header-title {
    color: var(--mbds-text);
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.neg-header-badge {
    background: rgba(201,168,76,0.10);
    color: var(--gold-hover);
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid rgba(201,168,76,0.28);
}
.neg-chevron {
    color: var(--mbds-muted);
    transition: transform 0.2s;
}
.neg-body {
    background: var(--off-white);
    padding: 16px 20px;
}

/* Coverage tabs */
.neg-coverage-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 0;
}
.neg-coverage-tab {
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--mbds-muted);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: all 0.15s;
}
.neg-coverage-tab:hover { color: var(--mbds-text); }
.neg-coverage-tab.active {
    color: var(--navy);
    border-bottom-color: var(--gold);
}
.neg-coverage-tab .tab-count {
    font-size: 11px;
    color: var(--mbds-muted);
    margin-left: 4px;
}

/* Rounds table */
.neg-rounds-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    background: #fff;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid var(--border);
}
.neg-rounds-table th {
    background: var(--off-white);
    color: var(--mbds-text);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.neg-rounds-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-weight: 600;
}
.neg-rounds-table tbody tr { background: #f0ede6; }
.neg-rounds-table tr:last-child td { border-bottom: none; }
.neg-rounds-table tr:hover { background: #e8e4dc; }

/* Round form */
.neg-round-form {
    background: #fff;
    border: 1px solid var(--gold);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}
.neg-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 12px;
}
.neg-form-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--mbds-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.neg-form-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-size: 13px;
    font-family: 'IBM Plex Sans', sans-serif;
    background: var(--off-white);
}
.neg-form-input:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 0 2px rgba(201,168,76,0.15);
}

/* Status badges */
.neg-status { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 10px; }
.neg-status-pending { background: #f3f4f6; color: #6b7280; }
.neg-status-countered { background: #fef3c7; color: #92400e; }
.neg-status-accepted { background: #d1fae5; color: #065f46; }
.neg-status-rejected { background: #fee2e2; color: #991b1b; }

/* Best offer card */
.neg-best-offer {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 12px;
}
.neg-best-label { color: var(--gold); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.neg-best-amount { color: #fff; font-size: 20px; font-weight: 700; font-family: 'IBM Plex Mono', monospace; }

/* Provider negotiations section */
.neg-provider-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 2px solid var(--border);
}
.neg-provider-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.neg-provider-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
}
.neg-btn {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.15s;
    font-family: 'IBM Plex Sans', sans-serif;
}
.neg-btn-gold { background: var(--gold); color: #fff; }
.neg-btn-gold:hover { background: var(--gold-hover); }
.neg-btn-outline { background: transparent; border: 1px solid var(--border); color: var(--mbds-muted); }
.neg-btn-outline:hover { border-color: var(--gold); color: var(--gold); }
.neg-btn-sm { padding: 4px 10px; font-size: 11px; }

/* Provider neg inline inputs */
.neg-prov-input {
    width: 90px;
    padding: 5px 8px;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 13px;
    font-family: 'IBM Plex Mono', monospace;
    text-align: right;
    background: transparent;
}
.neg-prov-input:hover { border-color: var(--border); background: #fff; }
.neg-prov-input:focus {
    outline: none;
    border-color: var(--gold);
    background: #fff;
    box-shadow: 0 0 0 2px rgba(201,168,76,0.15);
}

/* Adjuster info card */
.neg-adjuster-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 12px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
    gap: 10px;
    align-items: end;
}
.neg-adjuster-card .neg-adj-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--mbds-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
}
.neg-adjuster-card .neg-adj-input {
    width: 100%;
    padding: 5px 8px;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 700;
    font-family: 'IBM Plex Sans', sans-serif;
    background: transparent;
}
.neg-adjuster-card .neg-adj-input:hover {
    border-color: var(--border);
    background: var(--off-white);
}
.neg-adjuster-card .neg-adj-input:focus {
    outline: none;
    border-color: var(--gold);
    background: #fff;
    box-shadow: 0 0 0 2px rgba(201,168,76,0.15);
}

/* Neg status select */
.neg-prov-status {
    padding: 4px 8px;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 12px;
    font-family: 'IBM Plex Sans', sans-serif;
    background: transparent;
    cursor: pointer;
}
.neg-prov-status:hover { border-color: var(--border); background: #fff; }
.neg-prov-status:focus { outline: none; border-color: var(--gold); }
</style>

<div class="neg-panel" data-panel :class="{'panel-open': open}" x-data="negotiatePanel(caseId)" x-init="init()">
    <!-- Header -->
    <div class="neg-header" @click="open = !open; if(open) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="neg-header-left">
            <svg class="neg-chevron w-4 h-4" :style="open ? 'transform:rotate(90deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="neg-header-title">Negotiate</span>
            <template x-if="activeCoverages.length > 0">
                <span class="neg-header-badge" x-text="activeCoverages.length + ' Coverage'"></span>
            </template>
            <template x-if="getTotalBestOffer() > 0">
                <span style="color:var(--gold); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:600;"
                    x-text="'Best: $' + getTotalBestOffer().toLocaleString('en-US', {minimumFractionDigits:2})"></span>
            </template>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button @click.stop="showRoundForm = true; editingRound = null; resetRoundForm()"
                class="neg-btn neg-btn-gold neg-btn-sm" style="display:flex; align-items:center; gap:4px;">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Round
            </button>
        </div>
    </div>

    <!-- Body -->
    <div x-show="open" x-collapse>
        <div class="neg-body">
            <template x-if="loading">
                <div style="text-align:center; padding:24px; color:var(--mbds-muted);">Loading...</div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Coverage type tabs -->
                    <div class="neg-coverage-tabs">
                        <template x-for="type in ['3rd_party','um','uim','dv']" :key="type">
                            <div class="neg-coverage-tab"
                                :class="activeCoverage === type ? 'active' : ''"
                                @click="activeCoverage = type">
                                <span x-text="getCoverageLabel(type)"></span>
                                <span class="tab-count" x-text="'(' + (coverageNegotiations[type]?.length || 0) + ')'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Adjuster Info Card (per coverage) -->
                    <div class="neg-adjuster-card">
                        <div>
                            <div class="neg-adj-label">Insurance</div>
                            <input type="text" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].insurance_company"
                                @input="saveAdjusterInfo()" placeholder="e.g. State Farm">
                        </div>
                        <div>
                            <div class="neg-adj-label">Adjuster Name</div>
                            <input type="text" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].party"
                                @input="saveAdjusterInfo()" placeholder="e.g. John Smith">
                        </div>
                        <div>
                            <div class="neg-adj-label">Phone</div>
                            <input type="tel" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].adjuster_phone"
                                @input="saveAdjusterInfo()" @blur="formatPhone('adjuster_phone')" placeholder="(555) 123-4567">
                        </div>
                        <div>
                            <div class="neg-adj-label">Fax</div>
                            <input type="tel" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].adjuster_fax"
                                @input="saveAdjusterInfo()" @blur="formatPhone('adjuster_fax')" placeholder="(555) 123-4567">
                        </div>
                        <div>
                            <div class="neg-adj-label">Email</div>
                            <input type="email" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].adjuster_email"
                                @input="saveAdjusterInfo()" @blur="formatEmail()" placeholder="adjuster@insurance.com">
                        </div>
                        <div>
                            <div class="neg-adj-label">Claim #</div>
                            <input type="text" class="neg-adj-input" x-model="adjusterInfo[activeCoverage].claim_number"
                                @input="saveAdjusterInfo()" placeholder="CLM-123456">
                        </div>
                    </div>

                    <!-- Add/Edit Round Form -->
                    <template x-if="showRoundForm">
                        <div class="neg-round-form">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <span style="font-size:14px; font-weight:700; color:var(--navy);"
                                    x-text="editingRound ? 'Edit Round' : 'New Round — ' + getCoverageLabel(activeCoverage)"></span>
                                <button @click="showRoundForm = false" style="color:var(--mbds-muted); cursor:pointer; background:none; border:none; font-size:16px;">&times;</button>
                            </div>
                            <div class="neg-form-grid">
                                <!-- Row 1: Dates & Amounts -->
                                <div>
                                    <div class="neg-form-label">Demand Date</div>
                                    <input type="date" class="neg-form-input" x-model="roundForm.demand_date">
                                </div>
                                <div>
                                    <div class="neg-form-label">Demand Amount</div>
                                    <input type="number" step="0.01" class="neg-form-input" x-model.number="roundForm.demand_amount" placeholder="0.00">
                                </div>
                                <div>
                                    <div class="neg-form-label">Offer Date</div>
                                    <input type="date" class="neg-form-input" x-model="roundForm.offer_date">
                                </div>
                                <div>
                                    <div class="neg-form-label">Offer Amount</div>
                                    <input type="number" step="0.01" class="neg-form-input" x-model.number="roundForm.offer_amount" placeholder="0.00">
                                </div>
                                <!-- Row 2: Notes + Status -->
                                <div style="grid-column: span 3;">
                                    <div class="neg-form-label">Notes</div>
                                    <input type="text" class="neg-form-input" x-model="roundForm.notes" placeholder="Optional notes">
                                </div>
                                <div>
                                    <div class="neg-form-label">Status</div>
                                    <select class="neg-form-input" x-model="roundForm.status">
                                        <option value="pending">Pending</option>
                                        <option value="countered">Countered</option>
                                        <option value="accepted">Accepted</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:12px;">
                                <button @click="showRoundForm = false" class="neg-btn neg-btn-outline">Cancel</button>
                                <button @click="saveRound()" class="neg-btn neg-btn-gold">
                                    <span x-text="editingRound ? 'Update' : 'Add Round'"></span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Rounds table -->
                    <template x-if="(coverageNegotiations[activeCoverage]?.length || 0) > 0">
                        <div>
                            <table class="neg-rounds-table">
                                <thead>
                                    <tr>
                                        <th style="width:36px">#</th>
                                        <th>Demand Date</th>
                                        <th style="text-align:right">Demand</th>
                                        <th>Offer Date</th>
                                        <th style="text-align:right">Offer</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th style="width:60px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="round in coverageNegotiations[activeCoverage]" :key="round.id">
                                        <tr>
                                            <td style="color:var(--mbds-muted); font-weight:600;" x-text="round.round_number"></td>
                                            <td x-text="round.demand_date || '-'"></td>
                                            <td style="text-align:right; font-family:'IBM Plex Mono',monospace;">
                                                <span x-show="round.demand_amount > 0" style="color:#c2410c;"
                                                    x-text="'$' + parseFloat(round.demand_amount).toLocaleString('en-US', {minimumFractionDigits:2})"></span>
                                                <span x-show="!round.demand_amount || round.demand_amount == 0" style="color:var(--mbds-muted);">-</span>
                                            </td>
                                            <td x-text="round.offer_date || '-'"></td>
                                            <td style="text-align:right; font-family:'IBM Plex Mono',monospace;">
                                                <span x-show="round.offer_amount > 0" style="color:#065f46;"
                                                    x-text="'$' + parseFloat(round.offer_amount).toLocaleString('en-US', {minimumFractionDigits:2})"></span>
                                                <span x-show="!round.offer_amount || round.offer_amount == 0" style="color:var(--mbds-muted);">-</span>
                                            </td>
                                            <td>
                                                <span class="neg-status" :class="'neg-status-' + round.status" x-text="round.status"></span>
                                            </td>
                                            <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; cursor:pointer;"
                                                x-text="round.notes || '-'" :title="round.notes"
                                                @click="round.notes ? viewingNote = { round: round.round_number, coverage: getCoverageLabel(activeCoverage), text: round.notes } : null"></td>
                                            <td>
                                                <div style="display:flex; gap:4px;">
                                                    <button @click="editRound(round)" class="icon-btn" title="Edit">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                    </button>
                                                    <button @click="deleteRound(round)" class="icon-btn icon-btn-danger" title="Delete">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <!-- Best offer card -->
                            <template x-if="bestOffers[activeCoverage] > 0">
                                <div class="neg-best-offer">
                                    <span class="neg-best-label">Best Offer — <span x-text="getCoverageLabel(activeCoverage)"></span></span>
                                    <span class="neg-best-amount" x-text="'$' + bestOffers[activeCoverage].toLocaleString('en-US', {minimumFractionDigits:2})"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="(coverageNegotiations[activeCoverage]?.length || 0) === 0 && !showRoundForm">
                        <div style="text-align:center; padding:24px; color:var(--mbds-muted); font-size:13px;">
                            No negotiation rounds for <span x-text="getCoverageLabel(activeCoverage)"></span>.
                            <button @click="showRoundForm = true; resetRoundForm()" style="color:var(--gold); cursor:pointer; background:none; border:none; font-weight:600; text-decoration:underline;">Add first round</button>
                        </div>
                    </template>

                    <!-- Provider Lien Negotiations -->
                    <div class="neg-provider-section">
                        <div class="neg-provider-header">
                            <span class="neg-provider-title">Provider Lien Negotiations</span>
                            <div style="display:flex; gap:6px;">
                                <button @click="autoPopulateProviders()" class="neg-btn neg-btn-outline neg-btn-sm">
                                    Auto-populate from Medical Balance
                                </button>
                            </div>
                        </div>

                        <template x-if="providerNegotiations.length > 0">
                            <table class="neg-rounds-table">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th style="text-align:right">Original Balance</th>
                                        <th style="text-align:right">Reduction %</th>
                                        <th style="text-align:right">Accepted Amount</th>
                                        <th>Status</th>
                                        <th style="width:40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="pn in providerNegotiations" :key="pn.id">
                                        <tr>
                                            <td style="font-weight:500;" x-text="pn.provider_name"></td>
                                            <td style="text-align:right;">
                                                <span style="font-family:'IBM Plex Mono',monospace; color:var(--mbds-text); font-size:13px;"
                                                    x-text="'$' + parseFloat(pn.original_balance).toLocaleString('en-US', {minimumFractionDigits:2})"></span>
                                            </td>
                                            <td style="text-align:right;">
                                                <input type="number" step="1" min="0" max="100"
                                                    class="neg-prov-input" style="width:60px;"
                                                    :value="parseFloat(pn.reduction_percent || 0)"
                                                    @change="updateReductionPercent(pn, $event.target.value)">
                                                <span style="font-size:12px; color:var(--mbds-muted);">%</span>
                                            </td>
                                            <td style="text-align:right;">
                                                <input type="number" step="0.01" min="0"
                                                    class="neg-prov-input"
                                                    :value="parseFloat(pn.accepted_amount || 0)"
                                                    @change="updateAcceptedAmount(pn, $event.target.value)">
                                            </td>
                                            <td>
                                                <select class="neg-prov-status"
                                                    :value="pn.status"
                                                    @change="updateProviderStatus(pn, $event.target.value)">
                                                    <option value="pending">Pending</option>
                                                    <option value="negotiating">Negotiating</option>
                                                    <option value="accepted">Accepted</option>
                                                    <option value="rejected">Rejected</option>
                                                    <option value="waived">Waived</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button @click="deleteProviderNeg(pn)" class="icon-btn icon-btn-danger icon-btn-sm" title="Delete">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </template>

                        <template x-if="providerNegotiations.length === 0">
                            <div style="text-align:center; padding:20px; color:var(--mbds-muted); font-size:13px;">
                                No provider negotiations yet.
                                <button @click="autoPopulateProviders()" style="color:var(--gold); cursor:pointer; background:none; border:none; font-weight:600; text-decoration:underline;">
                                    Auto-populate from Medical Balance
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Note Viewer Modal -->
        <template x-if="viewingNote">
            <div style="position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center;"
                 @click.self="viewingNote = null" @keydown.escape.window="viewingNote = null">
                <div style="position:fixed; inset:0; background:rgba(0,0,0,0.4);"></div>
                <div style="position:relative; background:#fff; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,0.18); width:480px; max-width:90vw; max-height:70vh; display:flex; flex-direction:column;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid #e8e4dc;">
                        <span style="font-weight:700; font-size:14px; color:var(--navy);">
                            Round <span x-text="viewingNote.round"></span> Note
                            <span style="font-weight:400; color:var(--mbds-muted); font-size:12px; margin-left:6px;" x-text="'(' + viewingNote.coverage + ')'"></span>
                        </span>
                        <button @click="viewingNote = null" style="background:none; border:none; cursor:pointer; color:#999; font-size:20px; line-height:1;">&times;</button>
                    </div>
                    <div style="padding:18px; overflow-y:auto; font-size:14px; line-height:1.6; color:var(--mbds-text); white-space:pre-wrap; word-break:break-word;"
                         x-text="viewingNote.text"></div>
                </div>
            </div>
        </template>
    </div>
</div>
