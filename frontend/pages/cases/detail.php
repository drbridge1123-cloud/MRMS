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
        <div class="flex items-center justify-center py-20">
            <div class="spinner"></div>
        </div>
    </template>

    <template x-if="!loading && caseData">
        <div>
            <!-- Top bar -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <a href="/MRMS/frontend/pages/cases/index.php" class="text-v2-text-light hover:text-v2-text-mid">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold text-v2-text" x-text="caseData.case_number"></h2>
                        <p class="text-v2-text-light" x-text="caseData.client_name"></p>
                    </div>
                    <span class="status-badge" :class="'status-' + caseData.status"
                        x-text="getStatusLabel(caseData.status)"></span>
                </div>
                <div class="flex gap-2">
                    <button @click="showEditModal = true"
                        class="px-4 py-2 text-sm border border-v2-card-border rounded-lg hover:bg-v2-bg">Edit
                        Case</button>
                    <select x-model="caseData.status" @change="updateCaseStatus()"
                        class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                        <option value="active">Active</option>
                        <option value="pending_review">Pending Review</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>
            </div>

            <!-- Client info cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Date of Birth</p>
                    <p class="text-sm font-medium" x-text="formatDate(caseData.client_dob) || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Date of Injury</p>
                    <p class="text-sm font-medium" x-text="formatDate(caseData.doi) || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Attorney</p>
                    <p class="text-sm font-medium" x-text="caseData.attorney_name || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">Assigned To</p>
                    <p class="text-sm font-medium" x-text="caseData.assigned_name || '-'"></p>
                </div>
                <div class="info-card bg-white rounded-lg border border-v2-card-border p-4">
                    <p class="text-xs text-v2-text-light mb-1">INI Completed</p>
                    <p class="text-sm font-medium" x-text="caseData.ini_completed ? 'Yes' : 'No'"></p>
                </div>
            </div>

            <!-- Provider List -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-6">
                <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                    <h3 class="font-semibold text-v2-text">Providers</h3>
                    <button @click="showAddProviderModal = true"
                        class="bg-gold text-white px-3 py-1.5 rounded-lg text-sm hover:bg-gold-hover flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Provider
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="cursor-pointer select-none" @click="sortProviders('provider_name')">
                                    <div class="flex items-center gap-1">Provider <template
                                            x-if="provSortBy==='provider_name'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th class="cursor-pointer select-none" @click="sortProviders('provider_type')">
                                    <div class="flex items-center gap-1">Type <template
                                            x-if="provSortBy==='provider_type'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th class="cursor-pointer select-none" @click="sortProviders('overall_status')">
                                    <div class="flex items-center gap-1">Status <template
                                            x-if="provSortBy==='overall_status'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th class="cursor-pointer select-none" @click="sortProviders('first_request_date')">
                                    <div class="flex items-center gap-1">Request Date <template
                                            x-if="provSortBy==='first_request_date'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th class="cursor-pointer select-none" @click="sortProviders('last_request_date')">
                                    <div class="flex items-center gap-1">Last Follow-up <template
                                            x-if="provSortBy==='last_request_date'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th>Days Elapsed</th>
                                <th class="cursor-pointer select-none" @click="sortProviders('deadline')">
                                    <div class="flex items-center gap-1">Deadline <template
                                            x-if="provSortBy==='deadline'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th class="cursor-pointer select-none" @click="sortProviders('assigned_name')">
                                    <div class="flex items-center gap-1">Assigned <template
                                            x-if="provSortBy==='assigned_name'"><svg class="w-3 h-3"
                                                :class="provSortDir==='asc'?'':'rotate-180'" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg></template></div>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody x-show="providers.length === 0">
                            <tr>
                                <td colspan="9" class="text-center text-v2-text-light py-8">No providers added yet</td>
                            </tr>
                        </tbody>
                        <template x-for="p in providers" :key="p.id">
                            <tbody>
                                <tr @click="toggleRequestHistory(p.id)" class="cursor-pointer hover:bg-v2-bg/50"
                                    :class="expandedProvider === p.id ? 'provider-expanded-row' : ''">
                                    <td class="font-medium">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-3 h-3 text-v2-text-light transition-transform"
                                                :class="expandedProvider === p.id ? 'rotate-90' : ''" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                            <span x-text="p.provider_name"></span>
                                        </div>
                                    </td>
                                    <td><span class="text-xs text-v2-text-light"
                                            x-text="getProviderTypeLabel(p.provider_type)"></span></td>
                                    <td>
                                        <span class="status-badge" :class="'status-' + p.overall_status"
                                            x-text="getStatusLabel(p.overall_status)"></span>
                                    </td>
                                    <td x-text="formatDate(p.first_request_date) || '-'"></td>
                                    <td x-text="formatDate(p.last_request_date) || '-'"></td>
                                    <td>
                                        <span
                                            :class="p.days_since_request > 14 ? 'text-red-600 font-semibold' : 'text-v2-text-mid'"
                                            x-text="p.days_since_request != null ? p.days_since_request + 'd' : '-'"></span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <span
                                                :class="p.days_until_deadline < 0 ? 'text-red-600 font-semibold' : (p.days_until_deadline <= 7 ? 'text-yellow-600' : '')"
                                                x-text="formatDate(p.deadline) || '-'"></span>
                                            <button @click.stop="openDeadlineModal(p)" title="Change Deadline"
                                                class="p-0.5 text-v2-text-light hover:text-gold rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <template x-if="p.escalation_tier && p.escalation_tier !== 'normal'">
                                                <span class="escalation-badge"
                                                    :class="p.escalation_css + (p.escalation_tier === 'admin' ? ' escalation-pulse' : '')"
                                                    x-text="p.escalation_label"></span>
                                            </template>
                                        </div>
                                    </td>
                                    <td x-text="p.assigned_name || '-'"></td>
                                    <td>
                                        <div class="flex gap-1" @click.stop>
                                            <button @click="openRequestModal(p)" title="New Request"
                                                class="p-1.5 text-gold hover:bg-v2-bg rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                </svg>
                                            </button>
                                            <button @click="openReceiptModal(p)" title="Log Receipt"
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <button @click="markComplete(p)" title="Mark Complete"
                                                x-show="p.overall_status !== 'received_complete' && p.overall_status !== 'verified'"
                                                class="p-1 rounded hover:bg-emerald-100 text-emerald-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button @click="deleteProvider(p.id)" title="Remove"
                                                class="p-1.5 text-red-400 hover:bg-red-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Request history row (directly below this provider) -->
                                <tr x-show="expandedProvider === p.id" x-transition :id="'history-' + p.id">
                                    <td colspan="9" class="history-panel px-4 py-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-xs font-bold tracking-wider text-v2-text-mid uppercase"
                                                x-text="'REQUEST HISTORY (' + (expandedProvider === p.id ? requestHistory.length : 0) + ')'">
                                            </h4>
                                            <button @click.stop="openRequestModal(p)"
                                                class="flex items-center gap-1 text-xs font-medium text-gold hover:text-gold-hover">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Log Request
                                            </button>
                                        </div>

                                        <template x-if="requestHistory.length === 0 && expandedProvider === p.id">
                                            <p class="text-sm text-v2-text-light text-center py-4">No requests yet</p>
                                        </template>

                                        <div class="space-y-2">
                                            <template x-for="req in (expandedProvider === p.id ? requestHistory : [])"
                                                :key="req.id">
                                                <div class="bg-white rounded-lg border border-v2-card-border px-4 py-3">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-3 text-xs">
                                                            <span class="text-v2-text-mid font-medium"
                                                                x-text="formatDate(req.request_date)"></span>
                                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                                :class="{
                                                                      'bg-teal-100 text-teal-700': req.request_method === 'email',
                                                                      'bg-purple-100 text-purple-700': req.request_method === 'fax',
                                                                      'bg-blue-100 text-blue-700': req.request_method === 'portal',
                                                                      'bg-amber-100 text-amber-700': req.request_method === 'phone',
                                                                      'bg-gray-100 text-gray-700': req.request_method === 'mail'
                                                                  }"
                                                                x-text="getRequestMethodLabel(req.request_method)"></span>
                                                            <span class="text-v2-text-light"
                                                                x-text="getRequestTypeLabel(req.request_type)"></span>
                                                            <template x-if="req.sent_to">
                                                                <span
                                                                    class="text-v2-text-light flex items-center gap-1">
                                                                    <span>&rarr;</span>
                                                                    <span x-text="req.sent_to"></span>
                                                                </span>
                                                            </template>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <span class="send-status-badge"
                                                                :class="'send-status-' + (req.send_status || 'draft')"
                                                                x-text="getSendStatusLabel(req.send_status || 'draft')"></span>
                                                            <span class="text-xs text-v2-text-light"
                                                                x-text="req.requested_by_name || ''"></span>
                                                            <div class="flex items-center gap-1">
                                                                <template
                                                                    x-if="['email','fax'].includes(req.request_method) && req.send_status !== 'sent'">
                                                                    <button @click.stop="openPreviewModal(req)"
                                                                        title="Preview & Send"
                                                                        class="p-1 rounded text-gold hover:bg-gold/10">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                                        </svg>
                                                                    </button>
                                                                </template>
                                                                <template x-if="['draft', 'failed'].includes(req.send_status)">
                                                                    <button @click.stop="deleteRequest(req)"
                                                                        title="Delete draft request"
                                                                        class="p-1 rounded text-red-600 hover:bg-red-50">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </button>
                                                                </template>
                                                                <template x-if="req.send_status === 'sent'">
                                                                    <span class="p-1 text-green-500" title="Sent">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <template x-if="req.notes">
                                                        <p class="text-xs text-v2-text-light mt-1.5 pl-0.5"
                                                            x-text="req.notes"></p>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </template>
                    </table>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-6"
                x-data="documentUploader(caseId)" x-init="init()">
                <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                    <h3 class="font-semibold text-v2-text">Documents</h3>
                    <button @click="$refs.fileInput.click()"
                        class="bg-gold text-white px-3 py-1.5 rounded-lg text-sm hover:bg-gold-hover flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload
                    </button>
                </div>
                <div class="p-6">
                    <!-- Upload Area -->
                    <div class="mb-6"
                        @dragover="handleDragOver($event)"
                        @dragleave="handleDragLeave()"
                        @drop="handleDrop($event)">
                        <!-- Drag and Drop Zone -->
                        <div class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
                            :class="dragOver ? 'border-gold bg-gold/5' : 'border-v2-card-border hover:border-gold/50'">
                            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.tif,.tiff"
                                class="hidden">

                            <template x-if="!selectedFile">
                                <div>
                                    <svg class="w-12 h-12 mx-auto text-v2-text-light mb-3" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm text-v2-text mb-1">Drag and drop files here, or</p>
                                    <button type="button" @click="$refs.fileInput.click()"
                                        class="text-sm text-gold hover:text-gold-hover font-medium">
                                        Browse files
                                    </button>
                                    <p class="text-xs text-v2-text-light mt-2">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TIFF (max 10MB)</p>
                                </div>
                            </template>

                            <template x-if="selectedFile">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between bg-v2-bg rounded-lg p-3">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <div class="text-left">
                                                <p class="text-sm font-medium text-v2-text" x-text="selectedFile.name"></p>
                                                <p class="text-xs text-v2-text-light" x-text="(selectedFile.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="selectedFile = null"
                                            class="text-v2-text-light hover:text-red-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Document Type Selection -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-v2-text mb-1">Document Type</label>
                                            <select x-model="uploadForm.document_type"
                                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                                                <option value="hipaa_authorization">HIPAA Authorization</option>
                                                <option value="signed_release">Signed Release</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-v2-text mb-1">Notes (Optional)</label>
                                            <input type="text" x-model="uploadForm.notes" placeholder="Add notes..."
                                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                                        </div>
                                    </div>

                                    <!-- Provider Template Mode -->
                                    <div class="border-t border-v2-card-border pt-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="uploadForm.is_provider_template"
                                                class="rounded border-gray-300 text-gold focus:ring-gold">
                                            <span class="text-xs font-medium text-v2-text">
                                                This is a provider name template
                                                <span class="text-v2-text-light">(allows changing provider name for different providers)</span>
                                            </span>
                                        </label>

                                        <!-- Template Coordinates (shown when template mode is enabled) -->
                                        <template x-if="uploadForm.is_provider_template">
                                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg space-y-2">
                                                <p class="text-xs text-blue-800 font-medium">Provider Name Location</p>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <label class="block text-xs text-blue-700 mb-1">X Position</label>
                                                        <input type="number" x-model.number="uploadForm.provider_name_x" placeholder="e.g., 50"
                                                            class="w-full px-2 py-1 border border-blue-300 rounded text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-blue-700 mb-1">Y Position</label>
                                                        <input type="number" x-model.number="uploadForm.provider_name_y" placeholder="e.g., 100"
                                                            class="w-full px-2 py-1 border border-blue-300 rounded text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-blue-700 mb-1">Width</label>
                                                        <input type="number" x-model.number="uploadForm.provider_name_width" placeholder="e.g., 150"
                                                            class="w-full px-2 py-1 border border-blue-300 rounded text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-blue-700 mb-1">Height</label>
                                                        <input type="number" x-model.number="uploadForm.provider_name_height" placeholder="e.g., 20"
                                                            class="w-full px-2 py-1 border border-blue-300 rounded text-sm">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-blue-700 mb-1">Font Size</label>
                                                    <input type="number" x-model.number="uploadForm.provider_name_font_size" placeholder="e.g., 12"
                                                        class="w-full px-2 py-1 border border-blue-300 rounded text-sm">
                                                </div>
                                                <p class="text-xs text-blue-600 italic">
                                                    📌 Tip: These coordinates mark where the provider name appears in your PDF. You can adjust them later if needed.
                                                </p>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Progress Bar -->
                                    <template x-if="uploading">
                                        <div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                <div class="bg-gold h-2 rounded-full transition-all duration-300"
                                                    :style="'width: ' + uploadProgress + '%'"></div>
                                            </div>
                                            <p class="text-xs text-center text-v2-text-light" x-text="uploadProgress + '%'"></p>
                                        </div>
                                    </template>

                                    <!-- Upload Button -->
                                    <button type="button" @click="uploadDocument()" :disabled="uploading"
                                        class="w-full px-4 py-2 bg-gold text-white rounded-lg text-sm hover:bg-gold-hover disabled:opacity-50">
                                        <span x-show="!uploading">Upload Document</span>
                                        <span x-show="uploading">Uploading...</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Documents List -->
                    <template x-if="loading">
                        <div class="flex justify-center py-8">
                            <div class="spinner"></div>
                        </div>
                    </template>

                    <template x-if="!loading && documents.length === 0">
                        <p class="text-sm text-v2-text-light text-center py-8">No documents uploaded yet</p>
                    </template>

                    <template x-if="!loading && documents.length > 0">
                        <div class="space-y-2">
                            <template x-for="doc in documents" :key="doc.id">
                                <div class="flex items-center justify-between p-3 border border-v2-card-border rounded-lg hover:bg-v2-bg transition-colors">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gold/10 rounded flex items-center justify-center">
                                                <span class="text-xs font-bold text-gold" x-text="getFileExtension(doc.original_file_name)"></span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-v2-text truncate" x-text="doc.original_file_name"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs px-2 py-0.5 rounded-full" :class="getDocumentTypeBadgeClass(doc.document_type)"
                                                    x-text="getDocumentTypeLabel(doc.document_type)"></span>
                                                <template x-if="doc.is_provider_template == 1">
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800" title="Provider name can be changed">
                                                        📋 Template
                                                    </span>
                                                </template>
                                                <span class="text-xs text-v2-text-light" x-text="doc.file_size_formatted"></span>
                                                <span class="text-xs text-v2-text-light" x-text="formatDate(doc.created_at)"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <!-- Generate for Provider button (only for templates) -->
                                        <template x-if="doc.is_provider_template == 1">
                                            <button @click="promptGenerateProviderVersion(doc)" title="Generate for Provider"
                                                class="p-2 text-v2-text-light hover:text-blue-600 hover:bg-blue-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </button>
                                        </template>
                                        <button @click="downloadDocument(doc.id)" title="Download"
                                            class="p-2 text-v2-text-light hover:text-gold hover:bg-gold/10 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button @click="deleteDocument(doc.id, doc.original_file_name)" title="Delete"
                                            class="p-2 text-v2-text-light hover:text-red-500 hover:bg-red-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Activity Log section -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
                <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                    <h3 class="font-semibold text-v2-text">Activity Log</h3>
                    <select x-model="noteFilterProvider" @change="loadNotes()"
                        class="border border-v2-card-border rounded-lg px-2 py-1.5 text-xs">
                        <option value="">All Providers</option>
                        <template x-for="prov in providers" :key="prov.id">
                            <option :value="prov.id" x-text="prov.provider_name"></option>
                        </template>
                    </select>
                </div>
                <div class="p-6">
                    <!-- Add note form -->
                    <form @submit.prevent="addNote()" class="mb-6 space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <select x-model="newNote.note_type"
                                class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                                <option value="general">General</option>
                                <option value="follow_up">Follow-Up</option>
                                <option value="issue">Issue</option>
                                <option value="handoff">Handoff</option>
                            </select>
                            <select x-model="newNote.case_provider_id"
                                class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                                <option value="">No Provider</option>
                                <template x-for="prov in providers" :key="prov.id">
                                    <option :value="prov.id" x-text="prov.provider_name"></option>
                                </template>
                            </select>
                            <select x-model="newNote.contact_method"
                                class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                                <option value="">No Contact</option>
                                <option value="phone">Phone</option>
                                <option value="fax">Fax</option>
                                <option value="email">Email</option>
                                <option value="portal">Portal</option>
                                <option value="mail">Mail</option>
                                <option value="in_person">In Person</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="datetime-local" x-model="newNote.contact_date"
                                class="border border-v2-card-border rounded-lg px-3 py-2 text-sm"
                                title="Contact date/time">
                        </div>
                        <div class="flex gap-3">
                            <input type="text" x-model="newNote.content" placeholder="Add a note..."
                                class="flex-1 px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                            <button type="submit" :disabled="!newNote.content.trim()"
                                class="px-4 py-2 bg-gold text-white rounded-lg text-sm hover:bg-gold-hover disabled:opacity-50">Add</button>
                        </div>
                    </form>

                    <!-- Notes list -->
                    <div class="space-y-0">
                        <template x-for="note in notes" :key="note.id">
                            <div class="timeline-item group">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="text-sm font-medium text-v2-text" x-text="note.author_name"></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-v2-bg text-v2-text-light"
                                        x-text="note.note_type"></span>
                                    <template x-if="note.provider_name">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-v2-bg text-gold font-medium"
                                            x-text="note.provider_name"></span>
                                    </template>
                                    <template x-if="note.contact_method">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700"
                                            x-text="getContactMethodLabel(note.contact_method)"></span>
                                    </template>
                                    <template x-if="note.contact_date">
                                        <span class="text-xs text-v2-text-light"
                                            x-text="formatDateTime(note.contact_date)"></span>
                                    </template>
                                    <template x-if="!note.contact_date">
                                        <span class="text-xs text-v2-text-light"
                                            x-text="timeAgo(note.created_at)"></span>
                                    </template>
                                    <button @click="deleteNote(note.id)"
                                        class="ml-auto text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                        title="Delete note">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-sm text-v2-text-mid" x-text="note.content"></p>
                            </div>
                        </template>
                        <template x-if="notes.length === 0">
                            <p class="text-sm text-v2-text-light text-center py-4">No notes yet</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Add Provider Modal -->
    <div x-show="showAddProviderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showAddProviderModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h3 class="text-lg font-semibold">Add Provider to Case</h3>
            </div>
            <form @submit.prevent="addProvider()" class="p-6 space-y-4">
                <!-- Provider search -->
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Provider *</label>
                    <div class="relative">
                        <input type="text" x-model="providerSearch" @input.debounce.300ms="searchProviders()"
                            placeholder="Search provider..."
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                        <div x-show="providerResults.length > 0"
                            class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                            <template x-for="pr in providerResults" :key="pr.id">
                                <button type="button" @click="selectProvider(pr)"
                                    class="w-full text-left px-4 py-2 text-sm hover:bg-v2-bg flex justify-between">
                                    <span x-text="pr.name"></span>
                                    <span class="text-xs text-v2-text-light"
                                        x-text="getProviderTypeLabel(pr.type)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p x-show="selectedProvider" class="text-sm text-gold mt-1" x-text="selectedProvider?.name"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Record Types Needed</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="rt in ['medical_records','billing','chart','imaging','op_report']" :key="rt">
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" :value="rt" x-model="newProvider.record_types"
                                    class="rounded border-v2-card-border text-gold">
                                <span x-text="rt.replace('_',' ')"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Deadline</label>
                    <input type="date" x-model="newProvider.deadline"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAddProviderModal = false"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="!selectedProvider || saving"
                        class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">Add
                        Provider</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Modal -->
    <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showRequestModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h3 class="text-lg font-semibold">Log Record Request</h3>
                <p class="text-sm text-v2-text-light" x-text="currentProvider?.provider_name"></p>
            </div>
            <form @submit.prevent="submitRequest()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Request Date *</label>
                        <input type="date" x-model="newRequest.request_date" required
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Method *</label>
                        <select x-model="newRequest.request_method" required @change="updateSentToByMethod()"
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="email">Email</option>
                            <option value="fax">Fax</option>
                            <option value="portal">Portal</option>
                            <option value="phone">Phone</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Next Follow-up Date</label>
                    <input type="date" x-model="newRequest.next_followup_date"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    <p class="text-xs text-v2-text-light mt-1">Defaults to 7 days from today</p>
                </div>
                <div x-effect="$dispatch('auto-select-template', { type: newRequest.request_type })">
                    <label class="block text-sm font-medium text-v2-text mb-1">Type</label>
                    <select x-model="newRequest.request_type"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                        <option value="initial">Initial Request</option>
                        <option value="follow_up">Follow-Up</option>
                        <option value="re_request">Re-Request</option>
                        <option value="rfd">RFD</option>
                    </select>
                </div>

                <!-- Template Selector -->
                <div x-data="templateSelector('medical_records')"
                    x-init="init(); $watch('selectedTemplateId', val => newRequest.template_id = val)"
                    @template-selected="newRequest.template_id = $event.detail.templateId"
                    @auto-select-template.window="
                        if ($event.detail.type === 'follow_up') {
                            const followUpTemplate = templates.find(t => t.name.toLowerCase().includes('follow-up'));
                            if (followUpTemplate) {
                                selectedTemplateId = followUpTemplate.id;
                                selectTemplate(followUpTemplate.id);
                            }
                        } else if ($event.detail.type === 'initial') {
                            const defaultTemplate = templates.find(t => t.is_default === 1);
                            if (defaultTemplate) {
                                selectedTemplateId = defaultTemplate.id;
                                selectTemplate(defaultTemplate.id);
                            }
                        }
                    ">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-v2-text">Letter Template</label>
                        <button type="button" @click="previewSelectedTemplate()"
                            :disabled="!selectedTemplateId"
                            class="text-xs text-gold hover:text-gold-hover disabled:opacity-50 disabled:cursor-not-allowed">
                            Preview
                        </button>
                    </div>
                    <select x-model="selectedTemplateId" @change="selectTemplate($event.target.value)"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                        <option value="">Select template...</option>
                        <template x-for="template in templates" :key="template.id">
                            <option :value="template.id" x-text="template.name + (template.is_default ? ' (Default)' : '')"></option>
                        </template>
                    </select>
                    <p class="text-xs text-v2-text-light mt-1" x-show="selectedTemplate" x-text="selectedTemplate?.description"></p>

                    <!-- Preview Modal -->
                    <div x-show="showPreview" class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none;">
                        <div class="modal-overlay fixed inset-0" @click="closePreview()"></div>
                        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl z-10 max-h-[90vh] overflow-hidden" @click.stop>
                            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                                <h3 class="text-lg font-semibold">Template Preview</h3>
                                <button type="button" @click="closePreview()" class="text-v2-text-light hover:text-v2-text">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-6 overflow-y-auto max-h-[calc(90vh-8rem)]">
                                <div class="prose max-w-none" x-html="previewHtml"></div>
                            </div>
                            <div class="px-6 py-4 border-t border-v2-card-border flex justify-end">
                                <button type="button" @click="closePreview()" class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Sent To</label>
                    <input type="text" x-model="newRequest.sent_to"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"
                        placeholder="Email or fax number">
                </div>

                <!-- Document Attachments (for email requests) -->
                <div x-show="newRequest.request_method === 'email'"
                    x-data="documentSelector(caseId, currentProvider?.id)"
                    x-init="init(); $watch('selectedDocumentIds', val => newRequest.document_ids = val)"
                    @documents-selected="newRequest.document_ids = $event.detail.documentIds"
                    @document-uploaded.window="loadDocuments()"
                    @document-generated.window="loadDocuments()">
                    <div class="border border-v2-card-border rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-v2-text">Attachments</label>
                            <div class="flex gap-2 text-xs">
                                <button type="button" @click="selectAll()" :disabled="documents.length === 0"
                                    class="text-gold hover:text-gold-hover disabled:opacity-50">
                                    Select All
                                </button>
                                <span class="text-v2-text-light">|</span>
                                <button type="button" @click="deselectAll()" :disabled="selectedDocumentIds.length === 0"
                                    class="text-gold hover:text-gold-hover disabled:opacity-50">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <template x-if="loading">
                            <div class="text-center py-2">
                                <div class="spinner-sm inline-block"></div>
                            </div>
                        </template>

                        <template x-if="!loading && documents.length === 0">
                            <p class="text-xs text-v2-text-light text-center py-2">
                                No documents available. Upload documents in the Documents section above.
                            </p>
                        </template>

                        <template x-if="!loading && documents.length > 0">
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                <template x-for="doc in documents" :key="doc.id">
                                    <label class="flex items-center gap-2 p-2 hover:bg-v2-bg rounded cursor-pointer">
                                        <input type="checkbox" :value="doc.id"
                                            @change="toggleDocument(doc.id)"
                                            :checked="isSelected(doc.id)"
                                            class="rounded border-v2-card-border text-gold">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-v2-text truncate" x-text="doc.original_file_name"></p>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="text-xs px-1.5 py-0.5 rounded" :class="getDocumentTypeBadgeClass(doc.document_type)"
                                                    x-text="getDocumentTypeLabel(doc.document_type)"></span>
                                                <span class="text-xs text-v2-text-light" x-text="doc.file_size_formatted"></span>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <p class="text-xs text-v2-text-light mt-2" x-show="selectedDocumentIds.length > 0">
                            <span x-text="selectedDocumentIds.length"></span> document(s) will be attached
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Notes</label>
                    <textarea x-model="newRequest.notes" rows="2"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showRequestModal = false"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving"
                        class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">Log
                        Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div x-show="showReceiptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showReceiptModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h3 class="text-lg font-semibold">Log Receipt</h3>
                <p class="text-sm text-v2-text-light" x-text="currentProvider?.provider_name"></p>
            </div>
            <form @submit.prevent="submitReceipt()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Received Date *</label>
                        <input type="date" x-model="newReceipt.received_date" required
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Method *</label>
                        <select x-model="newReceipt.received_method" required
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="fax">Fax</option>
                            <option value="email">Email</option>
                            <option value="portal">Portal</option>
                            <option value="mail">Mail</option>
                            <option value="in_person">In Person</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-2">Received Items</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                x-model="newReceipt.has_medical_records" class="rounded"> Medical Records</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                x-model="newReceipt.has_billing" class="rounded"> Billing</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                x-model="newReceipt.has_chart" class="rounded"> Chart Notes</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                x-model="newReceipt.has_imaging" class="rounded"> Imaging</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                x-model="newReceipt.has_op_report" class="rounded"> Operative Report</label>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" x-model="newReceipt.is_complete" class="rounded text-green-600"> All records
                    complete
                </label>
                <div x-show="!newReceipt.is_complete">
                    <label class="block text-sm font-medium text-v2-text mb-1">Incomplete Reason</label>
                    <textarea x-model="newReceipt.incomplete_reason" rows="2"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">File Location (Sharepoint path)</label>
                    <input type="text" x-model="newReceipt.file_location"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"
                        placeholder="\\sharepoint\cases\...">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showReceiptModal = false"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving"
                        class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">Log
                        Receipt</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview & Send Modal -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showPreviewModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] z-10 flex flex-col"
            @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Preview Request Letter</h3>
                    <p class="text-sm text-v2-text-light">
                        Sending via <span class="font-medium"
                            x-text="previewData.method === 'email' ? 'Email' : 'Fax'"></span>
                        to <span class="font-medium" x-text="previewData.provider_name"></span>
                    </p>
                </div>
                <button @click="showPreviewModal = false" class="text-v2-text-light hover:text-v2-text-mid">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-3 border-b border-v2-card-border bg-v2-bg">
                <label class="block text-sm font-medium text-v2-text mb-1"
                    x-text="previewData.method === 'email' ? 'Recipient Email' : 'Recipient Fax Number'"></label>
                <input type="text" x-model="previewData.recipient"
                    class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"
                    :placeholder="previewData.method === 'email' ? 'provider@example.com' : '(212) 555-1234'">
            </div>
            <div x-show="previewData.subject && previewData.method === 'email'" class="px-6 py-3 border-b border-v2-card-border bg-v2-bg">
                <label class="block text-sm font-medium text-v2-text mb-1">Subject</label>
                <div class="px-3 py-2 bg-white border border-v2-card-border rounded-lg text-sm" x-text="previewData.subject"></div>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div class="border rounded-lg bg-white shadow-inner">
                    <iframe :srcdoc="previewData.letter_html" class="w-full border-0" style="min-height: 600px;"
                        sandbox=""></iframe>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-v2-card-border flex items-center justify-between">
                <div class="text-sm text-v2-text-light">
                    <template x-if="previewData.send_status === 'failed'">
                        <span class="text-red-600">Previous attempt failed. You can retry.</span>
                    </template>
                </div>
                <div class="flex gap-3">
                    <button @click="showPreviewModal = false"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button @click="confirmAndSend()" :disabled="sending || !previewData.recipient"
                        class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center gap-2">
                        <template x-if="sending">
                            <div class="spinner" style="width:16px;height:16px;border-width:2px;"></div>
                        </template>
                        <span
                            x-text="sending ? 'Sending...' : (previewData.method === 'email' ? 'Send Email' : 'Send Fax')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Deadline Change Modal -->
    <div x-show="showDeadlineModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showDeadlineModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h3 class="text-lg font-semibold">Change Deadline</h3>
                <p class="text-sm text-v2-text-light" x-text="deadlineProvider?.provider_name"></p>
            </div>
            <form @submit.prevent="submitDeadlineChange()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Current Deadline</label>
                    <p class="text-sm text-v2-text-mid" x-text="formatDate(deadlineProvider?.deadline) || 'Not set'">
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">New Deadline *</label>
                    <input type="date" x-model="deadlineForm.deadline" required
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Reason for Change * <span
                            class="text-v2-text-light font-normal">(min 5 chars)</span></label>
                    <textarea x-model="deadlineForm.reason" rows="3" required minlength="5"
                        placeholder="Why is the deadline being changed?"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none"></textarea>
                </div>

                <!-- Deadline History -->
                <template x-if="deadlineHistory.length > 0">
                    <div>
                        <p class="text-xs font-medium text-v2-text-light mb-2">Change History</p>
                        <div class="max-h-32 overflow-y-auto space-y-1.5">
                            <template x-for="dh in deadlineHistory" :key="dh.id">
                                <div class="text-xs bg-v2-bg rounded px-3 py-2">
                                    <div class="flex justify-between text-v2-text-light">
                                        <span x-text="dh.changed_by_name"></span>
                                        <span x-text="timeAgo(dh.created_at)"></span>
                                    </div>
                                    <p class="text-v2-text mt-0.5">
                                        <span x-text="formatDate(dh.old_deadline)"></span> &rarr; <span
                                            x-text="formatDate(dh.new_deadline)"></span>
                                    </p>
                                    <p class="text-v2-text-light mt-0.5" x-text="dh.reason"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showDeadlineModal = false"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving || !deadlineForm.deadline || deadlineForm.reason.length < 5"
                        class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">Update
                        Deadline</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Case Modal -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border">
                <h3 class="text-lg font-semibold">Edit Case</h3>
            </div>
            <form @submit.prevent="updateCase()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Case Number</label>
                        <input type="text" x-model="editData.case_number" required
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Client Name</label>
                        <input type="text" x-model="editData.client_name" required
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">DOB</label>
                        <input type="date" x-model="editData.client_dob"
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">DOI</label>
                        <input type="date" x-model="editData.doi"
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Attorney</label>
                        <input type="text" x-model="editData.attorney_name"
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Assigned To</label>
                        <select x-model="editData.assigned_to"
                            class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="">Unassigned</option>
                            <option value="1">Ella</option>
                            <option value="2">Miki</option>
                        </select>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="editData.ini_completed" class="rounded"> INI Completed
                </label>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Notes</label>
                    <textarea x-model="editData.notes" rows="2"
                        class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving"
                        class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Expanded provider highlight */
    .provider-expanded-row {
        border-left: 4px solid #C8A951;
        background-color: #FDFBF5;
    }

    .provider-expanded-row>td {
        border-left: none;
    }

    .history-panel {
        background: linear-gradient(135deg, #FBF9F1 0%, #F7F4EA 100%);
        border-left: 4px solid #C8A951;
        border-top: 1px solid #E8E0C8;
    }

    /* Scroll highlight flash */
    @keyframes historyFlash {
        0% {
            box-shadow: inset 0 0 0 2px #C8A951;
        }

        50% {
            box-shadow: inset 0 0 0 2px #C8A951, 0 0 12px rgba(200, 169, 81, 0.3);
        }

        100% {
            box-shadow: none;
        }
    }

    .history-flash {
        animation: historyFlash 1.5s ease-out;
    }
</style>

<script src="/MRMS/frontend/components/template-selector.js"></script>
<script src="/MRMS/frontend/components/document-uploader.js"></script>
<script src="/MRMS/frontend/components/document-selector.js"></script>

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
            showPreviewModal: false,
            showDeadlineModal: false,

            editData: {},
            currentProvider: null,
            provSortBy: '',
            provSortDir: 'asc',
            expandedProvider: null,
            requestHistory: [],
            previewData: { method: '', recipient: '', provider_name: '', client_name: '', send_status: '', subject: '', letter_html: '', request_id: null },
            sending: false,
            deadlineProvider: null,
            deadlineForm: { deadline: '', reason: '' },
            deadlineHistory: [],

            providerSearch: '',
            providerResults: [],
            selectedProvider: null,
            newProvider: { record_types: [], deadline: '' },
            newRequest: { request_date: new Date().toISOString().split('T')[0], request_method: 'email', request_type: 'initial', sent_to: '', authorization_sent: true, notes: '', template_id: null, document_ids: [] },
            newReceipt: { received_date: new Date().toISOString().split('T')[0], received_method: 'fax', has_medical_records: false, has_billing: false, has_chart: false, has_imaging: false, has_op_report: false, is_complete: false, incomplete_reason: '', file_location: '' },
            newNote: { note_type: 'general', content: '', case_provider_id: '', contact_method: '', contact_date: '' },
            noteFilterProvider: '',

            async init() {
                if (!this.caseId) {
                    window.location.href = '/MRMS/frontend/pages/cases/index.php';
                    return;
                }
                // Set default deadline (2 weeks from today)
                this.newProvider.deadline = this.getDefaultDeadline();
                await Promise.all([this.loadCase(), this.loadProviders(), this.loadNotes()]);

                // Auto-expand provider if cp param is present (from tracker)
                const cpId = getQueryParam('cp');
                if (cpId) {
                    this.expandedProvider = parseInt(cpId);
                    this.loadRequestHistory(parseInt(cpId));
                }

                this.loading = false;

                // Scroll to expanded provider and flash
                if (cpId) {
                    this.$nextTick(() => {
                        const el = document.getElementById('history-' + parseInt(cpId));
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            el.querySelector('td').classList.add('history-flash');
                        }
                    });
                }
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
                    let url = 'case-providers?case_id=' + this.caseId;
                    if (this.provSortBy) url += '&sort_by=' + this.provSortBy + '&sort_dir=' + this.provSortDir;
                    const res = await api.get(url);
                    this.providers = res.data || [];
                } catch (e) { }
            },

            sortProviders(column) {
                if (this.provSortBy === column) {
                    this.provSortDir = this.provSortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.provSortBy = column;
                    this.provSortDir = 'asc';
                }
                this.loadProviders();
            },

            async loadNotes() {
                try {
                    let url = 'notes?case_id=' + this.caseId;
                    if (this.noteFilterProvider) url += '&case_provider_id=' + this.noteFilterProvider;
                    const res = await api.get(url);
                    this.notes = res.data || [];
                } catch (e) { }
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
                } catch (e) { }
            },

            selectProvider(p) {
                this.selectedProvider = p;
                this.providerSearch = p.name;
                this.providerResults = [];
            },

            getDefaultDeadline() {
                const date = new Date();
                date.setDate(date.getDate() + 14); // 2 weeks from today
                return date.toISOString().split('T')[0];
            },

            async addProvider() {
                if (!this.selectedProvider) return;
                this.saving = true;
                try {
                    await api.post('case-providers', {
                        case_id: parseInt(this.caseId),
                        provider_id: this.selectedProvider.id,
                        record_types_needed: this.newProvider.record_types.join(',') || null,
                        deadline: this.newProvider.deadline || null
                    });
                    showToast('Provider added');
                    this.showAddProviderModal = false;
                    this.selectedProvider = null;
                    this.providerSearch = '';
                    this.newProvider = { record_types: [], deadline: this.getDefaultDeadline() };
                    await this.loadProviders();
                } catch (e) {
                    showToast(e.data?.message || 'Failed to add provider', 'error');
                }
                this.saving = false;
            },

            openRequestModal(p) {
                this.currentProvider = p;

                // Default next follow-up: +7 days
                const nextFollowup = new Date();
                nextFollowup.setDate(nextFollowup.getDate() + 7);
                const nextFollowupStr = nextFollowup.toISOString().split('T')[0];

                this.newRequest = {
                    request_date: new Date().toISOString().split('T')[0],
                    request_method: 'email',
                    request_type: p.overall_status === 'not_started' ? 'initial' : 'follow_up',
                    sent_to: '',
                    authorization_sent: true,
                    notes: '',
                    next_followup_date: nextFollowupStr,
                    template_id: null,
                    document_ids: []
                };
                this.updateSentToByMethod();
                this.showRequestModal = true;
            },

            updateSentToByMethod() {
                const p = this.currentProvider;
                if (!p) return;
                const method = this.newRequest.request_method;
                if (method === 'email') this.newRequest.sent_to = p.provider_email || '';
                else if (method === 'fax') this.newRequest.sent_to = p.provider_fax || '';
                else if (method === 'phone') this.newRequest.sent_to = p.provider_phone || '';
                else this.newRequest.sent_to = '';
            },

            async submitRequest() {
                this.saving = true;
                try {
                    const endpoint = this.newRequest.request_type === 'follow_up' ? 'requests/followup' : 'requests';
                    const response = await api.post(endpoint, {
                        case_provider_id: this.currentProvider.id,
                        ...this.newRequest,
                        authorization_sent: this.newRequest.authorization_sent ? 1 : 0
                    });

                    const requestId = response.data.id;

                    // Attach selected documents if any
                    if (this.newRequest.document_ids && this.newRequest.document_ids.length > 0) {
                        for (const documentId of this.newRequest.document_ids) {
                            // Skip invalid document IDs
                            if (!documentId || documentId === 0 || documentId === '0') {
                                continue;
                            }
                            try {
                                await api.post(`requests/${requestId}/attach`, {
                                    document_id: documentId
                                });
                            } catch (attachError) {
                                // Silently continue - attachment errors are not critical
                                console.error('Failed to attach document:', attachError);
                            }
                        }
                    }

                    showToast('Request logged');
                    this.showRequestModal = false;
                    const cpId = this.currentProvider.id;
                    await this.loadProviders();
                    if (this.expandedProvider === cpId) {
                        await this.loadRequestHistory(cpId);
                    }
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

            async markComplete(cp) {
                if (!confirm('Mark this provider as records received complete?')) return;
                try {
                    await api.put('case-providers/' + cp.id + '/status', { overall_status: 'received_complete' });
                    showToast('Provider marked as complete', 'success');
                    this.loadProviders();
                } catch (e) {
                    showToast('Failed to mark complete', 'error');
                }
            },

            async addNote() {
                if (!this.newNote.content.trim()) return;
                try {
                    const payload = {
                        case_id: parseInt(this.caseId),
                        note_type: this.newNote.note_type,
                        content: this.newNote.content
                    };
                    if (this.newNote.case_provider_id) payload.case_provider_id = parseInt(this.newNote.case_provider_id);
                    if (this.newNote.contact_method) payload.contact_method = this.newNote.contact_method;
                    if (this.newNote.contact_date) payload.contact_date = this.newNote.contact_date;
                    await api.post('notes', payload);
                    this.newNote = { note_type: 'general', content: '', case_provider_id: '', contact_method: '', contact_date: '' };
                    await this.loadNotes();
                } catch (e) {
                    showToast('Failed to add note', 'error');
                }
            },

            async deleteNote(noteId) {
                if (!await confirmAction('Delete this note?')) return;
                try {
                    await api.delete('notes/' + noteId);
                    await this.loadNotes();
                    showToast('Note deleted', 'success');
                } catch (e) {
                    showToast(e.data?.message || 'Failed to delete note', 'error');
                }
            },

            openDeadlineModal(p) {
                this.deadlineProvider = p;
                this.deadlineForm = { deadline: p.deadline || '', reason: '' };
                this.deadlineHistory = [];
                this.showDeadlineModal = true;
                this.loadDeadlineHistory(p.id);
            },

            async loadDeadlineHistory(cpId) {
                try {
                    const res = await api.get('case-providers/' + cpId + '/deadline-history');
                    this.deadlineHistory = res.data || [];
                } catch (e) { }
            },

            async submitDeadlineChange() {
                if (!this.deadlineForm.deadline || this.deadlineForm.reason.length < 5) return;
                this.saving = true;
                try {
                    await api.put('case-providers/' + this.deadlineProvider.id + '/deadline', this.deadlineForm);
                    showToast('Deadline updated');
                    this.showDeadlineModal = false;
                    await this.loadProviders();
                } catch (e) {
                    showToast(e.data?.message || 'Failed to update deadline', 'error');
                }
                this.saving = false;
            },

            toggleRequestHistory(cpId) {
                if (this.expandedProvider === cpId) {
                    this.expandedProvider = null;
                    return;
                }
                this.expandedProvider = cpId;
                this.loadRequestHistory(cpId);
            },

            async loadRequestHistory(cpId) {
                try {
                    const res = await api.get('requests?case_provider_id=' + cpId);
                    this.requestHistory = res.data || [];
                } catch (e) {
                    this.requestHistory = [];
                }
            },

            async deleteRequest(req) {
                if (!confirm(`Delete this ${req.send_status} ${req.request_type} request (${req.request_date})?`)) {
                    return;
                }

                try {
                    await api.delete('requests/' + req.id);
                    showToast('Request deleted successfully', 'success');
                    // Reload request history
                    await this.loadRequestHistory(req.case_provider_id);
                    // Reload case data to update counts
                    await this.loadCase();
                } catch (e) {
                    showToast(e.response?.data?.error || 'Failed to delete request', 'error');
                }
            },

            async openPreviewModal(req) {
                try {
                    const res = await api.get('requests/' + req.id + '/preview');
                    this.previewData = res.data;
                    this.showPreviewModal = true;
                } catch (e) {
                    showToast(e.data?.message || 'Failed to load preview', 'error');
                }
            },

            async confirmAndSend() {
                if (!this.previewData.recipient) {
                    showToast('Please enter a recipient', 'error');
                    return;
                }
                if (!await confirmAction(
                    'Send this request via ' + (this.previewData.method === 'email' ? 'email' : 'fax') + ' to ' + this.previewData.recipient + '?'
                )) return;

                this.sending = true;
                try {
                    const res = await api.post('requests/' + this.previewData.request_id + '/send', {
                        recipient: this.previewData.recipient
                    });
                    showToast(res.message || 'Sent successfully!');
                    this.showPreviewModal = false;
                    if (this.expandedProvider) {
                        await this.loadRequestHistory(this.expandedProvider);
                    }
                    await this.loadProviders();
                } catch (e) {
                    showToast(e.data?.message || 'Send failed', 'error');
                }
                this.sending = false;
            },

            getSendStatusLabel(status) {
                const labels = { draft: 'Draft', sending: 'Sending...', sent: 'Sent', failed: 'Failed' };
                return labels[status] || status;
            },

            getRequestMethodLabel(method) {
                return REQUEST_METHODS[method] || method;
            },

            getRequestTypeLabel(type) {
                return REQUEST_TYPES[type] || type;
            },

            getContactMethodLabel(method) {
                const labels = { phone: 'Phone', fax: 'Fax', email: 'Email', portal: 'Portal', mail: 'Mail', in_person: 'In Person', other: 'Other' };
                return labels[method] || method;
            },

            formatDateTime(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
            }
        };
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>