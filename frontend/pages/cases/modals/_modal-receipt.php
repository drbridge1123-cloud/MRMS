    <!-- Receipt Modal -->
    <div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showReceiptModal = false"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10" @click.stop>
            <form @submit.prevent="submitReceipt()">
                <div class="modal-v2-header">
                    <div>
                        <div class="modal-v2-title">Log Receipt</div>
                        <div class="modal-v2-subtitle" x-text="currentProvider?.provider_name"></div>
                    </div>
                    <button type="button" class="modal-v2-close" @click="showReceiptModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Received Date *</label>
                            <input type="date" x-model="newReceipt.received_date" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Method *</label>
                            <select x-model="newReceipt.received_method" required class="form-v2-select">
                                <option value="fax">Fax</option>
                                <option value="email">Email</option>
                                <option value="portal">Portal</option>
                                <option value="mail">Mail</option>
                                <option value="in_person">In Person</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-v2-label">Received Items</label>
                        <div class="flex flex-wrap gap-x-5 gap-y-2">
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_medical_records" style="accent-color:var(--gold)"> Medical Records</label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_billing" style="accent-color:var(--gold)"> Billing</label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_chart" style="accent-color:var(--gold)"> Chart Notes</label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_imaging" style="accent-color:var(--gold)"> Imaging</label>
                        </div>
                    </div>
                    <div class="form-v2-divider"></div>
                    <label class="flex items-center gap-2 text-sm font-semibold" style="color:var(--text)">
                        <input type="checkbox" x-model="newReceipt.is_complete" style="accent-color:#16a34a; width:18px; height:18px;"> All records complete
                    </label>
                    <div x-show="!newReceipt.is_complete">
                        <label class="form-v2-label">Incomplete Reason</label>
                        <textarea x-model="newReceipt.incomplete_reason" rows="2" class="form-v2-textarea" placeholder="Describe what's missing..."></textarea>
                    </div>
                    <div>
                        <label class="form-v2-label">File Location (Sharepoint path)</label>
                        <input type="text" x-model="newReceipt.file_location" class="form-v2-input" placeholder="\\sharepoint\cases\...">
                    </div>
                </div>
                <div class="modal-v2-footer" style="justify-content:space-between;">
                    <button type="button" @click="setProviderOnHold()" :disabled="saving" class="btn-v2-cancel" style="color:#b45309; border-color:#b45309;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        On Hold
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="showReceiptModal = false" class="btn-v2-cancel">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-v2-primary" style="background:#16a34a;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Log Receipt
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
