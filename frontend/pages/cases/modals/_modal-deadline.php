    <!-- Deadline Change Modal -->
    <div x-show="showDeadlineModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showDeadlineModal = false"></div>
        <div class="modal-v2 relative w-full max-w-md z-10" @click.stop>
            <form @submit.prevent="submitDeadlineChange()">
                <div class="modal-v2-header">
                    <div>
                        <div class="modal-v2-title">Change Deadline</div>
                        <div class="modal-v2-subtitle" x-text="deadlineProvider?.provider_name"></div>
                    </div>
                    <button type="button" class="modal-v2-close" @click="showDeadlineModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div class="form-v2-row">
                        <div>
                            <label class="form-v2-label">Current Deadline</label>
                            <p class="text-sm font-medium" style="color:var(--text-mid); padding:10px 0;" x-text="formatDate(deadlineProvider?.deadline) || 'Not set'"></p>
                        </div>
                        <div>
                            <label class="form-v2-label">New Deadline *</label>
                            <input type="date" x-model="deadlineForm.deadline" required class="form-v2-input">
                        </div>
                    </div>
                    <div>
                        <label class="form-v2-label">Reason for Change * <span style="font-weight:400; text-transform:none;">(min 5 chars)</span></label>
                        <textarea x-model="deadlineForm.reason" rows="3" required minlength="5"
                            placeholder="Why is the deadline being changed?" class="form-v2-textarea"></textarea>
                    </div>
                    <template x-if="deadlineHistory.length > 0">
                        <div>
                            <label class="form-v2-label">Change History</label>
                            <div class="max-h-32 overflow-y-auto space-y-1.5">
                                <template x-for="dh in deadlineHistory" :key="dh.id">
                                    <div class="text-xs rounded px-3 py-2" style="background:var(--bg)">
                                        <div class="flex justify-between" style="color:var(--text-light)">
                                            <span x-text="dh.changed_by_name"></span>
                                            <span x-text="timeAgo(dh.created_at)"></span>
                                        </div>
                                        <p style="color:var(--text)" class="mt-0.5">
                                            <span x-text="formatDate(dh.old_deadline)"></span> &rarr; <span x-text="formatDate(dh.new_deadline)"></span>
                                        </p>
                                        <p style="color:var(--text-light)" class="mt-0.5" x-text="dh.reason"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showDeadlineModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving || !deadlineForm.deadline || deadlineForm.reason.length < 5" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Update Deadline
                    </button>
                </div>
            </form>
        </div>
    </div>
