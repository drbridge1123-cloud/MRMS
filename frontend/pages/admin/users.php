<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requirePermission('users');
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
                <option value="accounting">Accounting</option>
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
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                                      :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : u.role === 'manager' ? 'bg-orange-100 text-orange-700' : u.role === 'accounting' ? 'bg-teal-100 text-teal-700' : 'bg-v2-bg text-v2-text'"
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
                                    <button @click="openEditModal(u)" title="Edit" class="icon-btn icon-btn-sm">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="openResetPasswordModal(u)" title="Reset Password" class="icon-btn icon-btn-sm">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </button>
                                    <button @click="toggleActive(u)" :title="u.is_active ? 'Deactivate' : 'Activate'"
                                            class="icon-btn icon-btn-sm"
                                            :class="u.is_active ? 'icon-btn-danger' : ''"
                                            :style="!u.is_active ? 'color:#16a34a' : ''">
                                        <svg x-show="u.is_active" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        <svg x-show="!u.is_active" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="showModal && (showModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showModal = false"></div>
        <div class="usm-modal relative z-10" style="width:480px;max-height:90vh;display:flex;flex-direction:column;" @click.stop>
            <form @submit.prevent="saveUser()" class="flex flex-col" style="max-height:90vh;">
                <div class="usm-header" style="flex-shrink:0;">
                    <div>
                        <div class="usm-title" x-text="isEditing ? 'Edit User' : 'New User'"></div>
                        <div class="usm-subtitle" x-show="isEditing" x-text="form.username"></div>
                    </div>
                    <button type="button" class="usm-close" @click="showModal = false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="usm-body" style="overflow-y:auto;flex:1;">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="usm-label">Username <span class="usm-req">*</span></label>
                            <input type="text" x-model="form.username" required class="usm-input">
                        </div>
                        <div>
                            <label class="usm-label">Full Name <span class="usm-req">*</span></label>
                            <input type="text" x-model="form.full_name" required class="usm-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="usm-label">Job Title</label>
                            <input type="text" x-model="form.title" placeholder="e.g., Paralegal" class="usm-input">
                        </div>
                        <template x-if="!isEditing">
                            <div>
                                <label class="usm-label">Password <span class="usm-req">*</span></label>
                                <input type="password" x-model="form.password" :required="!isEditing" minlength="6"
                                       class="usm-input" placeholder="Min 6 characters">
                            </div>
                        </template>
                        <div>
                            <label class="usm-label">Role</label>
                            <select x-model="form.role" @change="onRoleChange()" class="usm-select">
                                <option value="staff">Staff</option>
                                <option value="accounting">Accounting</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="usm-label">Card Last 4 Digits</label>
                            <input type="text" x-model="form.card_last4" maxlength="4" placeholder="e.g., 1234" class="usm-input"
                                   inputmode="numeric" pattern="[0-9]*"
                                   @input="$nextTick(() => form.card_last4 = form.card_last4.replace(/[^0-9]/g, ''))">
                        </div>
                    </div>

                    <!-- Page Access Permissions -->
                    <div class="usm-divider">
                        <span>Page Access</span>
                    </div>
                    <div class="flex items-center justify-end" style="margin-top:-8px;margin-bottom:6px;">
                        <button type="button" @click="onRoleChange()" class="text-xs hover:underline" style="color:var(--gold);">Reset to defaults</button>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5">
                        <template x-for="page in allPages" :key="page.key">
                            <label class="perm-item flex items-center gap-2.5 px-3 py-2 cursor-pointer text-sm"
                                   :class="form.permissions.includes(page.key) ? 'perm-active' : ''">
                                <input type="checkbox"
                                       :checked="form.permissions.includes(page.key)"
                                       @change="togglePermission(page.key)"
                                       class="rounded">
                                <span x-text="page.label"></span>
                            </label>
                        </template>
                    </div>

                    <!-- Email / SMTP Settings (edit mode only) -->
                    <template x-if="isEditing">
                        <div>
                            <div class="usm-divider">
                                <span>Email Settings</span>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div>
                                    <label class="usm-label">Gmail Address</label>
                                    <input type="email" x-model="form.smtp_email" placeholder="user@gmail.com" class="usm-input">
                                </div>
                                <div>
                                    <label class="usm-label">Gmail App Password</label>
                                    <input type="password" x-model="form.smtp_app_password" placeholder="Leave blank to keep current" class="usm-input">
                                    <p style="font-size:11px;color:var(--muted);margin-top:4px;">Google Account &rarr; Security &rarr; App passwords</p>
                                </div>
                                <template x-if="form.smtp_email">
                                    <p style="font-size:11px;color:#16a34a;">Emails will be sent from this user's Gmail</p>
                                </template>
                                <template x-if="!form.smtp_email">
                                    <p style="font-size:11px;color:var(--muted);">No personal email — uses firm default</p>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="usm-footer" style="flex-shrink:0;">
                    <button type="button" @click="showModal = false" class="usm-btn-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="usm-btn-submit">
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
    <div x-show="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="showResetModal && (showResetModal = false)">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="showResetModal = false"></div>
        <div class="usm-modal relative z-10" style="width:400px;" @click.stop>
            <form @submit.prevent="resetPassword()">
                <div class="usm-header">
                    <div>
                        <div class="usm-title">Reset Password</div>
                        <div class="usm-subtitle" x-text="resetUser?.full_name + ' (' + resetUser?.username + ')'"></div>
                    </div>
                    <button type="button" class="usm-close" @click="showResetModal = false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="usm-body">
                    <div>
                        <label class="usm-label">New Password <span class="usm-req">*</span></label>
                        <input type="password" x-model="newPassword" required minlength="6"
                               class="usm-input" placeholder="Min 6 characters">
                    </div>
                </div>
                <div class="usm-footer">
                    <button type="button" @click="showResetModal = false" class="usm-btn-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="usm-btn-submit">
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

<style>
.usm-modal{border-radius:12px;box-shadow:0 24px 64px rgba(0,0,0,.24);overflow:hidden;background:#fff}
.usm-header{background:#0F1B2D;padding:18px 24px;display:flex;align-items:center;justify-content:space-between}
.usm-title{font-size:15px;font-weight:700;color:#fff}
.usm-subtitle{font-size:12px;font-weight:500;color:var(--gold);margin-top:2px}
.usm-close{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;color:rgba(255,255,255,.35);transition:color .15s}
.usm-close:hover{color:rgba(255,255,255,.75)}
.usm-close svg{width:16px;height:16px}
.usm-body{padding:24px;display:flex;flex-direction:column;gap:16px}
.usm-label{display:block;font-size:9.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px}
.usm-req{color:var(--gold)}
.usm-input{width:100%;background:#fafafa;border:1.5px solid var(--border);border-radius:7px;padding:9px 12px;font-size:13px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s}
.usm-input:focus{border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.usm-select{width:100%;background:#fafafa;border:1.5px solid var(--border);border-radius:7px;padding:9px 12px;font-size:13px;outline:none;appearance:none;padding-right:30px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;transition:border-color .15s,background .15s,box-shadow .15s}
.usm-select:focus{border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.usm-divider{display:flex;align-items:center;gap:10px}
.usm-divider::before,.usm-divider::after{content:'';flex:1;height:1px;background:var(--border)}
.usm-divider span{font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.1em}
.usm-footer{padding:14px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:flex-end;gap:10px}
.usm-btn-cancel{background:#fff;border:1.5px solid var(--border);border-radius:7px;padding:9px 18px;font-size:13px;font-weight:500;color:#5A6B82;cursor:pointer;transition:border-color .15s,color .15s}
.usm-btn-cancel:hover{border-color:#94a3b8;color:#374151}
.usm-btn-submit{background:var(--gold);color:#fff;border:none;border-radius:7px;padding:9px 22px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(201,168,76,.35);display:flex;align-items:center;gap:6px;transition:opacity .15s}
.usm-btn-submit:hover{opacity:.92}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
