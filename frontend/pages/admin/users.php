<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAdmin();
$pageTitle = 'User Management';
$currentPage = 'admin-users';
$pageScripts = ['/MRMS/frontend/assets/js/pages/admin/users.js'];
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
                <input type="text" x-model="search" @input.debounce.300ms="loadData(1)"
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
                    <template x-if="!loading && items.length === 0">
                        <tr><td colspan="8" class="text-center text-v2-text-light py-8">No users found</td></tr>
                    </template>
                    <template x-for="u in items" :key="u.id">
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
        <div class="modal-v2-backdrop fixed inset-0" @click="showModal = false"></div>
        <div class="modal-v2 relative w-full max-w-md z-10" @click.stop>
            <form @submit.prevent="saveUser()">
                <div class="modal-v2-header">
                    <div class="modal-v2-title" x-text="isEditing ? 'Edit User' : 'New User'"></div>
                    <button type="button" class="modal-v2-close" @click="showModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div>
                        <label class="form-v2-label">Username *</label>
                        <input type="text" x-model="form.username" required class="form-v2-input">
                    </div>
                    <div>
                        <label class="form-v2-label">Full Name *</label>
                        <input type="text" x-model="form.full_name" required class="form-v2-input">
                    </div>
                    <div>
                        <label class="form-v2-label">Job Title</label>
                        <input type="text" x-model="form.title" placeholder="e.g., Legal Assistant, Paralegal, Attorney" class="form-v2-input">
                    </div>
                    <template x-if="!isEditing">
                        <div>
                            <label class="form-v2-label">Password *</label>
                            <input type="password" x-model="form.password" :required="!isEditing" minlength="6"
                                   class="form-v2-input" placeholder="Min 6 characters">
                        </div>
                    </template>
                    <div>
                        <label class="form-v2-label">Role</label>
                        <select x-model="form.role" class="form-v2-select">
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
                                <label class="form-v2-label">Gmail Address</label>
                                <input type="email" x-model="form.smtp_email" placeholder="user@gmail.com" class="form-v2-input">
                            </div>
                            <div>
                                <label class="form-v2-label">Gmail App Password</label>
                                <input type="password" x-model="form.smtp_app_password" placeholder="Leave blank to keep current" class="form-v2-input">
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
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="saving ? 'Saving...' : (isEditing ? 'Update' : 'Create')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showResetModal = false"></div>
        <div class="modal-v2 relative w-full max-w-sm z-10" @click.stop>
            <form @submit.prevent="resetPassword()">
                <div class="modal-v2-header">
                    <div>
                        <div class="modal-v2-title">Reset Password</div>
                        <p class="text-sm text-v2-text-light" x-text="resetUser?.full_name + ' (' + resetUser?.username + ')'"></p>
                    </div>
                    <button type="button" class="modal-v2-close" @click="showResetModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div>
                        <label class="form-v2-label">New Password *</label>
                        <input type="password" x-model="newPassword" required minlength="6"
                               class="form-v2-input" placeholder="Min 6 characters">
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showResetModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary" style="background:#ca8a04;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span x-text="saving ? 'Resetting...' : 'Reset Password'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
