<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Provider Database';
$currentPage = 'providers';
ob_start();
?>

<div x-data="providersListPage()" x-init="loadData()">

    <!-- Top bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="loadData(1)"
                       placeholder="Search providers..."
                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <select x-model="typeFilter" @change="loadData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Types</option>
                <option value="hospital">Hospital</option>
                <option value="er">Emergency Room</option>
                <option value="chiro">Chiropractor</option>
                <option value="imaging">Imaging</option>
                <option value="physician">Physician</option>
                <option value="surgery_center">Surgery Center</option>
                <option value="pharmacy">Pharmacy</option>
                <option value="other">Other</option>
            </select>

            <select x-model="difficultyFilter" @change="loadData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Difficulty</option>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
        </div>

        <button @click="showCreateModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Provider
        </button>
    </div>

    <!-- Providers table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('name')"><div class="flex items-center gap-1">Provider Name <template x-if="sortBy==='name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('type')"><div class="flex items-center gap-1">Type <template x-if="sortBy==='type'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Phone</th>
                        <th>Fax</th>
                        <th class="cursor-pointer select-none" @click="sort('preferred_method')"><div class="flex items-center gap-1">Preferred Method <template x-if="sortBy==='preferred_method'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('difficulty_level')"><div class="flex items-center gap-1">Difficulty <template x-if="sortBy==='difficulty_level'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('avg_response_days')"><div class="flex items-center gap-1">Avg Response <template x-if="sortBy==='avg_response_days'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr><td colspan="7" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr>
                    </template>
                    <template x-if="!loading && providers.length === 0">
                        <tr><td colspan="7" class="text-center text-gray-400 py-8">No providers found</td></tr>
                    </template>
                    <template x-for="p in providers" :key="p.id">
                        <tr class="cursor-pointer" @click="viewProvider(p.id)">
                            <td class="font-medium text-blue-600" x-text="p.name"></td>
                            <td><span class="text-xs text-gray-500" x-text="getProviderTypeLabel(p.type)"></span></td>
                            <td x-text="p.phone || '-'"></td>
                            <td x-text="p.fax || '-'"></td>
                            <td><span class="text-xs" x-text="getRequestMethodLabel(p.preferred_method)"></span></td>
                            <td>
                                <span class="status-badge" :class="'difficulty-' + p.difficulty_level" x-text="p.difficulty_level"></span>
                            </td>
                            <td x-text="p.avg_response_days ? p.avg_response_days + ' days' : '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <template x-if="pagination && pagination.total_pages > 1">
            <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100">
                <div class="text-sm text-gray-500">
                    Showing <span x-text="((pagination.page - 1) * pagination.per_page) + 1"></span>-<span x-text="Math.min(pagination.page * pagination.per_page, pagination.total)"></span> of <span x-text="pagination.total"></span>
                </div>
                <div class="flex gap-1">
                    <button @click="loadData(pagination.page - 1)" :disabled="pagination.page <= 1" class="px-3 py-1.5 text-sm border rounded-md disabled:opacity-50">Prev</button>
                    <button @click="loadData(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages" class="px-3 py-1.5 text-sm border rounded-md disabled:opacity-50">Next</button>
                </div>
            </div>
        </template>
    </div>

    <!-- Create Provider Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showCreateModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold">New Provider</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="createProvider()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provider Name *</label>
                        <input type="text" x-model="newProvider.name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select x-model="newProvider.type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="hospital">Hospital</option>
                            <option value="er">Emergency Room</option>
                            <option value="chiro">Chiropractor</option>
                            <option value="imaging">Imaging Center</option>
                            <option value="physician">Physician</option>
                            <option value="surgery_center">Surgery Center</option>
                            <option value="pharmacy">Pharmacy</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Method</label>
                        <select x-model="newProvider.preferred_method"
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" x-model="newProvider.address"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" x-model="newProvider.phone"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fax</label>
                        <input type="text" x-model="newProvider.fax"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" x-model="newProvider.email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Portal URL</label>
                        <input type="url" x-model="newProvider.portal_url"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                        <select x-model="newProvider.difficulty_level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>

                <!-- Third party -->
                <div>
                    <label class="flex items-center gap-2 text-sm mb-2">
                        <input type="checkbox" x-model="newProvider.uses_third_party" class="rounded"> Uses third party for records
                    </label>
                    <div x-show="newProvider.uses_third_party" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Third Party Name</label>
                            <input type="text" x-model="newProvider.third_party_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Third Party Contact</label>
                            <input type="text" x-model="newProvider.third_party_contact"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea x-model="newProvider.notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="saving ? 'Creating...' : 'Create Provider'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Provider Detail Modal -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showDetailModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10" @click.stop>
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-lg font-semibold" x-text="detailProvider?.name"></h3>
                    <span class="text-xs text-gray-500" x-text="getProviderTypeLabel(detailProvider?.type)"></span>
                </div>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="detailProvider">
                <div class="p-6 space-y-6">
                    <!-- Contact info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="text-sm font-medium" x-text="detailProvider.phone || '-'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Fax</p>
                            <p class="text-sm font-medium" x-text="detailProvider.fax || '-'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-medium" x-text="detailProvider.email || '-'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Preferred Method</p>
                            <p class="text-sm font-medium" x-text="getRequestMethodLabel(detailProvider.preferred_method)"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Difficulty</p>
                            <span class="status-badge" :class="'difficulty-' + detailProvider.difficulty_level" x-text="detailProvider.difficulty_level"></span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Avg Response</p>
                            <p class="text-sm font-medium" x-text="detailProvider.avg_response_days ? detailProvider.avg_response_days + ' days' : 'N/A'"></p>
                        </div>
                    </div>

                    <template x-if="detailProvider.address">
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="text-sm" x-text="detailProvider.address"></p>
                        </div>
                    </template>

                    <template x-if="detailProvider.portal_url">
                        <div>
                            <p class="text-xs text-gray-500">Portal URL</p>
                            <a :href="detailProvider.portal_url" target="_blank" class="text-sm text-blue-600 hover:underline" x-text="detailProvider.portal_url"></a>
                        </div>
                    </template>

                    <!-- Third party info -->
                    <template x-if="detailProvider.uses_third_party">
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <p class="text-xs text-yellow-800 font-medium mb-1">Uses Third Party</p>
                            <p class="text-sm" x-text="detailProvider.third_party_name"></p>
                            <p class="text-sm text-gray-600" x-text="detailProvider.third_party_contact"></p>
                        </div>
                    </template>

                    <!-- Department contacts -->
                    <template x-if="detailProvider.contacts && detailProvider.contacts.length > 0">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Department Contacts</h4>
                            <div class="space-y-2">
                                <template x-for="c in detailProvider.contacts" :key="c.id">
                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                                        <div>
                                            <span class="text-sm font-medium" x-text="c.department || 'General'"></span>
                                            <span class="text-xs text-gray-500 ml-2" x-text="c.contact_type"></span>
                                        </div>
                                        <span class="text-sm text-blue-600" x-text="c.contact_value"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="detailProvider.notes">
                        <div>
                            <p class="text-xs text-gray-500">Notes</p>
                            <p class="text-sm" x-text="detailProvider.notes"></p>
                        </div>
                    </template>

                    <div>
                        <p class="text-xs text-gray-500">Used in <span x-text="detailProvider.usage_count || 0"></span> case(s)</p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function providersListPage() {
    return {
        providers: [],
        pagination: null,
        loading: true,
        searchQuery: '',
        typeFilter: '',
        difficultyFilter: '',
        sortBy: '',
        sortDir: 'asc',
        showCreateModal: false,
        showDetailModal: false,
        saving: false,
        detailProvider: null,
        newProvider: {
            name: '', type: 'hospital', preferred_method: 'fax', address: '', phone: '', fax: '', email: '',
            portal_url: '', difficulty_level: 'medium', uses_third_party: false, third_party_name: '',
            third_party_contact: '', notes: ''
        },

        async loadData(page = 1) {
            this.loading = true;
            const params = buildQueryString({
                page,
                search: this.searchQuery,
                type: this.typeFilter,
                difficulty_level: this.difficultyFilter,
                sort_by: this.sortBy,
                sort_dir: this.sortDir
            });
            try {
                const res = await api.get('providers' + params);
                this.providers = res.data || [];
                this.pagination = res.pagination || null;
            } catch (e) {}
            this.loading = false;
        },

        sort(column) {
            if (this.sortBy === column) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDir = 'asc';
            }
            this.loadData(1);
        },

        async viewProvider(id) {
            try {
                const res = await api.get('providers/' + id);
                this.detailProvider = res.data;
                this.showDetailModal = true;
            } catch (e) {
                showToast('Failed to load provider', 'error');
            }
        },

        async createProvider() {
            this.saving = true;
            try {
                const data = { ...this.newProvider };
                data.uses_third_party = data.uses_third_party ? 1 : 0;
                await api.post('providers', data);
                showToast('Provider created successfully');
                this.showCreateModal = false;
                this.newProvider = {
                    name: '', type: 'hospital', preferred_method: 'fax', address: '', phone: '', fax: '', email: '',
                    portal_url: '', difficulty_level: 'medium', uses_third_party: false, third_party_name: '',
                    third_party_contact: '', notes: ''
                };
                this.loadData(1);
            } catch (e) {
                showToast(e.data?.message || 'Failed to create provider', 'error');
            }
            this.saving = false;
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
