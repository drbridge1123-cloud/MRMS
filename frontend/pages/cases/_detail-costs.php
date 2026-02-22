
            <!-- Cost Ledger Section -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-6">
                <div class="px-6 py-3 flex items-center justify-between cursor-pointer" @click="showCostLedger = !showCostLedger">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="showCostLedger ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <h3 class="font-semibold text-v2-text text-sm">Cost Ledger</h3>
                        <span class="text-xs text-v2-text-light" x-text="'(' + allCosts.length + ')'"></span>
                        <template x-if="allCostsTotal.paid > 0">
                            <span class="text-xs font-bold text-amber-700" x-text="'Total: $' + allCostsTotal.paid.toFixed(2)"></span>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click.stop="printCostLedger()" title="Print Cost Ledger"
                            class="border border-v2-card-border text-v2-text-mid px-2.5 py-1 rounded-lg text-xs hover:bg-v2-bg flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                        <label @click.stop class="border border-amber-300 text-amber-700 px-2.5 py-1 rounded-lg text-xs hover:bg-amber-50 flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Import CSV
                            <input type="file" accept=".csv" class="hidden" @change="handleCostImportFile($event)">
                        </label>
                        <button @click.stop="openCostModal()"
                            class="bg-amber-600 text-white px-2.5 py-1 rounded-lg text-xs hover:bg-amber-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Cost
                        </button>
                    </div>
                </div>
                <div x-show="showCostLedger" x-collapse>
                    <div class="overflow-x-auto">
                        <table class="data-table" style="min-width: 800px;">
                            <thead>
                                <tr>
                                    <th class="pl-6">Date</th>
                                    <th>Description</th>
                                    <th>Provider</th>
                                    <th>Category</th>
                                    <th>Billed</th>
                                    <th>Paid</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody x-show="allCosts.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center text-v2-text-light py-6 text-sm">No costs recorded yet</td>
                                </tr>
                            </tbody>
                            <template x-for="cost in allCosts" :key="cost.id">
                                <tr>
                                    <td class="text-sm pl-6" x-text="formatDate(cost.payment_date)"></td>
                                    <td class="text-sm" x-text="cost.description || '-'"></td>
                                    <td class="text-sm text-v2-text-mid" x-text="cost.linked_provider_name || '-'"></td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="getCategoryClass(cost.expense_category)"
                                            x-text="getCategoryLabel(cost.expense_category)"></span>
                                    </td>
                                    <td class="text-sm" x-text="'$' + parseFloat(cost.billed_amount || 0).toFixed(2)"></td>
                                    <td class="text-sm font-semibold text-amber-700" x-text="'$' + parseFloat(cost.paid_amount || 0).toFixed(2)"></td>
                                    <td>
                                        <div class="flex gap-1">
                                            <button @click="editCostEntry(cost)" title="Edit"
                                                class="p-1 rounded text-v2-text-light hover:text-gold hover:bg-gold/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <button @click="deleteCostEntry(cost)" title="Delete"
                                                class="p-1 rounded text-red-400 hover:text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="allCosts.length > 0">
                                <tr class="border-t-2 border-v2-card-border font-semibold">
                                    <td colspan="4" class="text-right text-sm text-v2-text-mid">TOTAL</td>
                                    <td class="text-sm" x-text="'$' + allCostsTotal.billed.toFixed(2)"></td>
                                    <td class="text-sm text-amber-700" x-text="'$' + allCostsTotal.paid.toFixed(2)"></td>
                                    <td></td>
                                </tr>
                            </template>
                        </table>
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
                        <!-- Summary -->
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
                                <p class="text-lg font-bold text-amber-700" x-text="'$' + (costImportSummary.total_paid || 0).toFixed(2)"></p>
                                <p class="text-[10px] text-v2-text-light">Total Paid</p>
                            </div>
                        </div>

                        <!-- Preview table -->
                        <div class="max-h-80 overflow-y-auto border border-v2-card-border rounded-lg">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 bg-white">
                                    <tr class="border-b border-v2-card-border">
                                        <th class="text-left px-3 py-2">Provider</th>
                                        <th class="text-left px-3 py-2">Description</th>
                                        <th class="text-left px-3 py-2">Category</th>
                                        <th class="text-right px-3 py-2">Billed</th>
                                        <th class="text-right px-3 py-2">Paid</th>
                                        <th class="text-left px-3 py-2">Type</th>
                                        <th class="text-left px-3 py-2">Staff</th>
                                        <th class="text-left px-3 py-2">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, idx) in costImportPreview" :key="idx">
                                        <tr class="border-b border-v2-bg">
                                            <td class="px-3 py-1.5 font-medium" x-text="row.provider_name || '-'"></td>
                                            <td class="px-3 py-1.5" x-text="row.description || '-'"></td>
                                            <td class="px-3 py-1.5">
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium"
                                                    :class="getCategoryClass(row.expense_category)"
                                                    x-text="getCategoryLabel(row.expense_category)"></span>
                                            </td>
                                            <td class="px-3 py-1.5 text-right" x-text="'$' + row.billed_amount.toFixed(2)"></td>
                                            <td class="px-3 py-1.5 text-right font-semibold text-amber-700" x-text="'$' + row.paid_amount.toFixed(2)"></td>
                                            <td class="px-3 py-1.5 capitalize" x-text="row.payment_type || '-'"></td>
                                            <td class="px-3 py-1.5" x-text="row.paid_by_name || '-'"></td>
                                            <td class="px-3 py-1.5" x-text="row.payment_date ? formatDate(row.payment_date) : '-'"></td>
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
