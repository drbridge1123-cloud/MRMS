    <!-- Add Provider Modal -->
    <div x-show="showAddProviderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showAddProviderModal = false"></div>
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <form @submit.prevent="addProvider()">
                <div class="modal-v2-header">
                    <div class="modal-v2-title">Add Provider to Case</div>
                    <button type="button" class="modal-v2-close" @click="showAddProviderModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div>
                        <label class="form-v2-label">Provider *</label>
                        <div class="relative">
                            <input type="text" x-model="providerSearch" @input.debounce.300ms="searchProviders()"
                                placeholder="Search provider..." class="form-v2-input">
                            <div x-show="providerResults.length > 0"
                                class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                <template x-for="pr in providerResults" :key="pr.id">
                                    <button type="button" @click="selectProvider(pr)"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-v2-bg flex justify-between">
                                        <span x-text="pr.name"></span>
                                        <span class="text-xs" style="color:var(--text-light)" x-text="getProviderTypeLabel(pr.type)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <p x-show="selectedProvider" class="text-sm mt-1" style="color:var(--gold)" x-text="selectedProvider?.name"></p>
                    </div>
                    <div>
                        <label class="form-v2-label">Record Types Needed</label>
                        <div class="flex flex-wrap gap-3">
                            <template x-for="rt in ['medical_records','billing','chart','imaging','op_report']" :key="rt">
                                <label class="flex items-center gap-1.5 text-sm">
                                    <input type="checkbox" :value="rt" x-model="newProvider.record_types"
                                        style="accent-color:var(--gold)">
                                    <span x-text="rt.replace('_',' ')"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="form-v2-label">Deadline</label>
                        <input type="date" x-model="newProvider.deadline" class="form-v2-input">
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showAddProviderModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="!selectedProvider || saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Provider
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Modal -->
    <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showRequestModal = false"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10" @click.stop>
            <form @submit.prevent="submitRequest()">
                <!-- Header -->
                <div class="modal-v2-header">
                    <div>
                        <div class="modal-v2-title">Log Record Request</div>
                        <div class="modal-v2-subtitle" x-text="currentProvider?.provider_name"></div>
                    </div>
                    <button type="button" class="modal-v2-close" @click="showRequestModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-v2-body">
                    <!-- Department Contact Selector (only if provider has contacts) -->
                    <template x-if="currentProvider?.contacts?.length > 0">
                        <div class="mb-4">
                            <label class="form-v2-label">Department</label>
                            <select x-model="newRequest.contact_id" @change="selectContact(newRequest.contact_id)" class="form-v2-select">
                                <option value="">— Manual (use fields below) —</option>
                                <template x-for="c in currentProvider.contacts" :key="c.id">
                                    <option :value="c.id" x-text="c.department + ' (' + c.contact_type + ': ' + c.contact_value + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <!-- Row 1: Date + Method + Follow-up -->
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                        <div>
                            <label class="form-v2-label">Request Date *</label>
                            <input type="date" x-model="newRequest.request_date" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Method *</label>
                            <select x-model="newRequest.request_method" required @change="newRequest.contact_id = ''; updateSentToByMethod()" class="form-v2-select">
                                <option value="email">Email</option>
                                <option value="fax">Fax</option>
                                <option value="portal">Portal</option>
                                <option value="phone">Phone</option>
                                <option value="mail">Mail</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-v2-label">Follow-up Date</label>
                            <input type="date" x-model="newRequest.next_followup_date" class="form-v2-input">
                            <p class="form-v2-hint">Defaults to 7 days</p>
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 2: Type + Template + Sent To -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;"
                        x-effect="$dispatch('auto-select-template', { type: newRequest.request_type })">
                        <div>
                            <label class="form-v2-label">Type</label>
                            <select x-model="newRequest.request_type" class="form-v2-select">
                                <option value="initial">Initial Request</option>
                                <option value="follow_up">Follow-Up</option>
                                <option value="re_request">Re-Request</option>
                                <option value="rfd">RFD</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-v2-label">Sent To</label>
                            <input type="text" x-model="newRequest.sent_to" class="form-v2-input" placeholder="Email or fax number">
                        </div>
                    </div>

                    <!-- Template Selector -->
                    <div x-data="templateSelector('medical_records')"
                        x-init="init(); $watch('selectedTemplateId', val => newRequest.template_id = val)"
                        @template-selected="newRequest.template_id = $event.detail.templateId"
                        @auto-select-template.window="
                            if ($event.detail.type === 'follow_up') {
                                const followUpTemplate = templates.find(t => t.name.toLowerCase().includes('follow-up'));
                                if (followUpTemplate) {
                                    selectedTemplateId = followUpTemplate.id;
                                    selectTemplate(followUpTemplate.id);
                                }
                            } else if ($event.detail.type === 'initial') {
                                const defaultTemplate = templates.find(t => t.is_default === 1);
                                if (defaultTemplate) {
                                    selectedTemplateId = defaultTemplate.id;
                                    selectTemplate(defaultTemplate.id);
                                }
                            }
                        ">
                        <div class="template-card-v2">
                            <div class="template-card-v2-header">
                                <label class="form-v2-label" style="margin-bottom:0">Letter Template</label>
                                <button type="button" @click="previewSelectedTemplate()"
                                    :disabled="!selectedTemplateId"
                                    class="text-xs font-semibold" style="color: var(--gold);"
                                    :style="!selectedTemplateId ? 'opacity:0.4;cursor:not-allowed' : 'cursor:pointer'">
                                    Preview
                                </button>
                            </div>
                            <select x-model="selectedTemplateId" @change="selectTemplate($event.target.value)" class="form-v2-select">
                                <option value="">Select template...</option>
                                <template x-for="template in templates" :key="template.id">
                                    <option :value="template.id" x-text="template.name + (template.is_default ? ' (Default)' : '')"></option>
                                </template>
                            </select>
                            <p class="form-v2-hint" x-show="selectedTemplate" x-text="selectedTemplate?.description"></p>
                        </div>

                        <!-- Preview Modal -->
                        <div x-show="showPreview" class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none;">
                            <div class="modal-v2-backdrop fixed inset-0" @click="closePreview()"></div>
                            <div class="modal-v2 relative w-full max-w-3xl z-10 max-h-[90vh] overflow-hidden" @click.stop>
                                <div class="modal-v2-header">
                                    <div class="modal-v2-title">Template Preview</div>
                                    <button type="button" class="modal-v2-close" @click="closePreview()">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-6 overflow-y-auto max-h-[calc(90vh-8rem)]">
                                    <div class="prose max-w-none" x-html="previewHtml"></div>
                                </div>
                                <div class="modal-v2-footer">
                                    <button type="button" @click="closePreview()" class="btn-v2-cancel">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Accordion -->
                    <div x-data="{ notesOpen: !!(newRequest.notes && newRequest.notes.trim()) }">
                        <div class="accordion-v2">
                            <button type="button" class="accordion-v2-toggle" @click="notesOpen = !notesOpen">
                                <div class="flex items-center gap-2">
                                    <label class="form-v2-label" style="margin-bottom:0; pointer-events:none;">Notes</label>
                                    <span x-show="newRequest.notes && newRequest.notes.trim().length > 0"
                                        style="font-size:11px; font-weight:700; color:var(--gold); background:rgba(201,168,76,0.12); padding:1px 8px; border-radius:9999px;">
                                        Added
                                    </span>
                                </div>
                                <svg class="accordion-v2-chevron" :class="notesOpen ? 'open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="notesOpen" x-collapse class="accordion-v2-body">
                                <textarea x-model="newRequest.notes" rows="3" class="form-v2-textarea" placeholder="Add notes about this request..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Document Attachments Accordion (email only) -->
                    <div x-show="newRequest.request_method === 'email'"
                        x-data="{...documentSelector(caseId, currentProvider?.id), attachOpen: false}"
                        x-effect="if (selectedDocumentIds.length > 0) attachOpen = true"
                        x-init="init(); $watch('selectedDocumentIds', val => newRequest.document_ids = val); $watch('showRequestModal', val => { if (val) loadDocuments() })"
                        @documents-selected="newRequest.document_ids = $event.detail.documentIds"
                        @document-uploaded.window="loadDocuments()"
                        @document-generated.window="loadDocuments()"
                        @document-deleted.window="loadDocuments()">
                        <div class="accordion-v2">
                            <button type="button" class="accordion-v2-toggle" @click="attachOpen = !attachOpen">
                                <div class="flex items-center gap-2">
                                    <label class="form-v2-label" style="margin-bottom:0; pointer-events:none;">Attachments</label>
                                    <span x-show="selectedDocumentIds.length > 0"
                                        style="font-size:11px; font-weight:700; color:var(--gold); background:rgba(201,168,76,0.12); padding:1px 8px; border-radius:9999px;"
                                        x-text="selectedDocumentIds.length + ' selected'"></span>
                                </div>
                                <svg class="accordion-v2-chevron" :class="attachOpen ? 'open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="attachOpen" x-collapse class="accordion-v2-body">
                                <div class="flex items-center justify-end gap-2 mb-3 text-xs">
                                    <button type="button" @click="selectAll()" :disabled="documents.length === 0"
                                        class="font-semibold" style="color: var(--gold);"
                                        :style="documents.length === 0 ? 'opacity:0.4;cursor:not-allowed' : 'cursor:pointer'">
                                        Select All
                                    </button>
                                    <span style="color: var(--text-light);">|</span>
                                    <button type="button" @click="deselectAll()" :disabled="selectedDocumentIds.length === 0"
                                        class="font-semibold" style="color: var(--gold);"
                                        :style="selectedDocumentIds.length === 0 ? 'opacity:0.4;cursor:not-allowed' : 'cursor:pointer'">
                                        Clear
                                    </button>
                                </div>

                                <template x-if="loading">
                                    <div class="text-center py-3">
                                        <div class="spinner inline-block"></div>
                                    </div>
                                </template>

                                <template x-if="!loading && documents.length === 0">
                                    <p class="form-v2-hint text-center py-3">
                                        No documents available.
                                    </p>
                                </template>

                                <template x-if="!loading && documents.length > 0">
                                    <div class="space-y-2">
                                        <template x-for="doc in documents" :key="doc.id">
                                            <label class="attachment-card-v2" :class="isSelected(doc.id) ? 'selected' : ''">
                                                <input type="checkbox" :value="doc.id"
                                                    @change="toggleDocument(doc.id)"
                                                    :checked="isSelected(doc.id)">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium truncate" style="color: var(--text);" x-text="doc.original_file_name"></p>
                                                    <div class="flex items-center gap-1.5 mt-1">
                                                        <span class="text-xs" style="color: var(--text-light);" x-text="doc.file_size_formatted"></span>
                                                    </div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-v2-footer">
                    <button type="button" @click="showRequestModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Log Request
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                                <option value="1">Ella</option>
                                <option value="2">Miki</option>
                                <option value="4">Jimi</option>
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

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showPaymentModal = false"></div>
        <div class="modal-v2 relative w-full max-w-2xl z-10" @click.stop>
            <form @submit.prevent="submitPayment()">
                <div class="modal-v2-header">
                    <div>
                        <div class="modal-v2-title" x-text="paymentForm.id ? 'Edit Payment' : 'Log Payment'"></div>
                        <div class="modal-v2-subtitle" x-text="currentProvider?.provider_name || 'Case-level cost'"></div>
                    </div>
                    <button type="button" class="modal-v2-close" @click="showPaymentModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
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
                                <option value="mr_cost">MR Cost</option>
                                <option value="litigation">Litigation</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 2: Billed + Paid + Payment Date -->
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
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
                            <label class="form-v2-label">Payment Date *</label>
                            <input type="date" x-model="paymentForm.payment_date" required class="form-v2-input">
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 3: Payment Type + Check # + Paid By -->
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                        <div>
                            <label class="form-v2-label">Payment Type</label>
                            <select x-model="paymentForm.payment_type" class="form-v2-select">
                                <option value="">Select...</option>
                                <option value="check">Check</option>
                                <option value="card">Card</option>
                                <option value="cash">Cash</option>
                                <option value="wire">Wire</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div x-show="paymentForm.payment_type === 'check'">
                            <label class="form-v2-label">Check #</label>
                            <input type="text" x-model="paymentForm.check_number" class="form-v2-input"
                                placeholder="Check number">
                        </div>
                        <div :class="paymentForm.payment_type !== 'check' ? 'col-span-2' : ''">
                            <label class="form-v2-label">Paid By</label>
                            <select x-model="paymentForm.paid_by" class="form-v2-select">
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
