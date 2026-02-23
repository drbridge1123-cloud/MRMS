    <!-- Edit Case Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showEditModal = false"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10" @click.stop>
            <form @submit.prevent="updateCase()">
                <div class="modal-v2-header">
                    <div class="modal-v2-title">Edit Case</div>
                    <button type="button" class="modal-v2-close" @click="showEditModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Case Number *</label>
                            <input type="text" x-model="editData.case_number" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Client Name *</label>
                            <input type="text" x-model="editData.client_name" required class="form-v2-input">
                        </div>
                    </div>
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">DOB *</label>
                            <input type="date" x-model="editData.client_dob" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">DOI *</label>
                            <input type="date" x-model="editData.doi" required class="form-v2-input">
                        </div>
                    </div>
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Attorney</label>
                            <input type="text" x-model="editData.attorney_name" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Assigned To *</label>
                            <select x-model="editData.assigned_to" required class="form-v2-select">
                                <option value="">Select...</option>
                                <template x-for="u in staffList" :key="u.id">
                                    <option :value="u.id" x-text="u.full_name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-medium" style="color:var(--text)">
                        <input type="checkbox" x-model="editData.ini_completed" style="accent-color:var(--gold); width:18px; height:18px;"> INI Completed
                    </label>
                    <div>
                        <label class="form-v2-label">Notes</label>
                        <textarea x-model="editData.notes" rows="2" class="form-v2-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showEditModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
