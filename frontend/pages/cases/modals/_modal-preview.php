    <!-- Preview & Send Modal -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="closePreviewModal()"></div>
        <div class="modal-v2 relative w-full max-w-3xl max-h-[90vh] z-10 flex flex-col" @click.stop>
            <div class="modal-v2-header">
                <div>
                    <div class="modal-v2-title" x-text="isEditingLetter ? 'Edit Request Letter' : 'Preview Request Letter'"></div>
                    <div class="modal-v2-subtitle">
                        Sending via <span class="font-medium" x-text="previewData.method === 'email' ? 'Email' : 'Fax'"></span>
                        to <span class="font-medium" x-text="previewData.provider_name"></span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="toggleLetterEdit()"
                        class="px-3 py-1.5 text-sm rounded-lg border flex items-center gap-1.5 transition-colors"
                        :class="isEditingLetter ? 'bg-white/20 text-white border-white/30' : 'border-white/20 text-white/60 hover:text-white hover:bg-white/10'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span x-text="isEditingLetter ? 'Editing' : 'Edit Letter'"></span>
                    </button>
                    <button class="modal-v2-close" @click="closePreviewModal()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div style="padding:12px 24px; border-bottom:1px solid #E8E5DE; background:#FAFAF8;">
                <div class="form-v2-row">
                    <div>
                        <label class="form-v2-label" x-text="previewData.method === 'email' ? 'Recipient Email' : 'Recipient Fax Number'"></label>
                        <input type="text" x-model="previewData.recipient" class="form-v2-input"
                            :placeholder="previewData.method === 'email' ? 'provider@example.com' : '(212) 555-1234'">
                    </div>
                    <div x-show="previewData.method === 'email'">
                        <label class="form-v2-label">Subject</label>
                        <input type="text" x-model="previewData.subject" :readonly="!isEditingLetter" class="form-v2-input"
                            :style="!isEditingLetter ? 'background:#F5F5F0; color:var(--text-mid); cursor:default' : ''">
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" style="padding:16px 24px;">
                <div class="border rounded-lg bg-white shadow-inner transition-colors"
                    :class="isEditingLetter ? 'border-gold ring-2 ring-gold/20' : 'border-v2-card-border'">
                    <iframe x-ref="letterIframe" :srcdoc="previewData.letter_html" class="w-full border-0" style="min-height: 600px;"></iframe>
                </div>
            </div>
            <div class="modal-v2-footer" style="justify-content:space-between;">
                <div class="text-sm flex items-center gap-3" style="color:var(--text-light)">
                    <template x-if="previewData.send_status === 'failed'">
                        <span class="text-red-600">Previous attempt failed. You can retry.</span>
                    </template>
                    <template x-if="isEditingLetter && originalLetterHtml">
                        <button @click="resetLetterToOriginal()" class="underline text-sm" style="color:var(--text-mid)">
                            Reset to Original
                        </button>
                    </template>
                    <template x-if="originalLetterHtml && !isEditingLetter">
                        <span class="inline-flex items-center gap-1 text-amber-600 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                            </svg>
                            Letter has been modified
                        </span>
                    </template>
                </div>
                <div class="flex gap-3">
                    <button @click="closePreviewModal()" class="btn-v2-cancel">Cancel</button>
                    <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient"
                        class="btn-v2-primary" style="background:#16a34a;">
                        <template x-if="sending">
                            <div class="spinner" style="width:16px;height:16px;border-width:2px;"></div>
                        </template>
                        <span x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
