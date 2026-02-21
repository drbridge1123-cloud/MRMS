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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="closeModal()"></div>
        <div class="modal-v2 relative w-full max-w-6xl z-10 max-h-[90vh] flex flex-col" @click.stop>
            <!-- Modal Header -->
            <div class="modal-v2-header">
                <div class="modal-v2-title" x-text="editingTemplate ? 'Edit Template' : 'Create Template'"></div>
                <button type="button" class="modal-v2-close" @click="closeModal()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-v2-body flex-1 overflow-y-auto">
                <div class="grid grid-cols-3 gap-6">
                    <!-- Main Form (2 columns) -->
                    <div class="col-span-2 space-y-4">
                        <div>
                            <label class="form-v2-label">Template Name *</label>
                            <input type="text" x-model="form.name" class="form-v2-input" placeholder="e.g., Medical Records Request - Standard">
                        </div>

                        <div>
                            <label class="form-v2-label">Description</label>
                            <textarea x-model="form.description" rows="2" class="form-v2-textarea" placeholder="Brief description of this template"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-v2-label">Template Type *</label>
                                <select x-model="form.template_type" class="form-v2-select">
                                    <option value="medical_records">Medical Records</option>
                                    <option value="health_ledger">Health Ledger</option>
                                    <option value="bulk_request">Bulk Request</option>
                                    <option value="balance_verification">Balance Verification</option>
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
                            <label class="form-v2-label">Subject Template</label>
                            <input type="text" x-model="form.subject_template" class="form-v2-input" placeholder="e.g., Medical Records Request - {{client_name}}">
                        </div>

                        <div>
                            <label class="form-v2-label">Body Template * (HTML with placeholders)</label>
                            <textarea x-model="form.body_template" rows="20" class="form-v2-textarea font-mono text-sm" placeholder="Enter HTML template with {{placeholders}}"></textarea>
                        </div>

                        <template x-if="editingTemplate">
                            <div>
                                <label class="form-v2-label">Change Notes</label>
                                <input type="text" x-model="form.change_notes" class="form-v2-input" placeholder="Describe what changed in this version">
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
            <div class="modal-v2-footer" style="justify-content:space-between;">
                <button type="button" @click="closeModal()" class="btn-v2-cancel">
                    Cancel
                </button>
                <div class="flex gap-3">
                    <button type="button" @click="previewCurrent()" class="btn-v2-cancel" style="border-color:var(--gold);color:var(--gold);">
                        Preview
                    </button>
                    <button type="button" @click="saveTemplate()" class="btn-v2-primary">
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
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="closePreviewModal()"></div>
        <div class="modal-v2 relative w-full max-w-4xl z-10 max-h-[90vh] flex flex-col" @click.stop>
            <div class="modal-v2-header">
                <div class="modal-v2-title">Template Preview</div>
                <button type="button" class="modal-v2-close" @click="closePreviewModal()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-v2-body flex-1 overflow-y-auto">
                <div x-html="previewHtml"></div>
            </div>
            <div class="modal-v2-footer">
                <button type="button" @click="closePreviewModal()" class="btn-v2-cancel">Close</button>
            </div>
        </div>
    </div>

    <!-- Version History Modal -->
    <div x-show="showVersionsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="closeVersionsModal()"></div>
        <div class="modal-v2 relative w-full max-w-3xl z-10 max-h-[90vh] flex flex-col" @click.stop>
            <div class="modal-v2-header">
                <div class="modal-v2-title">Version History</div>
                <button type="button" class="modal-v2-close" @click="closeVersionsModal()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-v2-body flex-1 overflow-y-auto">
                <div class="space-y-3">
                    <template x-for="version in versions" :key="version.id">
                        <div class="border border-v2-card-border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-v2-text">Version <span x-text="version.version_number"></span></span>
                                <span class="text-xs text-v2-text-light" x-text="new Date(version.created_at).toLocaleString()"></span>
                            </div>
                            <div class="text-sm text-v2-text-mid mb-1">
                                Changed by: <span x-text="version.changed_by_name || 'System'"></span>
                            </div>
                            <div class="text-sm text-v2-text-light" x-text="version.change_notes || 'No notes'"></div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="modal-v2-footer">
                <button type="button" @click="closeVersionsModal()" class="btn-v2-cancel">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
