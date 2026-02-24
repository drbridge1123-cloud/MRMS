            <!-- Provider List -->
            <div class="panel-section bg-white mb-4" data-panel :class="{'panel-open': showProviders}">
                <div class="px-5 py-3.5 flex items-center justify-between cursor-pointer" @click="showProviders = !showProviders; if(showProviders) $nextTick(() => $el.closest('[data-panel]').scrollIntoView({behavior:'smooth',block:'start'}))">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-3.5 h-3.5 text-v2-text-light transition-transform" :class="showProviders ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <h3 class="panel-title">Providers</h3>
                        <span class="panel-count" x-text="providers.length"></span>
                    </div>
                    <button @click.stop="showAddProviderModal = true" class="panel-btn">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Provider
                    </button>
                </div>
                <div x-show="showProviders" x-collapse class="overflow-x-auto">
                    <table class="data-table" style="min-width: 1100px;">
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
                                <th>Received</th>
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
                                <td colspan="10" class="text-center text-v2-text-light py-8">No providers added yet</td>
                            </tr>
                        </tbody>
                        <template x-for="p in providers" :key="p.id">
                            <tbody :class="{
                                'provider-selected': expandedProvider === p.id,
                                'provider-dimmed': expandedProvider !== null && expandedProvider !== p.id
                            }">
                                <tr @click="toggleRequestHistory(p.id)" class="cursor-pointer"
                                    :class="expandedProvider === p.id ? 'provider-expanded-row' : 'hover:bg-v2-bg/50'">
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
                                        <div class="ps-wrap">
                                            <!-- Label -->
                                            <span class="ps-label" :class="'ps-label-' + p.overall_status"
                                                x-text="getStatusLabel(p.overall_status)"></span>
                                            <!-- Progress bar -->
                                            <div class="ps-track">
                                                <div class="ps-fill" :class="'ps-fill-' + p.overall_status"
                                                    :style="'width:' + ({
                                                        no_records: '0',
                                                        not_started: '0',
                                                        requesting: '20',
                                                        follow_up: '35',
                                                        action_needed: '40',
                                                        on_hold: '60',
                                                        received_partial: '55',
                                                        received_complete: '100',
                                                        received: '100',
                                                        verified: '100',
                                                        done: '100'
                                                    }[p.overall_status] || '0') + '%'"></div>
                                            </div>
                                            <!-- No records sub-text -->
                                            <template x-if="p.overall_status === 'no_records' && p.no_records_reason">
                                                <div class="ps-sub" x-text="({
                                                    no_treatment: 'No treatment records',
                                                    patient_not_found: 'Patient not found',
                                                    records_destroyed: 'Records destroyed',
                                                    provider_closed: 'Provider closed',
                                                    other: p.no_records_detail || 'Other'
                                                })[p.no_records_reason] || p.no_records_reason"></div>
                                            </template>
                                            <!-- Partial record type tags -->
                                            <template x-if="p.overall_status === 'received_partial' && p.record_types_needed">
                                                <div class="ps-tags">
                                                    <template x-for="rt in p.record_types_needed.split(',')" :key="rt">
                                                        <span class="ps-tag"
                                                            :class="p.received_types && p.received_types[rt] ? 'ps-tag-done' : 'ps-tag-pending'"
                                                            :title="(p.received_types && p.received_types[rt] ? 'Received: ' : 'Pending: ') + rt.replace('_',' ')">
                                                            <template x-if="p.received_types && p.received_types[rt]">
                                                                <svg class="ps-tag-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                            </template>
                                                            <span x-text="getRecordTypeShort(rt)"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                        </template>
                                    </td>
                                    <td x-text="formatDate(p.first_request_date) || '-'"></td>
                                    <td x-text="formatDate(p.last_request_date) || '-'"></td>
                                    <td x-text="formatDate(p.received_date) || '-'"></td>
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
                                            <button @click.stop="openDeadlineModal(p)" title="Change Deadline" class="icon-btn icon-btn-sm" style="width:22px;height:22px;">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
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
                                            <button @click="openRequestModal(p)" title="New Request" class="icon-btn icon-btn-sm">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            </button>
                                            <button @click="openReceiptModal(p)" title="Log Receipt" class="icon-btn icon-btn-sm" style="color:#16a34a;">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            <button @click="deleteProvider(p.id)" title="Remove" class="icon-btn icon-btn-danger icon-btn-sm">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Request history row (directly below this provider) -->
                                <tr x-show="expandedProvider === p.id" x-transition :id="'history-' + p.id">
                                    <td colspan="10" class="history-panel px-4 py-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="history-label"
                                                x-text="'REQUEST HISTORY (' + (expandedProvider === p.id ? requestHistory.length : 0) + ')'">
                                            </h4>
                                            <button @click.stop="openRequestModal(p)" class="panel-btn">
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
                                                            <template x-if="req.department">
                                                                <span class="px-1.5 py-0.5 rounded text-xs font-medium"
                                                                    :class="req.department.toLowerCase().includes('billing') ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700'"
                                                                    x-text="req.department"></span>
                                                            </template>
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
                                                                    <button @click.stop="openPreviewModal(req)" title="Preview & Send" class="icon-btn icon-btn-sm">
                                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                                    </button>
                                                                </template>
                                                                <template x-if="['draft', 'failed'].includes(req.send_status)">
                                                                    <button @click.stop="deleteRequest(req)" title="Delete draft request" class="icon-btn icon-btn-danger icon-btn-sm">
                                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

