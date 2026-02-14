<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Case Detail';
$currentPage = 'cases';
ob_start();
?>

<div x-data="caseDetailPage()" x-init="init()">

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex items-center justify-center py-20"><div class="spinner"></div></div>
    </template>

    <template x-if="!loading && caseData">
        <div>
            <!-- Top bar -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <a href="/MRMS/frontend/pages/cases/index.php" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800" x-text="caseData.case_number"></h2>
                        <p class="text-gray-500" x-text="caseData.client_name"></p>
                    </div>
                    <span class="status-badge" :class="'status-' + caseData.status" x-text="getStatusLabel(caseData.status)"></span>
                </div>
                <div class="flex gap-2">
                    <button @click="showEditModal = true" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit Case</button>
                    <select x-model="caseData.status" @change="updateCaseStatus()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="active">Active</option>
                        <option value="pending_review">Pending Review</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>
            </div>

            <!-- Client info cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 mb-1">Date of Birth</p>
                    <p class="text-sm font-medium" x-text="formatDate(caseData.client_dob) || '-'"></p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 mb-1">Date of Injury</p>
                    <p class="text-sm font-medium" x-text="formatDate(caseData.doi) || '-'"></p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 mb-1">Attorney</p>
                    <p class="text-sm font-medium" x-text="caseData.attorney_name || '-'"></p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 mb-1">Assigned To</p>
                    <p class="text-sm font-medium" x-text="caseData.assigned_name || '-'"></p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-4">
                    <p class="text-xs text-gray-500 mb-1">INI Completed</p>
                    <p class="text-sm font-medium" x-text="caseData.ini_completed ? 'Yes' : 'No'"></p>
                </div>
            </div>

            <!-- Provider List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Providers</h3>
                    <button @click="showAddProviderModal = true"
                            class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Provider
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Last Follow-up</th>
                                <th>Days Elapsed</th>
                                <th>Deadline</th>
                                <th>Assigned</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="providers.length === 0">
                                <tr><td colspan="9" class="text-center text-gray-400 py-8">No providers added yet</td></tr>
                            </template>
                            <template x-for="p in providers" :key="p.id">
                                <tr>
                                    <td class="font-medium" x-text="p.provider_name"></td>
                                    <td><span class="text-xs text-gray-500" x-text="getProviderTypeLabel(p.provider_type)"></span></td>
                                    <td>
                                        <span class="status-badge" :class="'status-' + p.overall_status" x-text="getStatusLabel(p.overall_status)"></span>
                                    </td>
                                    <td x-text="formatDate(p.first_request_date) || '-'"></td>
                                    <td x-text="formatDate(p.last_request_date) || '-'"></td>
                                    <td>
                                        <span :class="p.days_since_request > 14 ? 'text-red-600 font-semibold' : 'text-gray-600'"
                                              x-text="p.days_since_request != null ? p.days_since_request + 'd' : '-'"></span>
                                    </td>
                                    <td>
                                        <span :class="p.days_until_deadline < 0 ? 'text-red-600 font-semibold' : (p.days_until_deadline <= 7 ? 'text-yellow-600' : '')"
                                              x-text="formatDate(p.deadline) || '-'"></span>
                                    </td>
                                    <td x-text="p.assigned_name || '-'"></td>
                                    <td>
                                        <div class="flex gap-1">
                                            <button @click="openRequestModal(p)" title="New Request"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            </button>
                                            <button @click="openReceiptModal(p)" title="Log Receipt"
                                                    class="p-1.5 text-green-600 hover:bg-green-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            <button @click="deleteProvider(p.id)" title="Remove"
                                                    class="p-1.5 text-red-400 hover:bg-red-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Notes & Activity</h3>
                </div>
                <div class="p-6">
                    <!-- Add note form -->
                    <form @submit.prevent="addNote()" class="mb-6">
                        <div class="flex gap-3">
                            <select x-model="newNote.note_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="general">General</option>
                                <option value="follow_up">Follow-Up</option>
                                <option value="issue">Issue</option>
                                <option value="handoff">Handoff</option>
                            </select>
                            <input type="text" x-model="newNote.content" placeholder="Add a note..."
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="submit" :disabled="!newNote.content.trim()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50">Add</button>
                        </div>
                    </form>

                    <!-- Notes list -->
                    <div class="space-y-0">
                        <template x-for="note in notes" :key="note.id">
                            <div class="timeline-item">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium text-gray-800" x-text="note.author_name"></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500" x-text="note.note_type"></span>
                                    <span class="text-xs text-gray-400" x-text="timeAgo(note.created_at)"></span>
                                </div>
                                <p class="text-sm text-gray-600" x-text="note.content"></p>
                            </div>
                        </template>
                        <template x-if="notes.length === 0">
                            <p class="text-sm text-gray-400 text-center py-4">No notes yet</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Add Provider Modal -->
    <div x-show="showAddProviderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showAddProviderModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold">Add Provider to Case</h3>
            </div>
            <form @submit.prevent="addProvider()" class="p-6 space-y-4">
                <!-- Provider search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider *</label>
                    <div class="relative">
                        <input type="text" x-model="providerSearch" @input.debounce.300ms="searchProviders()"
                               placeholder="Search provider..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <div x-show="providerResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                            <template x-for="pr in providerResults" :key="pr.id">
                                <button type="button" @click="selectProvider(pr)"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex justify-between">
                                    <span x-text="pr.name"></span>
                                    <span class="text-xs text-gray-400" x-text="getProviderTypeLabel(pr.type)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p x-show="selectedProvider" class="text-sm text-blue-600 mt-1" x-text="selectedProvider?.name"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Treatment Start</label>
                        <input type="date" x-model="newProvider.treatment_start_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Treatment End</label>
                        <input type="date" x-model="newProvider.treatment_end_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Record Types Needed</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="rt in ['medical_records','billing','chart','imaging','op_report']" :key="rt">
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" :value="rt" x-model="newProvider.record_types"
                                       class="rounded border-gray-300 text-blue-600">
                                <span x-text="rt.replace('_',' ')"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="date" x-model="newProvider.deadline"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAddProviderModal = false"
                            class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="!selectedProvider || saving"
                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">Add Provider</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Modal -->
    <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showRequestModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold">Log Record Request</h3>
                <p class="text-sm text-gray-500" x-text="currentProvider?.provider_name"></p>
            </div>
            <form @submit.prevent="submitRequest()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Request Date *</label>
                        <input type="date" x-model="newRequest.request_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Method *</label>
                        <select x-model="newRequest.request_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="fax">Fax</option>
                            <option value="email">Email</option>
                            <option value="portal">Portal</option>
                            <option value="phone">Phone</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select x-model="newRequest.request_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="initial">Initial Request</option>
                        <option value="follow_up">Follow-Up</option>
                        <option value="re_request">Re-Request</option>
                        <option value="rfd">RFD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sent To</label>
                    <input type="text" x-model="newRequest.sent_to"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Email or fax number">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="newRequest.authorization_sent" class="rounded border-gray-300 text-blue-600">
                    Authorization (HIPAA) attached
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea x-model="newRequest.notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showRequestModal = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">Log Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showReceiptModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold">Log Receipt</h3>
                <p class="text-sm text-gray-500" x-text="currentProvider?.provider_name"></p>
            </div>
            <form @submit.prevent="submitReceipt()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Received Date *</label>
                        <input type="date" x-model="newReceipt.received_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Method *</label>
                        <select x-model="newReceipt.received_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="fax">Fax</option>
                            <option value="email">Email</option>
                            <option value="portal">Portal</option>
                            <option value="mail">Mail</option>
                            <option value="in_person">In Person</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Received Items</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_medical_records" class="rounded"> Medical Records</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_billing" class="rounded"> Billing</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_chart" class="rounded"> Chart Notes</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_imaging" class="rounded"> Imaging</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="newReceipt.has_op_report" class="rounded"> Operative Report</label>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" x-model="newReceipt.is_complete" class="rounded text-green-600"> All records complete
                </label>
                <div x-show="!newReceipt.is_complete">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Incomplete Reason</label>
                    <textarea x-model="newReceipt.incomplete_reason" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Location (Sharepoint path)</label>
                    <input type="text" x-model="newReceipt.file_location"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="\\sharepoint\cases\...">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showReceiptModal = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">Log Receipt</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Case Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold">Edit Case</h3>
            </div>
            <form @submit.prevent="updateCase()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Case Number</label>
                        <input type="text" x-model="editData.case_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Name</label>
                        <input type="text" x-model="editData.client_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DOB</label>
                        <input type="date" x-model="editData.client_dob"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DOI</label>
                        <input type="date" x-model="editData.doi"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attorney</label>
                        <input type="text" x-model="editData.attorney_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select x-model="editData.assigned_to"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Unassigned</option>
                            <option value="1">Ella</option>
                            <option value="2">Micky</option>
                        </select>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="editData.ini_completed" class="rounded"> INI Completed
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea x-model="editData.notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function caseDetailPage() {
    return {
        caseId: getQueryParam('id'),
        caseData: null,
        providers: [],
        notes: [],
        loading: true,
        saving: false,

        showEditModal: false,
        showAddProviderModal: false,
        showRequestModal: false,
        showReceiptModal: false,

        editData: {},
        currentProvider: null,

        providerSearch: '',
        providerResults: [],
        selectedProvider: null,
        newProvider: { treatment_start_date: '', treatment_end_date: '', record_types: [], deadline: '' },
        newRequest: { request_date: new Date().toISOString().split('T')[0], request_method: 'fax', request_type: 'initial', sent_to: '', authorization_sent: true, notes: '' },
        newReceipt: { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, has_op_report: false, is_complete: false, incomplete_reason: '', file_location: '' },
        newNote: { note_type: 'general', content: '' },

        async init() {
            if (!this.caseId) {
                window.location.href = '/MRMS/frontend/pages/cases/index.php';
                return;
            }
            await Promise.all([this.loadCase(), this.loadProviders(), this.loadNotes()]);
            this.loading = false;
        },

        async loadCase() {
            try {
                const res = await api.get('cases/' + this.caseId);
                this.caseData = res.data;
                this.editData = { ...res.data };
            } catch (e) {
                showToast('Failed to load case', 'error');
            }
        },

        async loadProviders() {
            try {
                const res = await api.get('case-providers?case_id=' + this.caseId);
                this.providers = res.data || [];
            } catch (e) {}
        },

        async loadNotes() {
            try {
                const res = await api.get('notes?case_id=' + this.caseId);
                this.notes = res.data || [];
            } catch (e) {}
        },

        async updateCase() {
            this.saving = true;
            try {
                await api.put('cases/' + this.caseId, this.editData);
                showToast('Case updated');
                this.showEditModal = false;
                await this.loadCase();
            } catch (e) {
                showToast(e.data?.message || 'Update failed', 'error');
            }
            this.saving = false;
        },

        async updateCaseStatus() {
            try {
                await api.put('cases/' + this.caseId, { status: this.caseData.status });
                showToast('Status updated');
            } catch (e) {
                showToast('Failed to update status', 'error');
            }
        },

        async searchProviders() {
            if (this.providerSearch.length < 2) { this.providerResults = []; return; }
            try {
                const res = await api.get('providers/search?q=' + encodeURIComponent(this.providerSearch));
                this.providerResults = res.data || [];
            } catch (e) {}
        },

        selectProvider(p) {
            this.selectedProvider = p;
            this.providerSearch = p.name;
            this.providerResults = [];
        },

        async addProvider() {
            if (!this.selectedProvider) return;
            this.saving = true;
            try {
                await api.post('case-providers', {
                    case_id: parseInt(this.caseId),
                    provider_id: this.selectedProvider.id,
                    treatment_start_date: this.newProvider.treatment_start_date || null,
                    treatment_end_date: this.newProvider.treatment_end_date || null,
                    record_types_needed: this.newProvider.record_types.join(',') || null,
                    deadline: this.newProvider.deadline || null
                });
                showToast('Provider added');
                this.showAddProviderModal = false;
                this.selectedProvider = null;
                this.providerSearch = '';
                this.newProvider = { treatment_start_date: '', treatment_end_date: '', record_types: [], deadline: '' };
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to add provider', 'error');
            }
            this.saving = false;
        },

        openRequestModal(p) {
            this.currentProvider = p;
            this.newRequest = { request_date: new Date().toISOString().split('T')[0], request_method: 'fax', request_type: p.overall_status === 'not_started' ? 'initial' : 'follow_up', sent_to: '', authorization_sent: true, notes: '' };
            this.showRequestModal = true;
        },

        async submitRequest() {
            this.saving = true;
            try {
                const endpoint = this.newRequest.request_type === 'follow_up' ? 'requests/followup' : 'requests';
                await api.post(endpoint, {
                    case_provider_id: this.currentProvider.id,
                    ...this.newRequest,
                    authorization_sent: this.newRequest.authorization_sent ? 1 : 0
                });
                showToast('Request logged');
                this.showRequestModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to log request', 'error');
            }
            this.saving = false;
        },

        openReceiptModal(p) {
            this.currentProvider = p;
            this.newReceipt = { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, has_op_report: false, is_complete: false, incomplete_reason: '', file_location: '' };
            this.showReceiptModal = true;
        },

        async submitReceipt() {
            this.saving = true;
            try {
                await api.post('receipts', {
                    case_provider_id: this.currentProvider.id,
                    ...this.newReceipt,
                    has_medical_records: this.newReceipt.has_medical_records ? 1 : 0,
                    has_billing: this.newReceipt.has_billing ? 1 : 0,
                    has_chart: this.newReceipt.has_chart ? 1 : 0,
                    has_imaging: this.newReceipt.has_imaging ? 1 : 0,
                    has_op_report: this.newReceipt.has_op_report ? 1 : 0,
                    is_complete: this.newReceipt.is_complete ? 1 : 0,
                });
                showToast('Receipt logged');
                this.showReceiptModal = false;
                await this.loadProviders();
            } catch (e) {
                showToast(e.data?.message || 'Failed to log receipt', 'error');
            }
            this.saving = false;
        },

        async deleteProvider(id) {
            if (!await confirmAction('Remove this provider from the case?')) return;
            try {
                await api.delete('case-providers/' + id);
                showToast('Provider removed');
                await this.loadProviders();
            } catch (e) {
                showToast('Failed to remove provider', 'error');
            }
        },

        async addNote() {
            if (!this.newNote.content.trim()) return;
            try {
                await api.post('notes', {
                    case_id: parseInt(this.caseId),
                    ...this.newNote
                });
                this.newNote.content = '';
                await this.loadNotes();
            } catch (e) {
                showToast('Failed to add note', 'error');
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
