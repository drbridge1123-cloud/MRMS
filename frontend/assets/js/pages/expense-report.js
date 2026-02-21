function expenseReportPage() {
    return {
        ...listPageBase('expense-report', {
            defaultSort: 'payment_date',
            defaultDir: 'desc',
            filtersToParams() {
                return {
                    date_from: this.dateFrom,
                    date_to: this.dateTo,
                    category: this.categoryFilter,
                    payment_type: this.paymentTypeFilter,
                    staff_id: this.staffFilter,
                };
            }
        }),

        // Page-specific state
        summary: {},
        staffList: [],
        dateFrom: '',
        dateTo: '',
        categoryFilter: '',
        paymentTypeFilter: '',
        staffFilter: '',

        get pageTotalBilled() {
            return this.items.reduce((sum, i) => sum + parseFloat(i.billed_amount || 0), 0);
        },

        get pageTotalPaid() {
            return this.items.reduce((sum, i) => sum + parseFloat(i.paid_amount || 0), 0);
        },

        async init() {
            await this.loadData();
        },

        _resetPageFilters() {
            this.dateFrom = '';
            this.dateTo = '';
            this.categoryFilter = '';
            this.paymentTypeFilter = '';
            this.staffFilter = '';
        },

        _hasPageFilters() {
            return !!(this.dateFrom || this.dateTo || this.categoryFilter || this.paymentTypeFilter || this.staffFilter);
        },

        formatMoney(val) {
            if (val === null || val === undefined) return '-';
            const num = parseFloat(val);
            if (isNaN(num)) return '-';
            return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        getCategoryLabel(cat) {
            const labels = { mr_cost: 'MR Cost', litigation: 'Litigation', other: 'Other' };
            return labels[cat] || cat || '-';
        },

        getPaymentTypeLabel(type) {
            const labels = { check: 'Check', card: 'Card', cash: 'Cash', wire: 'Wire', other: 'Other' };
            return labels[type] || type || '-';
        },

        goToCase(caseId) {
            if (caseId) {
                window.location.href = '/MRMS/frontend/pages/cases/detail.php?id=' + caseId;
            }
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

        exportCSV() {
            const params = buildQueryString({
                date_from: this.dateFrom,
                date_to: this.dateTo,
                category: this.categoryFilter,
                payment_type: this.paymentTypeFilter,
                staff_id: this.staffFilter,
                search: this.search,
            });
            window.open('/MRMS/backend/api/expense-report/export' + params, '_blank');
        },
    };
}
