            <!-- Top bar -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <a href="/MRMS/frontend/pages/cases/index.php" class="text-v2-text-light hover:text-v2-text-mid">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold text-v2-text" x-text="caseData.case_number"></h2>
                            <span class="status-badge text-xs px-2.5 py-1" :class="'status-' + caseData.status"
                                x-text="getStatusLabel(caseData.status)"></span>
                        </div>
                        <p class="text-v2-text-light text-sm" x-text="caseData.client_name"></p>
                    </div>
                </div>
                <div class="flex gap-2 items-center">
                    <button @click="showEditModal = true"
                        class="px-3 py-1.5 text-sm text-v2-text-mid hover:text-v2-text border border-v2-card-border rounded-lg hover:bg-v2-bg">
                        Edit
                    </button>
                    <div class="w-px h-6 bg-v2-card-border mx-1"
                         x-show="caseData && ((FORWARD_TRANSITIONS[caseData.status] && FORWARD_TRANSITIONS[caseData.status].length > 0) || (BACKWARD_TRANSITIONS[caseData.status] && BACKWARD_TRANSITIONS[caseData.status].length > 0))"></div>
                    <select x-model="nextStatus" @change="changeStatus()"
                        class="border border-v2-card-border rounded-lg px-3 py-1.5 text-sm"
                        x-show="caseData && FORWARD_TRANSITIONS[caseData.status] && FORWARD_TRANSITIONS[caseData.status].length > 0">
                        <option value="">Move to...</option>
                        <template x-for="s in (caseData && FORWARD_TRANSITIONS[caseData.status] || [])" :key="s">
                            <option :value="s" x-text="getStatusLabel(s)"></option>
                        </template>
                    </select>
                    <button x-show="caseData && BACKWARD_TRANSITIONS[caseData.status] && BACKWARD_TRANSITIONS[caseData.status].length > 0"
                        @click="openSendBackModal()"
                        class="px-3 py-1.5 text-sm text-orange-600 border border-orange-200 rounded-lg hover:bg-orange-50">
                        Send Back
                    </button>
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
