function mbdsListPage() {
    return {
        ...listPageBase('mbds', {
            defaultSort: 'updated_at',
            defaultDir: 'desc',
            filtersToParams() {
                return {
                    status: this.statusFilter,
                };
            }
        }),

        // Page-specific state
        summary: {},
        statusFilter: '',

        async init() {
            await this.loadData();
        },

        toggleFilter(s) {
            this.statusFilter = this.statusFilter === s ? '' : s;
            this.loadData();
        },

        _resetPageFilters() {
            this.statusFilter = '';
        },

        _hasPageFilters() {
            return !!this.statusFilter;
        },

        exportCSV() {
            if (!this.items.length) return;
            const headers = ['Case #','Client','DOI','PIP','Health','Charges','Balance','Lines','Status','Updated'];
            const rows = this.items.map(i => [
                i.case_number, i.client_name, formatDate(i.doi),
                i.pip1_name || '', i.health1_name || '',
                i.total_charges?.toFixed(2), i.total_balance?.toFixed(2),
                i.line_count, i.status, formatDate(i.updated_at)
            ]);
            const csv = [headers, ...rows].map(r => r.map(c => '"' + String(c ?? '').replace(/"/g, '""') + '"').join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'mbds_reports_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
        },
    };
}
