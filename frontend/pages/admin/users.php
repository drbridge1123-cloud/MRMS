<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAdmin();
$pageTitle = 'User Management';
$currentPage = 'admin-users';
ob_start();
?>

<div x-data="usersPage()" x-init="loadData()">

    <!-- Top bar -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="loadData(1)"
                       placeholder="Search users..."
                       class="w-56 pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
            </div>
            <select x-model="roleFilter" @change="loadData(1)"
                    class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="staff">Staff</option>
            </select>
            <select x-model="activeFilter" @change="loadData(1)"
                    class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <button @click="openCreateModal()"
                class="bg-gold text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gold-hover flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New User
        </button>
    </div>

    <!-- Users table -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('id')"><div class="flex items-center gap-1">ID <template x-if="sortBy==='id'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('username')"><div class="flex items-center gap-1">Username <template x-if="sortBy==='username'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('full_name')"><div class="flex items-center gap-1">Full Name <template x-if="sortBy==='full_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('role')"><div class="flex items-center gap-1">Role <template x-if="sortBy==='role'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Email</th>
                        <th class="cursor-pointer select-none" @click="sort('is_active')"><div class="flex items-center gap-1">Status <template x-if="sortBy==='is_active'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('created_at')"><div class="flex items-center gap-1">Created <template x-if="sortBy==='created_at'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="8" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr>
                    </template>
                    <template x-if="!loading && users.length === 0">
                        <tr><td colspan="8" class="text-center text-v2-text-light py-8">No users found</td></tr>
                    </template>
                    <template x-for="u in users" :key="u.id">
                        <tr>
                            <td class="text-v2-text-light" x-text="u.id"></td>
                            <td class="font-medium" x-text="u.username"></td>
                            <td x-text="u.full_name"></td>
                            <td>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : u.role === 'manager' ? 'bg-orange-100 text-orange-700' : 'bg-v2-bg text-v2-text'"
                                      x-text="u.role"></span>
                            </td>
                            <td>
                                <span class="text-xs text-v2-text-light" x-text="u.smtp_email || '-'"></span>
                            </td>
                            <td>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                      x-text="u.is_active ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="text-v2-text-light" x-text="formatDate(u.created_at)"></td>
                            <td>
                                <div class="flex gap-1">
                                    <button @click="openEditModal(u)" title="Edit"
                                            class="p-1.5 text-gold hover:bg-v2-bg rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="openResetPasswordModal(u)" title="Reset Password"
                                            class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </button>
                                    <button @click="toggleActive(u)" :title="u.is_active ? 'Deactivate' : 'Activate'"
                                            class="p-1.5 rounded"
                                            :class="u.is_active ? 'text-red-500 hover:bg-red-50' : 'text-green-500 hover:bg-green-50'">
                                        <svg x-show="u.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <svg x-show="!u.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                <h3 class="text-lg font-semibold" x-text="isEditing ? 'Edit User' : 'New User'"></h3>
                <button @click="showModal = false" class="text-v2-text-light hover:text-v2-text-mid">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveUser()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Username *</label>
                    <input type="text" x-model="form.username" required
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Full Name *</label>
                    <input type="text" x-model="form.full_name" required
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Job Title</label>
                    <input type="text" x-model="form.title" placeholder="e.g., Legal Assistant, Paralegal, Attorney"
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <template x-if="!isEditing">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Password *</label>
                        <input type="password" x-model="form.password" :required="!isEditing" minlength="6"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"
                               placeholder="Min 6 characters">
                    </div>
                </template>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Role</label>
                    <select x-model="form.role"
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <!-- Email / SMTP Settings (edit mode only) -->
                <template x-if="isEditing">
                    <div class="border-t border-v2-card-border pt-4 space-y-3">
                        <p class="text-xs font-semibold text-v2-text-mid uppercase tracking-wider">Email Settings</p>
                        <div>
                            <label class="block text-sm font-medium text-v2-text mb-1">Gmail Address</label>
                            <input type="email" x-model="form.smtp_email" placeholder="user@gmail.com"
                                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-v2-text mb-1">Gmail App Password</label>
                            <input type="password" x-model="form.smtp_app_password" placeholder="Leave blank to keep current"
                                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                            <p class="text-xs text-v2-text-light mt-1">Google Account &rarr; Security &rarr; App passwords</p>
                        </div>
                        <template x-if="form.smtp_email">
                            <p class="text-xs text-green-600">Emails will be sent from this user's Gmail</p>
                        </template>
                        <template x-if="!form.smtp_email">
                            <p class="text-xs text-v2-text-light">No personal email — uses firm default</p>
                        </template>
                    </div>
                </template>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : (isEditing ? 'Update' : 'Create')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showResetModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h3 class="text-lg font-semibold">Reset Password</h3>
                <p class="text-sm text-v2-text-light" x-text="resetUser?.full_name + ' (' + resetUser?.username + ')'"></p>
            </div>
            <form @submit.prevent="resetPassword()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">New Password *</label>
                    <input type="password" x-model="newPassword" required minlength="6"
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"
                           placeholder="Min 6 characters">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showResetModal = false"
                            class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="px-4 py-2 text-sm text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 disabled:opacity-50">
                        <span x-text="saving ? 'Resetting...' : 'Reset Password'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function usersPage() {
    return {
        users: [],
        pagination: null,
        loading: true,
        saving: false,
        searchQuery: '',
        roleFilter: '',
        activeFilter: '',
        sortBy: '',
        sortDir: 'asc',

        showModal: false,
        isEditing: false,
        editingId: null,
        form: { username: '', full_name: '', title: '', password: '', role: 'staff' },

        showResetModal: false,
        resetUser: null,
        newPassword: '',

        async loadData(page = 1) {
            this.loading = true;
            const params = buildQueryString({
                page,
                search: this.searchQuery,
                role: this.roleFilter,
                is_active: this.activeFilter,
                sort_by: this.sortBy,
                sort_dir: this.sortDir
            });
            try {
                const res = await api.get('users' + params);
                this.users = res.data || [];
                this.pagination = res.pagination || null;
            } catch (e) {}
            this.loading = false;
        },

        sort(column) {
            if (this.sortBy === column) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDir = 'asc';
            }
            this.loadData(1);
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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
