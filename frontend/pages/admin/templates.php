<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requirePermission('templates');
$pageTitle = 'Letter Templates';
$currentPage = 'admin-templates';
$pageScripts = ['/MRMS/frontend/assets/js/pages/admin/templates.js'];
ob_start();
?>

<div x-data="templatesPage()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-v2-text">Letter Templates</h1>
            <p class="text-sm text-v2-text-light mt-1">Manage letter templates with placeholder support</p>
        </div>
        <button @click="openCreateModal()" class="btn-primary flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Template
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border p-4 mb-6">
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-v2-text mb-2">Filter by Type</label>
                <select x-model="filterType" @change="loadTemplates()" class="w-full px-3 py-2 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold outline-none">
                    <option value="">All Types</option>
                    <option value="medical_records">Medical Records</option>
                    <option value="health_ledger">Health Ledger</option>
                    <option value="bulk_request">Bulk Request</option>
                    <option value="balance_verification">Balance Verification</option>
                    <option value="medical_discount">Medical Discount</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="activeOnly" @change="loadTemplates()" class="w-4 h-4 text-gold border-gray-300 rounded focus:ring-gold">
                    <span class="text-sm text-v2-text">Active only</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-v2-bg border-b border-v2-card-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-v2-text uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-v2-text uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-v2-text uppercase">Default</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-v2-text uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-v2-text uppercase">Created By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-v2-text uppercase">Updated</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-v2-text uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-v2-card-border">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-v2-text-light">Loading...</td>
                        </tr>
                    </template>
                    <template x-if="!loading && templates.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-v2-text-light">No templates found</td>
                        </tr>
                    </template>
                    <template x-for="template in templates" :key="template.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-v2-text" x-text="template.name"></div>
                                <div class="text-xs text-v2-text-light mt-1" x-text="template.description || 'No description'"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 whitespace-nowrap" x-text="template.template_type.replace('_', ' ')"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span x-show="template.is_default" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gold text-white">
                                    ✓ Default
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="template.is_active ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium" x-text="template.is_active ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-v2-text-mid" x-text="template.created_by_name || 'System'"></td>
                            <td class="px-6 py-4 text-sm text-v2-text-mid" x-text="new Date(template.updated_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="viewVersions(template)" title="Version History" class="p-1.5 rounded text-v2-text-mid hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    <button @click="previewTemplate(template)" title="Preview" class="p-1.5 rounded text-blue-600 hover:bg-blue-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button @click="editTemplate(template)" title="Edit" class="p-1.5 rounded text-gold hover:bg-gold/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteTemplate(template)" title="Delete" class="p-1.5 rounded text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showModal && closeModal()">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closeModal()"></div>
        <div class="tpm-modal relative z-10" style="width:1000px;max-height:90vh;display:flex;flex-direction:column;" @click.stop>
            <!-- Modal Header -->
            <div class="tpm-header">
                <div>
                    <div class="tpm-title" x-text="editingTemplate ? 'Edit Template' : 'Create Template'"></div>
                    <div class="tpm-subtitle">HTML template with placeholder support</div>
                </div>
                <button type="button" class="tpm-close" @click="closeModal()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="tpm-body" style="flex:1;overflow-y:auto;">
                <div class="grid grid-cols-3 gap-6">
                    <!-- Main Form (2 columns) -->
                    <div class="col-span-2 space-y-4">
                        <div>
                            <label class="tpm-label">Template Name <span class="tpm-req">*</span></label>
                            <input type="text" x-model="form.name" class="tpm-input" placeholder="e.g., Medical Records Request - Standard">
                        </div>

                        <div>
                            <label class="tpm-label">Description</label>
                            <textarea x-model="form.description" rows="2" class="tpm-textarea" placeholder="Brief description of this template"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="tpm-label">Template Type <span class="tpm-req">*</span></label>
                                <select x-model="form.template_type" class="tpm-select">
                                    <option value="medical_records">Medical Records</option>
                                    <option value="health_ledger">Health Ledger</option>
                                    <option value="bulk_request">Bulk Request</option>
                                    <option value="balance_verification">Balance Verification</option>
                                    <option value="medical_discount">Medical Discount</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="form.is_default" class="w-4 h-4 text-gold border-gray-300 rounded focus:ring-gold">
                                    <span class="text-sm text-v2-text">Set as default template</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="tpm-label">Subject Template</label>
                            <input type="text" x-model="form.subject_template" class="tpm-input" placeholder="e.g., Medical Records Request - {{client_name}}">
                        </div>

                        <div>
                            <label class="tpm-label">Body Template <span class="tpm-req">*</span> (HTML with placeholders)</label>
                            <textarea x-model="form.body_template" rows="20" class="tpm-textarea font-mono text-sm" placeholder="Enter HTML template with {{placeholders}}"></textarea>
                        </div>

                        <template x-if="editingTemplate">
                            <div>
                                <label class="tpm-label">Change Notes</label>
                                <input type="text" x-model="form.change_notes" class="tpm-input" placeholder="Describe what changed in this version">
                            </div>
                        </template>
                    </div>

                    <!-- Placeholder Reference (1 column) -->
                    <div class="col-span-1">
                        <div class="sticky top-0 bg-gray-50 rounded-lg border border-v2-card-border p-4">
                            <h3 class="text-sm font-semibold text-v2-text mb-3">Available Placeholders</h3>
                            <div class="space-y-2 text-xs max-h-[600px] overflow-y-auto">
                                <div><code class="bg-white px-2 py-1 rounded">{{`firm_name}}`</code> - Firm name</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`firm_address}}`</code> - Firm address</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`firm_phone}}`</code> - Firm phone</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`client_name}}`</code> - Client name</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`case_number}}`</code> - Case number</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`doi|date:m/d/Y}}`</code> - Date of injury</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`provider_name}}`</code> - Provider</div>
                                <div><code class="bg-white px-2 py-1 rounded">{{`record_types_list}}`</code> - Records list</div>
                                <div class="pt-2 border-t border-gray-300">
                                    <strong>Conditionals:</strong>
                                    <pre class="bg-white p-2 rounded mt-1 text-xs">{{`#if authorization_sent}}`}
Text if true
{{`else}}`}
Text if false
{{`/if}}`}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="tpm-footer" style="justify-content:space-between;">
                <button type="button" @click="closeModal()" class="tpm-btn-cancel">
                    Cancel
                </button>
                <div class="flex gap-3">
                    <button type="button" @click="previewCurrent()" class="tpm-btn-cancel" style="border-color:var(--gold);color:var(--gold);">
                        Preview
                    </button>
                    <button type="button" @click="saveTemplate()" class="tpm-btn-submit">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="editingTemplate ? 'Update Template' : 'Create Template'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showPreviewModal && closePreviewModal()">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closePreviewModal()"></div>
        <div class="tpm-modal relative z-10" style="width:800px;max-height:90vh;display:flex;flex-direction:column;" @click.stop>
            <div class="tpm-header">
                <div>
                    <div class="tpm-title">Template Preview</div>
                    <div class="tpm-subtitle">Rendered output preview</div>
                </div>
                <button type="button" class="tpm-close" @click="closePreviewModal()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="tpm-body" style="flex:1;overflow-y:auto;">
                <div x-html="previewHtml"></div>
            </div>
            <div class="tpm-footer">
                <button type="button" @click="closePreviewModal()" class="tpm-btn-cancel">Close</button>
            </div>
        </div>
    </div>

    <!-- Version History Modal -->
    <div x-show="showVersionsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;" @keydown.escape.window="showVersionsModal && closeVersionsModal()">
        <div class="fixed inset-0" style="background:rgba(0,0,0,.45);" @click="closeVersionsModal()"></div>
        <div class="tpm-modal relative z-10" style="width:680px;max-height:90vh;display:flex;flex-direction:column;" @click.stop>
            <div class="tpm-header">
                <div>
                    <div class="tpm-title">Version History</div>
                    <div class="tpm-subtitle">Template revision log</div>
                </div>
                <button type="button" class="tpm-close" @click="closeVersionsModal()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="tpm-body" style="flex:1;overflow-y:auto;">
                <div class="space-y-3">
                    <template x-for="(version, idx) in versions" :key="version.id">
                        <div class="border border-v2-card-border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-v2-text">Version <span x-text="version.version_number"></span></span>
                                    <span x-show="idx === 0" class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Current</span>
                                </div>
                                <span class="text-xs text-v2-text-light" x-text="new Date(version.created_at).toLocaleString()"></span>
                            </div>
                            <div class="text-sm text-v2-text-mid mb-1">
                                Changed by: <span x-text="version.changed_by_name || 'System'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-v2-text-light" x-text="version.change_notes || 'No notes'"></div>
                                <button x-show="idx !== 0" @click="restoreVersion(version)"
                                        class="px-3 py-1 text-xs font-medium rounded-lg border border-gold text-gold hover:bg-gold/10 transition-colors flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                    Restore
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="tpm-footer">
                <button type="button" @click="closeVersionsModal()" class="tpm-btn-cancel">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.tpm-modal{border-radius:12px;box-shadow:0 24px 64px rgba(0,0,0,.24);overflow:hidden;background:#fff}
.tpm-header{background:#0F1B2D;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.tpm-title{font-size:15px;font-weight:700;color:#fff}
.tpm-subtitle{font-size:12px;font-weight:500;color:var(--gold);margin-top:2px}
.tpm-close{width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;color:rgba(255,255,255,.35);transition:color .15s}
.tpm-close:hover{color:rgba(255,255,255,.75)}
.tpm-close svg{width:16px;height:16px}
.tpm-body{padding:24px;display:flex;flex-direction:column;gap:16px}
.tpm-label{display:block;font-size:9.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px}
.tpm-req{color:var(--gold)}
.tpm-input{width:100%;background:#fafafa;border:1.5px solid var(--border);border-radius:7px;padding:9px 12px;font-size:13px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s}
.tpm-input:focus{border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.tpm-textarea{width:100%;background:#fafafa;border:1.5px solid var(--border);border-radius:7px;padding:9px 12px;font-size:13px;outline:none;resize:vertical;min-height:70px;line-height:1.5;transition:border-color .15s,background .15s,box-shadow .15s}
.tpm-textarea:focus{border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.tpm-select{width:100%;background:#fafafa;border:1.5px solid var(--border);border-radius:7px;padding:9px 12px;font-size:13px;outline:none;appearance:none;padding-right:30px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;transition:border-color .15s,background .15s,box-shadow .15s}
.tpm-select:focus{border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.tpm-footer{padding:14px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-shrink:0}
.tpm-btn-cancel{background:#fff;border:1.5px solid var(--border);border-radius:7px;padding:9px 18px;font-size:13px;font-weight:500;color:#5A6B82;cursor:pointer;transition:border-color .15s,color .15s}
.tpm-btn-cancel:hover{border-color:#94a3b8;color:#374151}
.tpm-btn-submit{background:var(--gold);color:#fff;border:none;border-radius:7px;padding:9px 22px;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(201,168,76,.35);display:flex;align-items:center;gap:6px;transition:opacity .15s}
.tpm-btn-submit:hover{opacity:.92}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
