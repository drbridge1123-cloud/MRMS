<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Provider Detail';
$currentPage = 'providers';
ob_start();
?>

<div x-data="providerDetailPage()" x-init="init()">
    <template x-if="loading">
        <div class="flex items-center justify-center py-20"><div class="spinner"></div></div>
    </template>
    <template x-if="!loading && provider">
        <div>
            <div class="flex items-center gap-4 mb-6">
                <a href="/MRMS/frontend/pages/providers/index.php" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800" x-text="provider.name"></h2>
                    <span class="text-sm text-gray-500" x-text="getProviderTypeLabel(provider.type)"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Contact Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm font-medium" x-text="provider.phone || '-'"></p></div>
                            <div><p class="text-xs text-gray-500">Fax</p><p class="text-sm font-medium" x-text="provider.fax || '-'"></p></div>
                            <div><p class="text-xs text-gray-500">Email</p><p class="text-sm font-medium" x-text="provider.email || '-'"></p></div>
                            <div><p class="text-xs text-gray-500">Portal</p><p class="text-sm font-medium"><a :href="provider.portal_url" x-text="provider.portal_url || '-'" class="text-blue-600"></a></p></div>
                            <div><p class="text-xs text-gray-500">Address</p><p class="text-sm" x-text="provider.address || '-'"></p></div>
                            <div><p class="text-xs text-gray-500">Preferred Method</p><p class="text-sm font-medium" x-text="getRequestMethodLabel(provider.preferred_method)"></p></div>
                        </div>
                    </div>

                    <!-- Contacts by department -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Department Contacts</h3>
                        <template x-if="provider.contacts && provider.contacts.length > 0">
                            <div class="space-y-3">
                                <template x-for="c in provider.contacts" :key="c.id">
                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                                        <div>
                                            <span class="text-sm font-medium" x-text="c.department || 'General'"></span>
                                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full ml-2" x-text="c.contact_type"></span>
                                            <template x-if="c.is_primary"><span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full ml-1">Primary</span></template>
                                        </div>
                                        <span class="text-sm text-blue-600 font-medium" x-text="c.contact_value"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!provider.contacts || provider.contacts.length === 0">
                            <p class="text-sm text-gray-400">No department contacts added</p>
                        </template>
                    </div>
                </div>

                <!-- Side panel -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Statistics</h3>
                        <div class="space-y-3">
                            <div><p class="text-xs text-gray-500">Difficulty</p><span class="status-badge" :class="'difficulty-' + provider.difficulty_level" x-text="provider.difficulty_level"></span></div>
                            <div><p class="text-xs text-gray-500">Avg Response Time</p><p class="text-sm font-medium" x-text="provider.avg_response_days ? provider.avg_response_days + ' days' : 'N/A'"></p></div>
                            <div><p class="text-xs text-gray-500">Used in Cases</p><p class="text-sm font-medium" x-text="provider.usage_count || 0"></p></div>
                        </div>
                    </div>

                    <template x-if="provider.uses_third_party">
                        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-6">
                            <h3 class="font-semibold text-yellow-800 mb-2">Third Party</h3>
                            <p class="text-sm" x-text="provider.third_party_name"></p>
                            <p class="text-sm text-gray-600" x-text="provider.third_party_contact"></p>
                        </div>
                    </template>

                    <template x-if="provider.notes">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h3 class="font-semibold text-gray-800 mb-2">Notes</h3>
                            <p class="text-sm text-gray-600" x-text="provider.notes"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function providerDetailPage() {
    return {
        providerId: getQueryParam('id'),
        provider: null,
        loading: true,

        async init() {
            if (!this.providerId) { window.location.href = '/MRMS/frontend/pages/providers/index.php'; return; }
            try {
                const res = await api.get('providers/' + this.providerId);
                this.provider = res.data;
            } catch (e) {
                showToast('Failed to load provider', 'error');
            }
            this.loading = false;
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
