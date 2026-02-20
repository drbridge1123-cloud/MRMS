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
                                    <td @click.stop>
                                        <select :value="p.overall_status"
                                            @change="updateProviderStatus(p, $event.target.value)"
                                            class="text-xs font-semibold border-0 bg-transparent cursor-pointer rounded px-1 py-0.5 focus:ring-1 focus:ring-gold outline-none"
                                            :class="'status-' + p.overall_status">
                                            <option value="not_started">Not Started</option>
                                            <option value="requesting">Requesting</option>
                                            <option value="follow_up">Follow Up</option>
                                            <option value="action_needed">Action Needed</option>
                                            <option value="received_partial">Partial</option>
                                            <option value="on_hold">On Hold</option>
                                            <option value="received_complete">Complete</option>
                                            <option value="verified">Verified</option>
                                        </select>
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
                                    <td colspan="10" class="history-panel px-4 py-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-xs font-bold tracking-wider text-v2-text-mid uppercase"
                                                x-text="'REQUEST HISTORY (' + (expandedProvider === p.id ? requestHistory.length : 0) + ')'">
                                            </h4>
                                            <button @click.stop="openRequestModal(p)"
                                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-gold text-white hover:bg-gold-hover">
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
