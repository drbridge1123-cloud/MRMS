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
