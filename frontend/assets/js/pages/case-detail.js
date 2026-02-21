function caseDetailPage() {
    return {
        caseId: getQueryParam('id'),
        caseData: null,
        providers: [],
        notes: [],
        loading: true,
        saving: false,

        showEditModal: false,
        showAddProviderModal: false,
        showSendBackModal: false,
        sendBackForm: { target_status: '', reason: '' },
        nextStatus: '',
        showRequestModal: false,
        showReceiptModal: false,
        showPreviewModal: false,
        showDeadlineModal: false,

        editData: {},
        currentProvider: null,
        provSortBy: '',
        provSortDir: 'asc',
        expandedProvider: null,
        requestHistory: [],
        previewData: { method: '', recipient: '', provider_name: '', client_name: '', send_status: '', subject: '', letter_html: '', request_id: null },
        isEditingLetter: false,
        originalLetterHtml: '',
        originalSubject: '',
        sending: false,
        deadlineProvider: null,
        deadlineForm: { deadline: '', reason: '' },
        deadlineHistory: [],

        providerSearch: '',
        providerResults: [],
        selectedProvider: null,
        newProvider: { record_types: [], deadline: '' },
        newRequest: { request_date: new Date().toISOString().split('T')[0], request_method: 'email', request_type: 'initial', sent_to: '', authorization_sent: true, notes: '', template_id: null, document_ids: [] },
        newReceipt: { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, is_complete: false, incomplete_reason: '', file_location: '' },
        newNote: { note_type: 'general', content: '', case_provider_id: '', contact_method: '', contact_date: '' },
        noteFilterProvider: '',

        // Payment state
        showPaymentModal: false,
        payments: [],
        paymentTotal: 0,
        staffList: [],
        paymentForm: { id: null, description: 'Record Fee', expense_category: 'mr_cost', billed_amount: 0, paid_amount: 0, payment_type: 'check', check_number: '', payment_date: new Date().toISOString().split('T')[0], paid_by: '', receipt_document_id: null, receipt_file_name: '', notes: '' },

        // Cost Ledger state
        allCosts: [],
        allCostsTotal: { billed: 0, paid: 0 },
        showCostLedger: false,

        async init() {
            if (!this.caseId) {
                window.location.href = '/MRMS/frontend/pages/cases/index.php';
                return;
            }
            // Set default deadline (2 weeks from today)
            this.newProvider.deadline = this.getDefaultDeadline();
            await Promise.all([this.loadCase(), this.loadProviders(), this.loadNotes(), this.loadStaffList(), this.loadAllCosts()]);

            // Auto-expand provider if cp param is present (from tracker)
            const cpId = getQueryParam('cp');
            if (cpId) {
                this.expandedProvider = parseInt(cpId);
                this.loadRequestHistory(parseInt(cpId));
                this.loadPayments(parseInt(cpId));
            }

            this.loading = false;

            // Scroll to expanded provider and flash
            if (cpId) {
                this.$nextTick(() => {
                    const el = document.getElementById('history-' + parseInt(cpId));
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.querySelector('td').classList.add('history-flash');
                    }
                });
            }
        },

        async loadCase() {
            try {
                const res = await api.get('cases/' + this.caseId);
                this.caseData = res.data;
                this.editData = { ...res.data };
            } catch (e) {
                showToast('Failed to load case', 'error');
            }
        },

        async loadProviders() {
            try {
                let url = 'case-providers?case_id=' + this.caseId;
                if (this.provSortBy) url += '&sort_by=' + this.provSortBy + '&sort_dir=' + this.provSortDir;
                const res = await api.get(url);
                this.providers = res.data || [];
            } catch (e) { }
        },

        sortProviders(column) {
            if (this.provSortBy === column) {
                this.provSortDir = this.provSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.provSortBy = column;
                this.provSortDir = 'asc';
            }
            this.loadProviders();
        },

        async loadNotes() {
            try {
                let url = 'notes?case_id=' + this.caseId;
                if (this.noteFilterProvider) url += '&case_provider_id=' + this.noteFilterProvider;
                const res = await api.get(url);
                this.notes = res.data || [];
            } catch (e) { }
        },

        async updateCase() {
            this.saving = true;
            try {
                await api.put('cases/' + this.caseId, this.editData);
                showToast('Case updated');
                this.showEditModal = false;
                await this.loadCase();
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Update failed', 'error');
            }
            this.saving = false;
        },

        async changeStatus() {
            if (!this.nextStatus) return;
            this.saving = true;
            try {
                await api.post('cases/' + this.caseId + '/change-status', {
                    new_status: this.nextStatus
                });
                showToast('Status updated');
                this.nextStatus = '';
                await this.loadCase();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update status', 'error');
            } finally {
                this.saving = false;
            }
        },

        async openSendBackModal() {
            this.sendBackForm = { target_status: '', reason: '' };
            const backTargets = BACKWARD_TRANSITIONS[this.caseData.status] || [];
            if (backTargets.length > 0) {
                this.sendBackForm.target_status = backTargets[0];
            }
            this.showSendBackModal = true;
        },

        async submitSendBack() {
            if (!this.sendBackForm.target_status || !this.sendBackForm.reason.trim()) {
                showToast('Please fill in all required fields', 'error');
                return;
            }
            this.saving = true;
            try {
                await api.post('cases/' + this.caseId + '/send-back', this.sendBackForm);
                showToast('Case sent back successfully');
                this.showSendBackModal = false;
                await this.loadCase();
            } catch (e) {
                showToast(e.data?.message || 'Failed to send back', 'error');
            } finally {
                this.saving = false;
            }
        },

        async searchProviders() {
            if (this.providerSearch.length < 2) { this.providerResults = []; return; }
            try {
                const res = await api.get('providers/search?q=' + encodeURIComponent(this.providerSearch));
                this.providerResults = res.data || [];
            } catch (e) { }
        },

        selectProvider(p) {
            this.selectedProvider = p;
            this.providerSearch = p.name;
            this.providerResults = [];
        },

        getDefaultDeadline() {
            const date = new Date();
            date.setDate(date.getDate() + 14); // 2 weeks from today
            return date.toISOString().split('T')[0];
        },

        async addProvider() {
            if (!this.selectedProvider) return;
            this.saving = true;
            try {
                await api.post('case-providers', {
                    case_id: parseInt(this.caseId),
                    provider_id: this.selectedProvider.id,
                    record_types_needed: this.newProvider.record_types.join(',') || null,
                    deadline: this.newProvider.deadline || null
                });
                showToast('Provider added');
                this.showAddProviderModal = false;
                this.selectedProvider = null;
                this.providerSearch = '';
                this.newProvider = { record_types: [], deadline: this.getDefaultDeadline() };
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to add provider', 'error');
            }
            this.saving = false;
        },

        openRequestModal(p) {
            this.currentProvider = p;

            // Default next follow-up: +7 days
            const nextFollowup = new Date();
            nextFollowup.setDate(nextFollowup.getDate() + 7);
            const nextFollowupStr = nextFollowup.toISOString().split('T')[0];

            this.newRequest = {
                request_date: new Date().toISOString().split('T')[0],
                request_method: 'email',
                request_type: p.overall_status === 'not_started' ? 'initial' : 'follow_up',
                sent_to: '',
                department: '',
                contact_id: '',
                authorization_sent: true,
                notes: '',
                next_followup_date: nextFollowupStr,
                template_id: null,
                document_ids: []
            };

            // Auto-select primary contact if available
            if (p.contacts && p.contacts.length > 0) {
                const primary = p.contacts.find(c => c.is_primary == 1) || p.contacts[0];
                this.newRequest.contact_id = primary.id;
                this.selectContact(primary.id);
            } else {
                this.updateSentToByMethod();
            }
            this.showRequestModal = true;
        },

        selectContact(contactId) {
            const p = this.currentProvider;
            if (!contactId || !p || !p.contacts) {
                this.updateSentToByMethod();
                return;
            }
            const contact = p.contacts.find(c => c.id == contactId);
            if (contact) {
                this.newRequest.request_method = contact.contact_type;
                this.newRequest.sent_to = contact.contact_value;
                this.newRequest.department = contact.department || '';
            } else {
                this.newRequest.department = '';
                this.updateSentToByMethod();
            }
        },

        updateSentToByMethod() {
            const p = this.currentProvider;
            if (!p) return;
            const method = this.newRequest.request_method;
            if (method === 'email') this.newRequest.sent_to = p.provider_email || '';
            else if (method === 'fax') this.newRequest.sent_to = p.provider_fax || '';
            else if (method === 'phone') this.newRequest.sent_to = p.provider_phone || '';
            else this.newRequest.sent_to = '';
        },

        async submitRequest() {
            this.saving = true;
            try {
                const endpoint = this.newRequest.request_type === 'follow_up' ? 'requests/followup' : 'requests';
                const response = await api.post(endpoint, {
                    case_provider_id: this.currentProvider.id,
                    ...this.newRequest,
                    authorization_sent: this.newRequest.authorization_sent ? 1 : 0
                });

                const requestId = response.data.id;

                // Attach selected documents if any
                if (this.newRequest.document_ids && this.newRequest.document_ids.length > 0) {
                    for (const documentId of this.newRequest.document_ids) {
                        if (!documentId || documentId === 0 || documentId === '0') {
                            continue;
                        }
                        try {
                            await api.post(`requests/${requestId}/attach`, {
                                document_id: documentId
                            });
                        } catch (attachError) {
                            console.error('Failed to attach document:', attachError);
                        }
                    }
                }

                showToast('Request logged');
                this.showRequestModal = false;
                const cpId = this.currentProvider.id;
                await this.loadProviders();
                if (this.expandedProvider === cpId) {
                    await this.loadRequestHistory(cpId);
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to log request', 'error');
            }
            this.saving = false;
        },

        openReceiptModal(p) {
            this.currentProvider = p;
            this.newReceipt = { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, is_complete: false, incomplete_reason: '', file_location: '' };
            this.showReceiptModal = true;
        },

        async submitReceipt() {
            this.saving = true;
            try {
                await api.post('receipts', {
                    case_provider_id: this.currentProvider.id,
                    ...this.newReceipt,
                    has_medical_records: this.newReceipt.has_medical_records ? 1 : 0,
                    has_billing: this.newReceipt.has_billing ? 1 : 0,
                    has_chart: this.newReceipt.has_chart ? 1 : 0,
                    has_imaging: this.newReceipt.has_imaging ? 1 : 0,
                    is_complete: this.newReceipt.is_complete ? 1 : 0,
                });
                showToast('Receipt logged');
                this.showReceiptModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to log receipt', 'error');
            }
            this.saving = false;
        },

        async setProviderOnHold() {
            if (!this.currentProvider) return;
            this.saving = true;
            try {
                await api.put('case-providers/' + this.currentProvider.id + '/status', { overall_status: 'on_hold' });
                showToast('Provider set to On Hold');
                this.showReceiptModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update status', 'error');
            }
            this.saving = false;
        },

        async deleteProvider(id) {
            if (!await confirmAction('Remove this provider from the case?')) return;
            try {
                await api.delete('case-providers/' + id);
                showToast('Provider removed');
                await this.loadProviders();
            } catch (e) {
                showToast('Failed to remove provider', 'error');
            }
        },

        async updateProviderStatus(cp, newStatus) {
            try {
                await api.put('case-providers/' + cp.id + '/status', { overall_status: newStatus });
                showToast('Status updated', 'success');
                await this.loadProviders();
                await this.loadCase();
            } catch (e) {
                showToast('Failed to update status', 'error');
            }
        },

        async markComplete(cp) {
            if (!confirm('Mark this provider as records received complete?')) return;
            try {
                await api.put('case-providers/' + cp.id + '/status', { overall_status: 'received_complete' });
                showToast('Provider marked as complete', 'success');
                await this.loadProviders();
            } catch (e) {
                showToast('Failed to mark complete', 'error');
            }
        },

        async addNote() {
            if (!this.newNote.content.trim()) return;
            try {
                const payload = {
                    case_id: parseInt(this.caseId),
                    note_type: this.newNote.note_type,
                    content: this.newNote.content
                };
                if (this.newNote.case_provider_id) payload.case_provider_id = parseInt(this.newNote.case_provider_id);
                if (this.newNote.contact_method) payload.contact_method = this.newNote.contact_method;
                if (this.newNote.contact_date) payload.contact_date = this.newNote.contact_date;
                await api.post('notes', payload);
                this.newNote = { note_type: 'general', content: '', case_provider_id: '', contact_method: '', contact_date: '' };
                await this.loadNotes();
            } catch (e) {
                showToast('Failed to add note', 'error');
            }
        },

        async deleteNote(noteId) {
            if (!await confirmAction('Delete this note?')) return;
            try {
                await api.delete('notes/' + noteId);
                await this.loadNotes();
                showToast('Note deleted', 'success');
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete note', 'error');
            }
        },

        openDeadlineModal(p) {
            this.deadlineProvider = p;
            this.deadlineForm = { deadline: p.deadline || '', reason: '' };
            this.deadlineHistory = [];
            this.showDeadlineModal = true;
            this.loadDeadlineHistory(p.id);
        },

        async loadDeadlineHistory(cpId) {
            try {
                const res = await api.get('case-providers/' + cpId + '/deadline-history');
                this.deadlineHistory = res.data || [];
            } catch (e) { }
        },

        async submitDeadlineChange() {
            if (!this.deadlineForm.deadline || this.deadlineForm.reason.length < 5) return;
            this.saving = true;
            try {
                await api.put('case-providers/' + this.deadlineProvider.id + '/deadline', this.deadlineForm);
                showToast('Deadline updated');
                this.showDeadlineModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update deadline', 'error');
            }
            this.saving = false;
        },

        toggleRequestHistory(cpId) {
            if (this.expandedProvider === cpId) {
                this.expandedProvider = null;
                return;
            }
            this.expandedProvider = cpId;
            this.loadRequestHistory(cpId);
            this.loadPayments(cpId);
        },

        async loadRequestHistory(cpId) {
            try {
                const res = await api.get('requests?case_provider_id=' + cpId);
                this.requestHistory = res.data || [];
            } catch (e) {
                this.requestHistory = [];
            }
        },

        async deleteRequest(req) {
            if (!confirm(`Delete this ${req.send_status} ${req.request_type} request (${req.request_date})?`)) {
                return;
            }

            try {
                await api.delete('requests/' + req.id);
                showToast('Request deleted successfully', 'success');
                await this.loadRequestHistory(req.case_provider_id);
                await this.loadProviders();
                await this.loadCase();
            } catch (e) {
                showToast(e.response?.data?.error || 'Failed to delete request', 'error');
            }
        },

        async openPreviewModal(req) {
            try {
                const res = await api.get('requests/' + req.id + '/preview');
                this.previewData = res.data;
                this.isEditingLetter = false;
                this.originalLetterHtml = '';
                this.originalSubject = '';
                this.showPreviewModal = true;
            } catch (e) {
                showToast(e.data?.message || 'Failed to load preview', 'error');
            }
        },

        toggleLetterEdit() {
            const iframe = this.$refs.letterIframe;
            if (!iframe) return;

            if (this.isEditingLetter) {
                // Switching OFF edit mode - save edits from iframe
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    this.previewData.letter_html = '<!DOCTYPE html>' + iframeDoc.documentElement.outerHTML;
                    iframeDoc.designMode = 'off';
                } catch (e) { /* ignore */ }
                this.isEditingLetter = false;
            } else {
                // Switching ON edit mode - store originals on first edit
                if (!this.originalLetterHtml) {
                    this.originalLetterHtml = this.previewData.letter_html;
                    this.originalSubject = this.previewData.subject;
                }
                this.isEditingLetter = true;
                const enableEdit = () => {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        if (iframeDoc && iframeDoc.body) {
                            iframeDoc.designMode = 'on';
                        } else {
                            setTimeout(enableEdit, 50);
                        }
                    } catch (e) {
                        setTimeout(enableEdit, 50);
                    }
                };
                this.$nextTick(() => enableEdit());
            }
        },

        resetLetterToOriginal() {
            if (this.originalLetterHtml) {
                this.previewData.letter_html = this.originalLetterHtml;
                this.previewData.subject = this.originalSubject;
                if (this.isEditingLetter) {
                    const iframe = this.$refs.letterIframe;
                    const enableEdit = () => {
                        try {
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            if (iframeDoc && iframeDoc.body && iframeDoc.readyState === 'complete') {
                                iframeDoc.designMode = 'on';
                            } else {
                                setTimeout(enableEdit, 50);
                            }
                        } catch (e) {
                            setTimeout(enableEdit, 50);
                        }
                    };
                    iframe.addEventListener('load', enableEdit, { once: true });
                }
                showToast('Letter reset to original');
            }
        },

        closePreviewModal() {
            if (this.isEditingLetter) {
                try {
                    const iframe = this.$refs.letterIframe;
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    iframeDoc.designMode = 'off';
                } catch (e) { /* ignore */ }
            }
            this.isEditingLetter = false;
            this.originalLetterHtml = '';
            this.originalSubject = '';
            this.showPreviewModal = false;
        },

        getEditedLetterHtml() {
            try {
                const iframe = this.$refs.letterIframe;
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                return '<!DOCTYPE html>' + iframeDoc.documentElement.outerHTML;
            } catch (e) {
                return this.previewData.letter_html;
            }
        },

        async confirmAndSend() {
            if (!this.previewData.recipient) {
                showToast('Please enter a recipient', 'error');
                return;
            }
            if (!await confirmAction(
                'Send this request via ' + (this.previewData.method === 'email' ? 'email' : 'fax') + ' to ' + this.previewData.recipient + '?'
            )) return;

            this.sending = true;
            try {
                const payload = {
                    recipient: this.previewData.recipient
                };

                if (this.originalLetterHtml) {
                    const currentHtml = this.isEditingLetter ? this.getEditedLetterHtml() : this.previewData.letter_html;
                    if (currentHtml !== this.originalLetterHtml) {
                        payload.letter_html = currentHtml;
                    }
                    if (this.previewData.subject !== this.originalSubject) {
                        payload.subject = this.previewData.subject;
                    }
                }

                const res = await api.post('requests/' + this.previewData.request_id + '/send', payload);
                showToast(res.message || 'Sent successfully!');
                this.closePreviewModal();
                if (this.expandedProvider) {
                    await this.loadRequestHistory(this.expandedProvider);
                }
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Send failed', 'error');
            }
            this.sending = false;
        },

        getSendStatusLabel(status) {
            const labels = { draft: 'Draft', sending: 'Sending...', sent: 'Sent', failed: 'Failed' };
            return labels[status] || status;
        },

        getRequestMethodLabel(method) {
            return REQUEST_METHODS[method] || method;
        },

        getRequestTypeLabel(type) {
            return REQUEST_TYPES[type] || type;
        },

        getContactMethodLabel(method) {
            const labels = { phone: 'Phone', fax: 'Fax', email: 'Email', portal: 'Portal', mail: 'Mail', in_person: 'In Person', other: 'Other' };
            return labels[method] || method;
        },

        formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        },

        // ---- Cost Ledger Methods ----

        async loadAllCosts() {
            try {
                const res = await api.get('mr-fee-payments?case_id=' + this.caseId);
                this.allCosts = res.data.payments || [];
                this.allCostsTotal = {
                    billed: res.data.total_billed || 0,
                    paid: res.data.total_paid || 0
                };
            } catch (e) {
                this.allCosts = [];
                this.allCostsTotal = { billed: 0, paid: 0 };
            }
        },

        openCostModal() {
            this.currentProvider = null;
            this.paymentForm = {
                id: null,
                description: '',
                expense_category: 'other',
                billed_amount: 0,
                paid_amount: 0,
                payment_type: 'check',
                check_number: '',
                payment_date: new Date().toISOString().split('T')[0],
                paid_by: '',
                receipt_document_id: null,
                receipt_file_name: '',
                notes: ''
            };
            this.showPaymentModal = true;
        },

        editCostEntry(pmt) {
            this.currentProvider = pmt.case_provider_id ? { id: pmt.case_provider_id } : null;
            this.editPayment(pmt);
        },

        async deleteCostEntry(pmt) {
            if (!await confirmAction('Delete this cost entry of $' + parseFloat(pmt.paid_amount).toFixed(2) + '?')) return;
            try {
                await api.delete('mr-fee-payments/' + pmt.id);
                showToast('Cost entry deleted');
                await this.loadAllCosts();
                if (this.expandedProvider) {
                    await this.loadPayments(this.expandedProvider);
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete cost entry', 'error');
            }
        },

        getCategoryLabel(cat) {
            const labels = { mr_cost: 'MR Cost', litigation: 'Litigation', other: 'Other' };
            return labels[cat] || cat;
        },

        printCostLedger() {
            const caseName = this.caseData?.client_name || '';
            const caseNum = this.caseData?.case_number || '';
            let rows = this.allCosts.map(c =>
                `<tr>
                    <td>${formatDate(c.payment_date)}</td>
                    <td>${c.description || '-'}</td>
                    <td>${c.linked_provider_name || '-'}</td>
                    <td>${this.getCategoryLabel(c.expense_category)}</td>
                    <td>$${parseFloat(c.billed_amount || 0).toFixed(2)}</td>
                    <td>$${parseFloat(c.paid_amount || 0).toFixed(2)}</td>
                </tr>`
            ).join('');
            rows += `<tr style="border-top:2px solid #333;font-weight:bold;">
                <td colspan="4" style="text-align:right;">TOTAL</td>
                <td>$${this.allCostsTotal.billed.toFixed(2)}</td>
                <td>$${this.allCostsTotal.paid.toFixed(2)}</td>
            </tr>`;
            const html = `<!DOCTYPE html><html><head><title>Cost Ledger - ${caseNum}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 30px; }
                    h2 { margin-bottom: 4px; }
                    .sub { color: #666; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 13px; }
                    th { background: #f5f5f5; font-weight: 600; text-transform: uppercase; font-size: 11px; }
                </style>
            </head><body>
                <h2>Cost Ledger</h2>
                <div class="sub">${caseName} &mdash; ${caseNum}</div>
                <table>
                    <thead><tr><th>Date</th><th>Description</th><th>Provider</th><th>Category</th><th>Billed</th><th>Paid</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </body></html>`;
            const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
            w.onload = () => { w.print(); };
        },

        getCategoryClass(cat) {
            const classes = {
                mr_cost: 'bg-blue-100 text-blue-700',
                litigation: 'bg-purple-100 text-purple-700',
                other: 'bg-gray-100 text-gray-700'
            };
            return classes[cat] || 'bg-gray-100 text-gray-700';
        },

        // ---- Payment Methods ----

        async loadStaffList() {
            try {
                const res = await api.get('users');
                this.staffList = (res.data || []).filter(u => u.is_active == 1);
            } catch (e) {
                this.staffList = [];
            }
        },

        async loadPayments(cpId) {
            try {
                const res = await api.get('mr-fee-payments?case_id=' + this.caseId + '&case_provider_id=' + cpId);
                this.payments = res.data.payments || [];
                this.paymentTotal = res.data.total_paid || 0;
            } catch (e) {
                this.payments = [];
                this.paymentTotal = 0;
            }
        },

        openPaymentModal(provider) {
            this.currentProvider = provider;
            this.paymentForm = {
                id: null,
                description: 'Record Fee',
                expense_category: 'mr_cost',
                billed_amount: 0,
                paid_amount: 0,
                payment_type: 'check',
                check_number: '',
                payment_date: new Date().toISOString().split('T')[0],
                paid_by: '',
                receipt_document_id: null,
                receipt_file_name: '',
                notes: ''
            };
            this.showPaymentModal = true;
        },

        editPayment(pmt) {
            this.paymentForm = {
                id: pmt.id,
                description: pmt.description || '',
                expense_category: pmt.expense_category || 'mr_cost',
                billed_amount: parseFloat(pmt.billed_amount) || 0,
                paid_amount: parseFloat(pmt.paid_amount) || 0,
                payment_type: pmt.payment_type || '',
                check_number: pmt.check_number || '',
                payment_date: pmt.payment_date || '',
                paid_by: pmt.paid_by || '',
                receipt_document_id: pmt.receipt_document_id || null,
                receipt_file_name: pmt.receipt_file_name || '',
                notes: pmt.notes || ''
            };
            this.showPaymentModal = true;
        },

        async submitPayment() {
            this.saving = true;
            try {
                const payload = {
                    case_id: parseInt(this.caseId),
                    case_provider_id: this.currentProvider?.id || null,
                    description: this.paymentForm.description,
                    expense_category: this.paymentForm.expense_category,
                    billed_amount: this.paymentForm.billed_amount || 0,
                    paid_amount: this.paymentForm.paid_amount || 0,
                    payment_type: this.paymentForm.payment_type || null,
                    check_number: this.paymentForm.check_number || null,
                    payment_date: this.paymentForm.payment_date || null,
                    paid_by: this.paymentForm.paid_by || null,
                    receipt_document_id: this.paymentForm.receipt_document_id || null,
                    notes: this.paymentForm.notes || null
                };

                if (this.paymentForm.id) {
                    await api.put('mr-fee-payments/' + this.paymentForm.id, payload);
                    showToast('Payment updated');
                } else {
                    await api.post('mr-fee-payments', payload);
                    showToast('Payment logged');
                }

                this.showPaymentModal = false;
                await this.loadAllCosts();
                if (this.expandedProvider) {
                    await this.loadPayments(this.expandedProvider);
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to save payment', 'error');
            }
            this.saving = false;
        },

        async deletePayment(pmt) {
            if (!await confirmAction('Delete this payment of $' + parseFloat(pmt.paid_amount).toFixed(2) + '?')) return;
            try {
                await api.delete('mr-fee-payments/' + pmt.id);
                showToast('Payment deleted');
                await this.loadAllCosts();
                if (this.expandedProvider) {
                    await this.loadPayments(this.expandedProvider);
                }
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete payment', 'error');
            }
        },

        async uploadReceipt(event) {
            const file = event.target.files[0];
            if (!file) return;

            const uploading = true;
            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('case_id', this.caseId);
                formData.append('case_provider_id', this.currentProvider?.id || '');
                formData.append('document_type', 'other');
                formData.append('notes', 'MR Fee Receipt');

                const res = await api.upload('documents/upload', formData);
                this.paymentForm.receipt_document_id = res.data.id;
                this.paymentForm.receipt_file_name = res.data.original_file_name;
                showToast('Receipt uploaded');
            } catch (e) {
                showToast(e.data?.message || 'Upload failed', 'error');
            }
            event.target.value = '';
        },

        getPaymentTypeLabel(type) {
            const labels = { check: 'Check', card: 'Card', cash: 'Cash', wire: 'Wire', other: 'Other' };
            return labels[type] || type;
        }
    };
}
