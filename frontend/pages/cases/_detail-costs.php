
<style>
.cost-panel {
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
.cost-header {
    background: #fff;
    border-bottom: 1px solid var(--border);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}
.cost-header:hover { background: var(--off-white); }
.cost-header-left { display: flex; align-items: center; gap: 10px; min-width: 0; }
.cost-header-title { color: var(--mbds-text); font-size: 14px; font-weight: 600; letter-spacing: 0.3px; white-space: nowrap; }
.cost-header-badge {
    background: var(--gold);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cost-chevron { color: var(--mbds-muted); transition: transform 0.2s; width: 14px !important; height: 14px !important; min-width: 14px; max-width: 14px; flex-shrink: 0; }
.cost-btn {
    border: 1px solid var(--border);
    color: var(--mbds-muted);
    padding: 6px 14px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s;
}
.cost-btn:hover { border-color: #ccc8be; color: var(--mbds-text); }
.cost-btn-primary {
    background: var(--gold);
    color: #fff;
    border: 1px solid var(--gold);
    padding: 6px 14px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s;
}
.cost-btn-primary:hover { background: var(--gold-hover); border-color: var(--gold-hover); }
.cost-body {
    background: var(--off-white);
}
.cost-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    background: #fff;
    table-layout: fixed;
}
.cost-table th {
    background: #b5a070;
    color: var(--navy);
    font-weight: 700;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 9px 10px;
    text-align: left;
    border-bottom: 2px solid var(--border);
    border-right: 1px solid rgba(255,255,255,0.25);
    white-space: nowrap;
    overflow: hidden;
}
.cost-table th:last-child { border-right: none; }
.cost-table td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cost-table tr:hover { background: #fafaf7; }
.cost-table .cost-total-row {
    background: var(--navy);
    font-weight: 600;
}
.cost-table .cost-total-row td {
    background: var(--navy);
    color: rgba(255,255,255,0.7);
    font-family: 'IBM Plex Mono', monospace;
    padding: 10px 10px;
    border: none;
    border-right: 1px solid var(--navy-border);
    border-top: 2px solid var(--gold);
}
.cost-table .cost-total-row td:last-child { border-right: none; }
.cost-category-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.cost-cat-mr_cost { background: #e8f0fe; color: #1a56db; }
.cost-cat-litigation { background: #fce8e8; color: #b83232; }
.cost-cat-filing_fee { background: #fef3e2; color: #b45309; }
.cost-cat-other { background: #f0f0ec; color: #5a5a54; }
.cost-action-btn {
    padding: 4px;
    border-radius: 4px;
    color: var(--mbds-muted);
    cursor: pointer;
    transition: all 0.15s;
    background: none;
    border: none;
}
.cost-action-btn:hover { color: var(--gold); background: rgba(201,168,76,0.1); }
.cost-action-btn.delete:hover { color: #b83232; background: rgba(184,50,50,0.08); }
.cost-empty {
    padding: 32px 20px;
    text-align: center;
    color: var(--mbds-muted);
    font-size: 13px;
}
.cost-money { font-family: 'IBM Plex Mono', monospace; font-size: 12px; }
.cost-money-paid { color: var(--gold); font-weight: 600; }
.cost-section-header td {
    background: #f0ede6;
    color: var(--gold);
    font-weight: 700;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 5px 10px;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}
.cost-section-header:hover { background: #f0ede6 !important; }
.cost-subtotal-row td {
    background: var(--navy);
    color: rgba(255,255,255,0.7);
    font-weight: 600;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    padding: 10px 10px;
    border: none;
    border-right: 1px solid var(--navy-border);
    border-top: 2px solid var(--gold);
}
.cost-subtotal-row td:last-child { border-right: none; }
.cost-subtotal-row:hover td { background: var(--navy) !important; }
</style>

<!-- Cost Ledger Panel -->
<div class="cost-panel" data-panel :class="{'panel-open': showCostLedger}">
    <!-- Header -->
    <div class="cost-header" @click="showCostLedger = !showCostLedger; if(showCostLedger) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
        <div class="cost-header-left">
            <svg class="cost-chevron" :style="showCostLedger ? 'transform:rotate(90deg)' : ''" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="cost-header-title">Cost Ledger</span>
            <template x-if="allCosts.length > 0">
                <span class="cost-header-badge" x-text="allCosts.length"></span>
            </template>
            <template x-if="allCostsTotal.billed > 0">
                <span style="color:var(--mbds-text); font-size:12px; font-family:'IBM Plex Mono',monospace; font-weight:700;"
                    x-text="'Total: $' + allCostsTotal.billed.toLocaleString('en-US', {minimumFractionDigits:2})"></span>
            </template>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <button @click.stop="printCostLedger()" class="cost-btn">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <label @click.stop class="cost-btn" style="cursor:pointer">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV
                <input type="file" accept=".csv" style="display:none" @change="handleCostImportFile($event)">
            </label>
            <button @click.stop="openCostModal()" class="cost-btn-primary">
                <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Cost
            </button>
        </div>
    </div>

    <!-- Body -->
    <div x-show="showCostLedger" x-collapse>
        <div class="cost-body">
            <!-- Empty state -->
            <template x-if="allCosts.length === 0">
                <div class="cost-empty">No costs recorded yet</div>
            </template>

            <!-- Table -->
            <template x-if="allCosts.length > 0">
                <div style="overflow-x:auto;">
                    <table class="cost-table">
                        <thead>
                            <tr>
                                <th style="width:8%;padding-left:16px;">Date</th>
                                <th style="width:17%;">Provider</th>
                                <th style="width:14%;">Description</th>
                                <th style="width:7%;text-align:center;">Category</th>
                                <th style="width:8%;text-align:right;">Billed</th>
                                <th style="width:5%;text-align:center;">Type</th>
                                <th style="width:8%;text-align:center;">Card/Check #</th>
                                <th style="width:7%;">Staff</th>
                                <th style="width:8%;text-align:right;">Paid</th>
                                <th style="width:8%;">Paid Date</th>
                                <th style="width:5%;text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <template x-for="group in getCostGroups()" :key="group.category">
                            <tbody>
                                <tr class="cost-section-header">
                                    <td colspan="11" x-text="group.label"></td>
                                </tr>
                                <template x-for="cost in group.costs" :key="cost.id">
                                    <tr>
                                        <td style="padding-left:16px;" x-text="formatDate(cost.payment_date)"></td>
                                        <td style="font-weight:500;" x-text="cost.linked_provider_name || cost.provider_name || '-'"></td>
                                        <td style="color:var(--mbds-muted);" x-text="cost.description || '-'"></td>
                                        <td style="text-align:center;">
                                            <span class="cost-category-badge"
                                                :class="'cost-cat-' + (cost.expense_category || 'other')"
                                                x-text="getCategoryLabel(cost.expense_category)"></span>
                                        </td>
                                        <td class="cost-money" style="text-align:right;" x-text="'$' + parseFloat(cost.billed_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                        <td class="capitalize" style="text-align:center;" x-text="cost.payment_type || '-'"></td>
                                        <td style="text-align:center;" x-text="cost.check_number || '-'"></td>
                                        <td x-text="cost.paid_by_name || '-'"></td>
                                        <td class="cost-money cost-money-paid" style="text-align:right;" x-text="'$' + parseFloat(cost.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                        <td x-text="cost.paid_date ? formatDate(cost.paid_date) : '-'"></td>
                                        <td style="text-align:center;">
                                            <div style="display:flex;gap:2px;justify-content:center;">
                                                <button @click="editCostEntry(cost)" title="Edit" class="icon-btn icon-btn-sm">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button @click="deleteCostEntry(cost)" title="Delete" class="icon-btn icon-btn-danger icon-btn-sm">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr class="cost-subtotal-row">
                                    <td colspan="4" style="text-align:right;padding-right:10px;text-transform:uppercase;font-size:10px;letter-spacing:0.1em;color:rgba(255,255,255,0.35);font-weight:700;font-family:'IBM Plex Sans',sans-serif;" x-text="group.label + ' Total'"></td>
                                    <td style="text-align:right;font-family:'IBM Plex Mono',monospace;" x-text="'$' + group.totalBilled.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;color:var(--gold);font-weight:600;font-family:'IBM Plex Mono',monospace;" x-text="'$' + group.totalPaid.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </template>
                    </table>
                </div>
            </template>
        </div>
    </div>
</div>

<!-- Cost Import Preview Modal -->
<div x-show="showCostImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="modal-v2-backdrop fixed inset-0" @click="showCostImportModal = false"></div>
    <div class="modal-v2 relative w-full max-w-3xl z-10" @click.stop>
        <div class="modal-v2-header">
            <h3 class="modal-v2-title">Import Cost Ledger Preview</h3>
            <button type="button" class="modal-v2-close" @click="showCostImportModal = false">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-v2-body">
            <div class="flex gap-4 mb-4">
                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                    <p class="text-lg font-bold text-v2-text" x-text="costImportSummary.count || 0"></p>
                    <p class="text-[10px] text-v2-text-light">Entries</p>
                </div>
                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                    <p class="text-lg font-bold text-v2-text" x-text="'$' + (costImportSummary.total_billed || 0).toFixed(2)"></p>
                    <p class="text-[10px] text-v2-text-light">Total Billed</p>
                </div>
                <div class="bg-v2-bg rounded-lg px-4 py-2 text-center flex-1">
                    <p class="text-lg font-bold" style="color:#C9A84C;" x-text="'$' + (costImportSummary.total_paid || 0).toFixed(2)"></p>
                    <p class="text-[10px] text-v2-text-light">Total Paid</p>
                </div>
            </div>
            <div class="max-h-80 overflow-y-auto border border-v2-card-border rounded-lg">
                <table class="w-full text-xs">
                    <thead class="sticky top-0 bg-white">
                        <tr class="border-b border-v2-card-border">
                            <th class="text-left px-3 py-2">Date</th>
                            <th class="text-left px-3 py-2">Provider</th>
                            <th class="text-left px-3 py-2">Description</th>
                            <th class="text-right px-3 py-2">Billed</th>
                            <th class="text-left px-3 py-2">Type</th>
                            <th class="text-left px-3 py-2">Staff</th>
                            <th class="text-right px-3 py-2">Paid</th>
                            <th class="text-left px-3 py-2">Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, idx) in costImportPreview" :key="idx">
                            <tr class="border-b border-v2-bg">
                                <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.original_date ? formatDate(row.original_date) : '-'"></td>
                                <td class="px-3 py-1.5">
                                    <div class="font-medium" x-text="row.provider_name || '-'"></div>
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium"
                                        :class="getCategoryClass(row.expense_category)"
                                        x-text="getCategoryLabel(row.expense_category)"></span>
                                </td>
                                <td class="px-3 py-1.5" x-text="row.description || '-'"></td>
                                <td class="px-3 py-1.5 text-right" x-text="'$' + row.billed_amount.toFixed(2)"></td>
                                <td class="px-3 py-1.5 capitalize" x-text="row.payment_type || '-'"></td>
                                <td class="px-3 py-1.5" x-text="row.paid_by_name || '-'"></td>
                                <td class="px-3 py-1.5 text-right font-semibold" style="color:#C9A84C;" x-text="'$' + row.paid_amount.toFixed(2)"></td>
                                <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.paid_date ? formatDate(row.paid_date) : '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-v2-footer">
            <button type="button" @click="showCostImportModal = false" class="btn-v2-cancel">Cancel</button>
            <button type="button" @click="confirmCostImport()" :disabled="costImporting"
                    class="btn-v2-primary bg-amber-600 hover:bg-amber-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span x-text="costImporting ? 'Importing...' : 'Import ' + (costImportSummary.count || 0) + ' Entries'"></span>
            </button>
        </div>
    </div>
</div>
