    <!-- Add Provider Modal -->
    <div x-show="showAddProviderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;">
        <div class="modal-v2-backdrop fixed inset-0" @click="showAddProviderModal = false"></div>
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <form @submit.prevent="addProvider()">
                <div class="modal-v2-header">
                    <div class="modal-v2-title">Add Provider to Case</div>
                    <button type="button" class="modal-v2-close" @click="showAddProviderModal = false">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body">
                    <div>
                        <label class="form-v2-label">Provider *</label>
                        <div class="relative">
                            <input type="text" x-model="providerSearch" @input.debounce.300ms="searchProviders()"
                                placeholder="Search provider..." class="form-v2-input">
                            <div x-show="providerResults.length > 0"
                                class="absolute z-10 w-full mt-1 bg-white border border-v2-card-border rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                <template x-for="pr in providerResults" :key="pr.id">
                                    <button type="button" @click="selectProvider(pr)"
                                        class="w-full text-left px-4 py-2 text-sm hover:bg-v2-bg flex justify-between">
                                        <span x-text="pr.name"></span>
                                        <span class="text-xs" style="color:var(--text-light)" x-text="getProviderTypeLabel(pr.type)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <p x-show="selectedProvider" class="text-sm mt-1" style="color:var(--gold)" x-text="selectedProvider?.name"></p>
                    </div>
                    <div>
                        <label class="form-v2-label">Record Types Needed</label>
                        <div class="flex flex-wrap gap-3">
                            <template x-for="rt in ['medical_records','billing','chart','imaging','op_report']" :key="rt">
                                <label class="flex items-center gap-1.5 text-sm">
                                    <input type="checkbox" :value="rt" x-model="newProvider.record_types"
                                        style="accent-color:var(--gold)">
                                    <span x-text="rt.replace('_',' ')"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="form-v2-label">Deadline</label>
                        <input type="date" x-model="newProvider.deadline" class="form-v2-input">
                    </div>
                </div>
                <div class="modal-v2-footer">
                    <button type="button" @click="showAddProviderModal = false" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="!selectedProvider || saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
