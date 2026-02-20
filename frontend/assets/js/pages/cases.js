function casesListPage() {
    return {
        ...listPageBase('cases', {
            defaultSort: '',
            defaultDir: 'desc',
            perPage: 9999,
            filtersToParams() {
                return {
                    status: this.statusFilter,
                    assigned_to: this.assignedFilter,
                };
            }
        }),

        // Page-specific state
        statusFilter: '',
        assignedFilter: '',
        showCreateModal: false,
        saving: false,
        users: [],
        staffList: [],
        newCase: { case_number: '', client_name: '', client_dob: '', doi: '', attorney_name: '', assigned_to: '', notes: '' },

        _resetPageFilters() {
            this.statusFilter = '';
            this.assignedFilter = '';
        },

        _hasPageFilters() {
            return this.statusFilter || this.assignedFilter;
        },

        async createCase() {
            this.saving = true;
            try {
                await api.post('cases', { ...this.newCase });
                showToast('Case created successfully');
                this.showCreateModal = false;
                this.newCase = { case_number: '', client_name: '', client_dob: '', doi: '', attorney_name: '', assigned_to: '', notes: '' };
                this.loadData(1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to create case', 'error');
            }
            this.saving = false;
        },

        async deleteCase(id, caseNumber, clientName) {
            if (!confirm(`Delete case ${caseNumber} (${clientName})? This will also delete all providers, requests, and notes for this case.`)) return;
            try {
                await api.delete('cases/' + id);
                showToast('Case deleted');
                this.loadData(this.pagination?.page || 1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete case', 'error');
            }
        },

        async init() {
            try {
                const res = await api.get('users?active_only=1');
                this.staffList = res.data || [];
                this.users = this.staffList;
            } catch (e) {}

            const auth = Alpine.store('auth');
            if (auth.loading) {
                await new Promise(r => {
                    const iv = setInterval(() => { if (!auth.loading) { clearInterval(iv); r(); } }, 50);
                });
            }

            const uid = auth.user?.id;
            const defaults = { 2: 'collecting', 1: 'in_review', 4: 'completed' };
            if (uid && defaults[uid]) {
                this.statusFilter = defaults[uid];
            }

            await this.loadData();
        }
    };
}
