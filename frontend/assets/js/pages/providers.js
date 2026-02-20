function providersListPage() {
    return {
        ...listPageBase('providers', {
            defaultSort: '',
            defaultDir: 'asc',
            filtersToParams() {
                return {
                    type: this.typeFilter,
                    difficulty_level: this.difficultyFilter,
                };
            }
        }),

        // Page-specific state
        typeFilter: '',
        difficultyFilter: '',
        showCreateModal: false,
        showDetailModal: false,
        showProviderModal: false,
        saving: false,
        detailProvider: null,
        selectedProvider: null,
        editProvider: {
            id: null, name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
            phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
            third_party_name: '', third_party_contact: '', notes: ''
        },
        newProvider: {
            name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
            phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
            third_party_name: '', third_party_contact: '', notes: ''
        },

        _resetPageFilters() {
            this.typeFilter = '';
            this.difficultyFilter = '';
        },

        _hasPageFilters() {
            return this.typeFilter !== '' || this.difficultyFilter !== '';
        },

        getDifficultyStyle(level) {
            const styles = {
                easy:   { background: '#F0FDF4', color: '#166534' },
                medium: { background: '#FFFBEB', color: '#D97706' },
                hard:   { background: '#FEF2F2', color: '#DC2626' }
            };
            return styles[level] || {};
        },

        getAvgColor(days) {
            if (!days) return '#5A6B82';
            if (days > 21) return '#DC2626';
            if (days > 10) return '#D97706';
            return '#166534';
        },

        getContactTypeStyle(type) {
            const styles = {
                email:  { background: '#EFF6FF', color: '#1E40AF' },
                fax:    { background: '#F5F3FF', color: '#6B21A8' },
                phone:  { background: '#ECFEFF', color: '#0E7490' },
                portal: { background: '#FFF7ED', color: '#C2410C' }
            };
            return styles[type] || { background: '#F5F5F0', color: '#5A6B82' };
        },

        async viewProvider(id) {
            try {
                const res = await api.get('providers/' + id);
                this.detailProvider = res.data;
                this.selectedProvider = res.data;
            } catch (e) {
                showToast('Failed to load provider', 'error');
            }
        },

        async deleteProvider(id, name) {
            if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
            try {
                await api.delete('providers/' + id);
                showToast('Provider deleted');
                this.selectedProvider = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete provider', 'error');
            }
        },

        async createProvider() {
            this.saving = true;
            try {
                const data = { ...this.newProvider };
                data.uses_third_party = data.uses_third_party ? 1 : 0;
                await api.post('providers', data);
                showToast('Provider created successfully');
                this.showCreateModal = false;
                this.newProvider = {
                    name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
                    phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
                    third_party_name: '', third_party_contact: '', notes: ''
                };
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create provider', 'error');
            }
            this.saving = false;
        },

        async updateProvider() {
            if (!this.editProvider.id) return;
            this.saving = true;
            try {
                const data = { ...this.editProvider };
                data.uses_third_party = data.uses_third_party ? 1 : 0;
                await api.put('providers/' + data.id, data);
                showToast('Provider updated successfully');
                this.showProviderModal = false;
                this.editProvider = {
                    id: null, name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
                    phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
                    third_party_name: '', third_party_contact: '', notes: ''
                };
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update provider', 'error');
            }
            this.saving = false;
        }
    };
}
