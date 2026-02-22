function bankReconciliationPage() {
    return {
        ...listPageBase('bank-reconciliation', {
            defaultSort: 'transaction_date',
            defaultDir: 'desc',
            perPage: 50,
            filtersToParams() {
                return {
                    batch_id: this.batchFilter,
                    status: this.statusFilter,
                    date_from: this.dateFrom,
                    date_to: this.dateTo,
                };
            }
        }),

        summary: {},
        batches: [],
        batchFilter: '',
        statusFilter: '',
        staffFilter: '',
        dateFrom: '',
        dateTo: '',

        // Import
        showImportModal: false,
        importFile: null,
        importing: false,
        importResult: null,

        // Selection
        selectedIds: [],

        // Match
        showMatchModal: false,
        matchingEntry: null,
        matchSearch: '',
        matchResults: [],
        matchSearching: false,

        async init() {
            await this.loadData();
        },

        // Override loadData to also capture batches
        async loadData(page = 1) {
            this.loading = true;
            this.selectedIds = [];
            try {
                const filterParams = {
                    batch_id: this.batchFilter,
                    status: this.statusFilter,
                    staff: this.staffFilter,
                    date_from: this.dateFrom,
                    date_to: this.dateTo,
                };
                const params = buildQueryString({
                    search: this.search,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir,
                    page: page,
                    per_page: 50,
                    ...filterParams
                });
                const res = await api.get('bank-reconciliation' + params);
                this.items = res.data || [];
                this.pagination = res.pagination || null;
                if (res.summary) this.summary = res.summary;
                if (res.batches) this.batches = res.batches;
            } catch (e) {
                console.error('loadData error:', e);
            }
            this.loading = false;
        },

        toggleStatusFilter(status) {
            this.statusFilter = this.statusFilter === status ? '' : status;
            this.loadData(1);
        },

        _resetPageFilters() {
            this.batchFilter = '';
            this.statusFilter = '';
            this.staffFilter = '';
            this.dateFrom = '';
            this.dateTo = '';
        },

        _hasPageFilters() {
            return !!(this.batchFilter || this.statusFilter || this.staffFilter || this.dateFrom || this.dateTo);
        },

        sortIcon(col) {
            if (this.sortBy !== col) return '';
            const cls = this.sortDir === 'asc' ? '' : ' rotate-180';
            return `<svg class="w-3 h-3 inline-block${cls}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>`;
        },

        formatMoney(val) {
            if (val === null || val === undefined) return '-';
            const num = parseFloat(val);
            if (isNaN(num)) return '-';
            return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        goToCase(caseId) {
            if (caseId) window.location.href = '/MRMS/frontend/pages/cases/detail.php?id=' + caseId;
        },

        getPageNumbers() {
            if (!this.pagination) return [];
            const total = this.pagination.total_pages;
            const current = this.pagination.page;
            const pages = [];
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            if (end - start < 4) {
                if (start === 1) end = Math.min(total, start + 4);
                else start = Math.max(1, end - 4);
            }
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        // Import
        async doImport() {
            if (!this.importFile) return;
            this.importing = true;
            this.importResult = null;
            try {
                const formData = new FormData();
                formData.append('file', this.importFile);
                const res = await api.upload('bank-reconciliation/import', formData);
                this.importResult = res.data;
                showToast(res.message || 'Import complete', 'success');
                this.importFile = null;
                await this.loadData(1);
            } catch (e) {
                showToast(e.message || 'Import failed', 'error');
            }
            this.importing = false;
        },

        // Match
        openMatchModal(entry) {
            this.matchingEntry = entry;
            this.matchSearch = entry.check_number || '';
            this.matchResults = [];
            this.showMatchModal = true;
            this.searchPayments();
        },

        async searchPayments() {
            this.matchSearching = true;
            try {
                const params = buildQueryString({
                    q: this.matchSearch,
                    amount: this.matchingEntry?.amount || '',
                    check_number: '',
                });
                const res = await api.get('bank-reconciliation/search-payments' + params);
                this.matchResults = res.data || [];
            } catch (e) {
                console.error('searchPayments error:', e);
            }
            this.matchSearching = false;
        },

        async confirmMatch(paymentId) {
            if (!this.matchingEntry) return;
            try {
                await api.put('bank-reconciliation/' + this.matchingEntry.id + '/match', { payment_id: paymentId });
                showToast('Entry matched successfully', 'success');
                this.showMatchModal = false;
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Match failed', 'error');
            }
        },

        async unmatchEntry(entry) {
            if (!await confirmAction('Remove this match?')) return;
            try {
                await api.put('bank-reconciliation/' + entry.id + '/unmatch');
                showToast('Match removed', 'success');
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Failed', 'error');
            }
        },

        async ignoreEntry(entry) {
            try {
                await api.put('bank-reconciliation/' + entry.id + '/ignore');
                showToast('Entry ignored', 'success');
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Failed', 'error');
            }
        },

        async restoreEntry(entry) {
            try {
                await api.put('bank-reconciliation/' + entry.id + '/unmatch');
                showToast('Entry restored', 'success');
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Failed', 'error');
            }
        },

        // Row click
        onRowClick(item) {
            if (item.reconciliation_status === 'unmatched') {
                this.openMatchModal(item);
            } else if (item.reconciliation_status === 'matched') {
                this.unmatchEntry(item);
            } else if (item.reconciliation_status === 'ignored') {
                this.restoreEntry(item);
            }
        },

        // Selection
        toggleSelect(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx === -1) this.selectedIds.push(id);
            else this.selectedIds.splice(idx, 1);
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedIds = this.items.map(i => i.id);
            } else {
                this.selectedIds = [];
            }
        },

        isAllSelected() {
            return this.items.length > 0 && this.selectedIds.length === this.items.length;
        },

        isIndeterminate() {
            return this.selectedIds.length > 0 && this.selectedIds.length < this.items.length;
        },

        clearSelection() {
            this.selectedIds = [];
        },

        // Bulk actions
        async bulkIgnore() {
            if (!this.selectedIds.length) return;
            try {
                const res = await api.post('bank-reconciliation/bulk-action', { ids: this.selectedIds, action: 'ignore' });
                showToast(res.message || 'Done', 'success');
                this.selectedIds = [];
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Failed', 'error');
            }
        },

        async bulkRestore() {
            if (!this.selectedIds.length) return;
            try {
                const res = await api.post('bank-reconciliation/bulk-action', { ids: this.selectedIds, action: 'restore' });
                showToast(res.message || 'Done', 'success');
                this.selectedIds = [];
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Failed', 'error');
            }
        },

        async bulkAutoMatch() {
            if (!this.selectedIds.length) return;
            try {
                const res = await api.post('bank-reconciliation/bulk-action', { ids: this.selectedIds, action: 'auto-match' });
                showToast(res.message || 'Done', 'success');
                this.selectedIds = [];
                await this.loadData();
            } catch (e) {
                showToast(e.message || 'Failed', 'error');
            }
        },
    };
}
