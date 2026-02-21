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
        dateFrom: '',
        dateTo: '',

        // Import
        showImportModal: false,
        importFile: null,
        importing: false,
        importResult: null,

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
            try {
                const filterParams = {
                    batch_id: this.batchFilter,
                    status: this.statusFilter,
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
            this.dateFrom = '';
            this.dateTo = '';
        },

        _hasPageFilters() {
            return !!(this.batchFilter || this.statusFilter || this.dateFrom || this.dateTo);
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
    };
}
