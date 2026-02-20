function usersPage() {
    return {
        ...listPageBase('users', {
            defaultSort: '',
            defaultDir: 'asc',
            filtersToParams() {
                return {
                    role: this.roleFilter,
                    is_active: this.activeFilter,
                };
            }
        }),

        // Page-specific state
        saving: false,
        roleFilter: '',
        activeFilter: '',

        showModal: false,
        isEditing: false,
        editingId: null,
        form: { username: '', full_name: '', title: '', password: '', role: 'staff' },

        showResetModal: false,
        resetUser: null,
        newPassword: '',

        _resetPageFilters() {
            this.roleFilter = '';
            this.activeFilter = '';
        },

        _hasPageFilters() {
            return this.roleFilter || this.activeFilter;
        },

        openCreateModal() {
            this.isEditing = false;
            this.editingId = null;
            this.form = { username: '', full_name: '', title: '', password: '', role: 'staff' };
            this.showModal = true;
        },

        openEditModal(u) {
            this.isEditing = true;
            this.editingId = u.id;
            this.form = { username: u.username, full_name: u.full_name, title: u.title || '', role: u.role, smtp_email: u.smtp_email || '', smtp_app_password: '' };
            this.showModal = true;
        },

        async saveUser() {
            this.saving = true;
            try {
                if (this.isEditing) {
                    const payload = {
                        username: this.form.username,
                        full_name: this.form.full_name,
                        title: this.form.title || null,
                        role: this.form.role,
                        smtp_email: this.form.smtp_email || null
                    };
                    if (this.form.smtp_app_password) {
                        payload.smtp_app_password = this.form.smtp_app_password;
                    }
                    await api.put('users/' + this.editingId, payload);
                    showToast('User updated');
                } else {
                    await api.post('users', this.form);
                    showToast('User created');
                }
                this.showModal = false;
                this.loadData(1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to save user', 'error');
            }
            this.saving = false;
        },

        openResetPasswordModal(u) {
            this.resetUser = u;
            this.newPassword = '';
            this.showResetModal = true;
        },

        async resetPassword() {
            this.saving = true;
            try {
                await api.put('users/' + this.resetUser.id + '/reset-password', {
                    new_password: this.newPassword
                });
                showToast('Password reset successfully');
                this.showResetModal = false;
            } catch (e) {
                showToast(e.data?.message || 'Failed to reset password', 'error');
            }
            this.saving = false;
        },

        async toggleActive(u) {
            const action = u.is_active ? 'deactivate' : 'activate';
            if (!await confirmAction(`Are you sure you want to ${action} ${u.full_name}?`)) return;
            try {
                await api.put('users/' + u.id + '/toggle-active');
                showToast(`User ${action}d`);
                this.loadData(1);
            } catch (e) {
                showToast(e.data?.message || 'Action failed', 'error');
            }
        }
    };
}
