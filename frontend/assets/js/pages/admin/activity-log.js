function activityLogPage() {
    return {
        ...listPageBase('activity-log', {
            defaultSort: '',
            defaultDir: 'desc',
            perPage: 30,
            filtersToParams() {
                return {
                    user_id: this.userFilter,
                    action: this.actionFilter,
                    entity_type: this.entityFilter,
                    date_from: this.dateFrom,
                    date_to: this.dateTo,
                };
            }
        }),

        // Page-specific state
        allUsers: [],
        userFilter: '',
        actionFilter: '',
        entityFilter: '',
        dateFrom: '',
        dateTo: '',

        // Override loadData for post-processing (_showDetails flag)
        async loadData(page = 1) {
            this.loading = true;
            const params = buildQueryString({
                page,
                per_page: 30,
                user_id: this.userFilter,
                action: this.actionFilter,
                entity_type: this.entityFilter,
                date_from: this.dateFrom,
                date_to: this.dateTo,
                sort_by: this.sortBy,
                sort_dir: this.sortDir
            });
            try {
                const res = await api.get('activity-log' + params);
                this.items = (res.data || []).map(l => ({ ...l, _showDetails: false }));
                this.pagination = res.pagination || null;
            } catch (e) {}
            this.loading = false;
            if (this.allUsers.length === 0) this.loadUsers();
        },

        _resetPageFilters() {
            this.userFilter = '';
            this.actionFilter = '';
            this.entityFilter = '';
            this.dateFrom = '';
            this.dateTo = '';
        },

        _hasPageFilters() {
            return this.userFilter || this.actionFilter || this.entityFilter || this.dateFrom || this.dateTo;
        },

        async loadUsers() {
            try {
                const res = await api.get('users?per_page=100');
                this.allUsers = res.data || [];
            } catch (e) {}
        },
    };
}
