function trackerPage() {
    return {
        ...listPageBase('tracker/list', {
            defaultSort: 'deadline',
            defaultDir: 'asc',
            perPage: 99999,
            filtersToParams() {
                return {
                    status: this.statusFilter,
                    filter: this.activeFilter,
                    tier: this.tierFilter,
                    assigned_to: this.assignedFilter,
                };
            }
        }),

        // Page-specific state
        summary: { total: 0, overdue: 0, followup_due: 0, not_started: 0 },
        statusFilter: '',
        activeFilter: '',
        tierFilter: '',
        assignedFilter: '',
        staffList: [],

        // Bulk selection
        selectedItems: [],
        allSelected: false,
        lastClickedIndex: null,

        // Bulk request modal
        showBulkRequestModal: false,
        bulkRequestForm: {
            request_date: new Date().toISOString().split('T')[0],
            request_method: 'email',
            request_type: 'follow_up',
            followup_date: '',
            notes: ''
        },
        bulkRequestCases: [],
        bulkRequestProviderName: '',
        bulkRequestError: '',

        // Bulk preview modal
        showBulkPreviewModal: false,
        bulkPreviewHtml: '',
        bulkPreviewProviderName: '',
        bulkPreviewCaseCount: 0,

        _resetPageFilters() {
            this.statusFilter = '';
            this.activeFilter = '';
            this.tierFilter = '';
            this.assignedFilter = '';
        },

        _hasPageFilters() {
            return this.statusFilter || this.activeFilter || this.tierFilter || this.assignedFilter;
        },

        async init() {
            this.loadStaff();
            await this.loadData(1);
        },

        async loadStaff() {
            try {
                const res = await api.get('users?active_only=1');
                this.staffList = res.data || [];
            } catch(e) { this.staffList = []; }
        },

        toggleFilter(filter) {
            this.activeFilter = this.activeFilter === filter ? '' : filter;
            this.loadData(1);
        },

        goToCase(caseId, cpId) {
            let url = '/MRMS/frontend/pages/cases/detail.php?id=' + caseId;
            if (cpId) url += '&cp=' + cpId;
            window.location.href = url;
        },

        getMethodLabel(method) {
            const labels = { email: 'Email', fax: 'Fax', portal: 'Portal', phone: 'Phone', mail: 'Mail' };
            return labels[method] || method || '';
        },

        // Bulk selection methods
        toggleSelect(id, event) {
            const currentIndex = this.items.findIndex(item => item.id === id);

            // Shift-click range selection
            if (event && event.shiftKey && this.lastClickedIndex !== null) {
                event.preventDefault();

                const start = Math.min(this.lastClickedIndex, currentIndex);
                const end = Math.max(this.lastClickedIndex, currentIndex);

                for (let i = start; i <= end; i++) {
                    const itemId = this.items[i].id;
                    if (!this.selectedItems.includes(itemId)) {
                        this.selectedItems.push(itemId);
                    }
                }
            } else {
                const index = this.selectedItems.indexOf(id);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                } else {
                    this.selectedItems.push(id);
                }
            }

            this.lastClickedIndex = currentIndex;
            this.updateAllSelected();
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.items.map(item => item.id);
            }
            this.updateAllSelected();
        },

        updateAllSelected() {
            this.allSelected = this.items.length > 0 && this.selectedItems.length === this.items.length;
        },

        clearSelections() {
            this.selectedItems = [];
            this.allSelected = false;
        },

        // Bulk request modal methods
        async openBulkRequestModal() {
            if (this.selectedItems.length === 0) {
                showToast('Please select at least one case', 'error');
                return;
            }

            // Reset form
            this.bulkRequestForm.request_date = new Date().toISOString().split('T')[0];
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            this.bulkRequestForm.followup_date = nextWeek.toISOString().split('T')[0];
            this.bulkRequestForm.notes = '';
            this.bulkRequestError = '';

            // Get selected items details
            const selectedCases = this.items.filter(item => this.selectedItems.includes(item.id));

            // Validate same provider
            const providers = [...new Set(selectedCases.map(c => c.provider_name))];
            if (providers.length > 1) {
                this.bulkRequestError = 'Selected cases must be from the same provider. Found: ' + providers.join(', ');
                this.showBulkRequestModal = true;
                return;
            }

            this.bulkRequestProviderName = providers[0];

            // Populate cases with default recipients
            this.bulkRequestCases = selectedCases.map(c => ({
                id: c.id,
                case_number: c.case_number,
                client_name: c.client_name,
                provider_name: c.provider_name,
                recipient: ''
            }));

            this.showBulkRequestModal = true;
        },

        closeBulkRequestModal() {
            this.showBulkRequestModal = false;
            this.bulkRequestCases = [];
            this.bulkRequestProviderName = '';
            this.bulkRequestError = '';
        },

        removeFromBulk(index) {
            this.bulkRequestCases.splice(index, 1);
            if (this.bulkRequestCases.length === 0) {
                this.closeBulkRequestModal();
            }
        },

        async previewBulkRequests() {
            if (this.bulkRequestCases.length === 0) {
                showToast('No cases to preview', 'error');
                return;
            }

            try {
                const payload = {
                    requests: this.bulkRequestCases.map(c => ({
                        case_provider_id: c.id,
                        recipient: c.recipient || undefined
                    })),
                    request_date: this.bulkRequestForm.request_date,
                    request_method: this.bulkRequestForm.request_method,
                    request_type: this.bulkRequestForm.request_type,
                    next_followup_date: this.bulkRequestForm.followup_date,
                    notes: this.bulkRequestForm.notes
                };

                const res = await api.post('requests/preview-bulk', payload);
                this.bulkPreviewHtml = res.data.letter_html || '';
                this.bulkPreviewProviderName = res.data.provider_name || '';
                this.bulkPreviewCaseCount = res.data.case_count || 0;
                this.showBulkPreviewModal = true;
            } catch (e) {
                showToast('Failed to generate preview: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closeBulkPreviewModal() {
            this.showBulkPreviewModal = false;
            this.bulkPreviewHtml = '';
            this.bulkPreviewProviderName = '';
            this.bulkPreviewCaseCount = 0;
        },

        async createAndSendBulkRequests() {
            if (this.bulkRequestCases.length === 0) {
                showToast('No cases to process', 'error');
                return;
            }

            if (!confirm(`Create and send ${this.bulkRequestCases.length} request(s) to ${this.bulkRequestProviderName}?`)) {
                return;
            }

            try {
                const payload = {
                    requests: this.bulkRequestCases.map(c => ({
                        case_provider_id: c.id,
                        recipient: c.recipient || undefined
                    })),
                    request_date: this.bulkRequestForm.request_date,
                    request_method: this.bulkRequestForm.request_method,
                    request_type: this.bulkRequestForm.request_type,
                    next_followup_date: this.bulkRequestForm.followup_date,
                    notes: this.bulkRequestForm.notes,
                    auto_send: true
                };

                const res = await api.post('requests/bulk-create', payload);
                showToast(res.message || 'Bulk requests created and sent successfully', 'success');

                this.closeBulkRequestModal();
                this.clearSelections();
                await this.loadData(this.pagination?.page || 1);
            } catch (e) {
                showToast('Failed to create bulk requests: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async confirmAndSendBulk() {
            this.closeBulkPreviewModal();
            await this.createAndSendBulkRequests();
        }
    };
}
