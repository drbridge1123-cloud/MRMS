    <!-- Move Forward Modal -->
    <div x-show="showMoveForwardModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showMoveForwardModal = false"></div>
        <div class="modal-v2 relative w-full max-w-md z-10" @click.stop>
            <form @submit.prevent="submitMoveForward()">
                <div class="modal-v2-header" style="background:var(--gold);">
                    <div class="modal-v2-title" style="color:var(--sidebar, #0F1B2D);">Move Case Forward</div>
                    <button type="button" class="modal-v2-close" style="color:var(--sidebar, #0F1B2D);" @click="showMoveForwardModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; background:var(--bg); border-radius:8px; margin-bottom:16px;">
                        <span class="text-sm font-semibold" style="color:var(--text-mid)" x-text="caseData ? getStatusLabel(caseData.status) : ''"></span>
                        <svg width="20" height="20" fill="none" stroke="var(--gold)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <span class="text-sm font-bold" style="color:var(--gold)" x-text="moveForwardForm.target_status ? getStatusLabel(moveForwardForm.target_status) : ''"></span>
                    </div>
                    <div>
                        <label class="form-v2-label">Note * <span style="font-weight:400; text-transform:none;">(min 5 chars)</span></label>
                        <textarea x-model="moveForwardForm.note" required rows="3" minlength="5" placeholder="Describe why this case is being moved forward..." class="form-v2-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showMoveForwardModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving || moveForwardForm.note.trim().length < 5" class="btn-v2-primary" style="background:var(--gold); color:var(--sidebar, #0F1B2D);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        Confirm & Move Forward
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Send Back Modal -->
    <div x-show="showSendBackModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showSendBackModal = false"></div>
        <div class="modal-v2 relative w-full max-w-md z-10" @click.stop>
            <form @submit.prevent="submitSendBack()">
                <div class="modal-v2-header" style="background:#ea580c;">
                    <div class="modal-v2-title">Send Case Back</div>
                    <button type="button" class="modal-v2-close" @click="showSendBackModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div>
                        <label class="form-v2-label">Send Back To *</label>
                        <select x-model="sendBackForm.target_status" required class="form-v2-select">
                            <option value="">Select status...</option>
                            <template x-for="s in (caseData && BACKWARD_TRANSITIONS[caseData.status] || [])" :key="s">
                                <option :value="s" x-text="getStatusLabel(s)"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="form-v2-label">Reason *</label>
                        <textarea x-model="sendBackForm.reason" required rows="3" placeholder="Explain why this case needs to be sent back..." class="form-v2-textarea"></textarea>
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showSendBackModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary" style="background:#ea580c;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        Send Back
                    </button>
                </div>
            </form>
        </div>
    </div>
