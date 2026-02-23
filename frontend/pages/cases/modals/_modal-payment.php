    <!-- Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showPaymentModal = false"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10" @click.stop>
            <form @submit.prevent="submitPayment()">
                <div class="modal-v2-header">
                    <div>
                        <div class="modal-v2-title" x-text="paymentForm.id ? 'Edit Payment' : 'Log Payment'"></div>
                        <div class="modal-v2-subtitle" x-text="paymentForm.provider_name || 'Case-level cost'"></div>
                    </div>
                    <button type="button" class="modal-v2-close" @click="showPaymentModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <!-- Provider Search Autocomplete -->
                    <div>
                        <label class="form-v2-label">Provider</label>
                        <div class="relative">
                            <input type="text" x-model="paymentProviderSearch"
                                @input.debounce.300ms="searchPaymentProviders()"
                                @focus="if(paymentProviderSearch.length >= 2) searchPaymentProviders()"
                                placeholder="Search provider or leave empty for case-level cost..."
                                class="form-v2-input" style="padding-right:2rem;">
                            <button type="button" x-show="paymentProviderSearch.length > 0"
                                @click="clearPaymentProvider()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                style="background:none;border:none;cursor:pointer;padding:2px;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <div x-show="paymentProviderResults.length > 0"
                                @click.outside="paymentProviderResults = []"
                                class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                <template x-for="pr in paymentProviderResults" :key="pr.id">
                                    <button type="button" @click="selectPaymentProvider(pr)"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-v2-bg flex justify-between">
                                        <span x-text="pr.name"></span>
                                        <span class="text-xs" style="color:var(--text-light)" x-text="getProviderTypeLabel(pr.type)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <p x-show="paymentForm.case_provider_id" class="text-xs mt-1" style="color:var(--gold)">Linked to case provider</p>
                        <p x-show="paymentProviderSearch && !paymentForm.case_provider_id && paymentForm.provider_name" class="text-xs mt-1 text-v2-text-light">Provider not linked to this case</p>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 1: Description + Category -->
                    <div class="form-v2-row">
                        <div class="flex-1">
                            <label class="form-v2-label">Description</label>
                            <input type="text" x-model="paymentForm.description" class="form-v2-input"
                                placeholder="Record Fee, Police Report, etc.">
                        </div>
                        <div style="min-width:160px;">
                            <label class="form-v2-label">Category</label>
                            <select x-model="paymentForm.expense_category" class="form-v2-select">
                                <option value="mr_cost">Records Fee</option>
                                <option value="litigation">Litigation</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 2: Billed + Paid + Date + Paid Date -->
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px;">
                        <div>
                            <label class="form-v2-label">Billed Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-v2-text-light text-sm">$</span>
                                <input type="number" step="0.01" min="0" x-model.number="paymentForm.billed_amount"
                                    class="form-v2-input" style="padding-left:1.5rem;">
                            </div>
                        </div>
                        <div>
                            <label class="form-v2-label">Paid Amount *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-v2-text-light text-sm">$</span>
                                <input type="number" step="0.01" min="0" x-model.number="paymentForm.paid_amount"
                                    required class="form-v2-input" style="padding-left:1.5rem;">
                            </div>
                        </div>
                        <div>
                            <label class="form-v2-label">Date *</label>
                            <input type="date" x-model="paymentForm.payment_date" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Paid Date</label>
                            <input type="date" x-model="paymentForm.paid_date" class="form-v2-input">
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 3: Payment Type + Card/Check # + Paid By -->
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                        <div>
                            <label class="form-v2-label">Payment Type</label>
                            <select x-model="paymentForm.payment_type" @change="autoFillCardNumber()" class="form-v2-select">
                                <option value="">Select...</option>
                                <option value="check">Check</option>
                                <option value="card">Card</option>
                                <option value="cash">Cash</option>
                                <option value="wire">Wire</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div x-show="paymentForm.payment_type === 'check' || paymentForm.payment_type === 'card'">
                            <label class="form-v2-label" x-text="paymentForm.payment_type === 'card' ? 'Card Last 4' : 'Check #'"></label>
                            <input type="text" x-model="paymentForm.check_number" class="form-v2-input"
                                :placeholder="paymentForm.payment_type === 'card' ? 'Last 4 digits' : 'Check number'"
                                :readonly="paymentForm.payment_type === 'card' && paymentForm.check_number !== ''"
                                :style="paymentForm.payment_type === 'card' && paymentForm.check_number !== '' ? 'background:#f9f9f6;color:var(--gold);font-weight:600' : ''"
                                maxlength="paymentForm.payment_type === 'card' ? 4 : 50">
                        </div>
                        <div :class="paymentForm.payment_type !== 'check' && paymentForm.payment_type !== 'card' ? 'col-span-2' : ''">
                            <label class="form-v2-label">Paid By</label>
                            <select x-model="paymentForm.paid_by" @change="autoFillCardNumber()" class="form-v2-select">
                                <option value="">Select staff...</option>
                                <template x-for="u in staffList" :key="u.id">
                                    <option :value="u.id" x-text="u.full_name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Receipt Upload -->
                    <div x-data="{ uploading: false }">
                        <label class="form-v2-label">Receipt / Invoice</label>
                        <template x-if="paymentForm.receipt_document_id">
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-green-50 border border-green-200 text-sm">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="text-green-700 font-medium" x-text="paymentForm.receipt_file_name || 'Receipt attached'"></span>
                                <button type="button" @click="paymentForm.receipt_document_id = null; paymentForm.receipt_file_name = ''"
                                    class="ml-auto text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!paymentForm.receipt_document_id">
                            <div>
                                <input type="file" accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff"
                                    @change="uploadReceipt($event)" :disabled="uploading"
                                    class="form-v2-input text-sm" style="padding:6px;">
                                <template x-if="uploading">
                                    <p class="text-xs text-amber-600 mt-1 flex items-center gap-1">
                                        <span class="spinner" style="width:12px;height:12px;border-width:2px;"></span>
                                        Uploading...
                                    </p>
                                </template>
                                <p class="form-v2-hint">PDF, JPG, PNG, TIF (optional)</p>
                            </div>
                        </template>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-v2-label">Notes</label>
                        <textarea x-model="paymentForm.notes" rows="2" class="form-v2-textarea"
                            placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showPaymentModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="paymentForm.id ? 'Update Payment' : 'Log Payment'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
