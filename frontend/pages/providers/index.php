<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Database';
$currentPage = 'providers';
$pageScripts = [
    '/MRMS/frontend/assets/js/pages/providers.js',
    '/MRMS/frontend/assets/js/pages/insurance-companies.js',
    '/MRMS/frontend/assets/js/pages/adjusters.js'
];
ob_start();
?>

<style>
.data-table tbody tr.prov-row:hover { background: rgba(201,168,76,.05); }
.prov-row { border-left: 3px solid transparent; cursor: pointer; transition: all .1s; }
.prov-row:hover, .prov-row-active { background: rgba(201,168,76,.05) !important; border-left-color: #C9A84C; }
.db-row { border-left: 3px solid transparent; cursor: pointer; transition: all .1s; }
.db-row:hover, .db-row-active { background: rgba(201,168,76,.05) !important; border-left-color: #C9A84C; }
</style>

<div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'providers' }">

    <!-- Tab Header -->
    <div class="flex items-center gap-6 mb-4">
        <div class="flex gap-0 border-b border-v2-card-border">
            <button @click="activeTab = 'providers'"
                    class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'providers' ? 'text-gold border-gold' : 'text-v2-text-light border-transparent hover:text-v2-text-mid'">
                Providers
            </button>
            <button @click="activeTab = 'insurance'"
                    class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'insurance' ? 'text-gold border-gold' : 'text-v2-text-light border-transparent hover:text-v2-text-mid'">
                Insurance
            </button>
            <button @click="activeTab = 'adjusters'"
                    class="px-4 py-2 text-sm font-semibold transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'adjusters' ? 'text-gold border-gold' : 'text-v2-text-light border-transparent hover:text-v2-text-mid'">
                Adjusters
            </button>
        </div>
    </div>

    <!-- ===================== PROVIDERS TAB ===================== -->
    <div x-show="activeTab === 'providers'" x-data="providersListPage()" x-init="loadData()">

        <!-- Top bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.300ms="loadData()"
                           placeholder="Search by name, phone, fax, or email..."
                           class="w-80 pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>

                <select x-model="typeFilter" @change="loadData()"
                        class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    <option value="acupuncture">Acupuncture</option>
                    <option value="chiro">Chiropractor</option>
                    <option value="massage">Massage</option>
                    <option value="pain_management">Pain Management</option>
                    <option value="pt">Physical Therapy</option>
                    <option value="er">Emergency Room</option>
                    <option value="hospital">Hospital</option>
                    <option value="physician">Physician</option>
                    <option value="imaging">Imaging</option>
                    <option value="pharmacy">Pharmacy</option>
                    <option value="surgery_center">Surgery Center</option>
                    <option value="other">Other</option>
                </select>

                <select x-model="difficultyFilter" @change="loadData()"
                        class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Difficulty</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button @click="exportCSV()"
                        class="border border-v2-card-border text-v2-text px-4 py-2 rounded-lg text-sm font-medium hover:bg-v2-bg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </button>
                <button @click="showCreateModal = true"
                        style="background:#0F1B2D; color:#fff;" class="px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Provider
                </button>
            </div>
        </div>

        <!-- Stats Bar -->
        <div style="background:#fff; border-bottom:1px solid var(--border, #e8e4dc); padding:12px 24px; display:flex; margin-bottom:0; border-radius:12px 12px 0 0; border:1px solid var(--v2-card-border, #e2e0dc); border-bottom:1px solid var(--v2-card-border, #e2e0dc);">
            <div style="display:flex; align-items:center; padding-right:24px; border-right:1px solid var(--border, #e8e4dc);">
                <div>
                    <div style="font-size:9px; font-weight:700; color:var(--muted, #8a8a82); text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px;">Total Providers</div>
                    <div style="font-size:20px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#1a2535;" x-text="items.length"></div>
                </div>
            </div>
            <div style="display:flex; align-items:center; padding:0 24px; border-right:1px solid var(--border, #e8e4dc);">
                <div>
                    <div style="font-size:9px; font-weight:700; color:var(--muted, #8a8a82); text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px;">Chiropractors</div>
                    <div style="font-size:20px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#2E7D6B;" x-text="items.filter(p => p.type === 'chiro').length"></div>
                </div>
            </div>
            <div style="display:flex; align-items:center; padding:0 24px; border-right:1px solid var(--border, #e8e4dc);">
                <div>
                    <div style="font-size:9px; font-weight:700; color:var(--muted, #8a8a82); text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px;">Physicians</div>
                    <div style="font-size:20px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#3B6FD4;" x-text="items.filter(p => p.type === 'physician').length"></div>
                </div>
            </div>
            <div style="display:flex; align-items:center; padding:0 24px;">
                <div>
                    <div style="font-size:9px; font-weight:700; color:var(--muted, #8a8a82); text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px;">Avg Response</div>
                    <div style="font-size:20px; font-weight:700; font-family:'IBM Plex Mono',monospace; color:#B8973F;" x-text="(() => { const withDays = items.filter(p => p.avg_response_days > 0); return withDays.length ? Math.round(withDays.reduce((s,p) => s + Number(p.avg_response_days), 0) / withDays.length) + 'd' : '—'; })()"></div>
                </div>
            </div>
        </div>

        <!-- Providers Table -->
        <div>
            <div class="bg-white shadow-sm border border-v2-card-border" style="border-radius:0 0 12px 12px; border-top:none;"
                 x-init="initScrollContainer($el)">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="cursor-pointer select-none" @click="sort('name')"><div class="flex items-center gap-1">Provider Name <template x-if="sortBy==='name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                            <th class="cursor-pointer select-none" @click="sort('type')"><div class="flex items-center gap-1">Type <template x-if="sortBy==='type'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                            <th>Phone</th>
                            <th>Fax</th>
                            <th>Email</th>
                            <th class="cursor-pointer select-none" @click="sort('preferred_method')"><div class="flex items-center gap-1">Preferred Method <template x-if="sortBy==='preferred_method'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                            <th class="cursor-pointer select-none" @click="sort('difficulty_level')"><div class="flex items-center gap-1">Difficulty <template x-if="sortBy==='difficulty_level'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                            <th class="cursor-pointer select-none" @click="sort('avg_response_days')"><div class="flex items-center gap-1">Avg Response <template x-if="sortBy==='avg_response_days'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="loading">
                            <tr><td colspan="8" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr>
                        </template>
                        <template x-if="!loading && items.length === 0">
                            <tr><td colspan="8" class="text-center text-v2-text-light py-8">No providers found</td></tr>
                        </template>
                        <template x-for="p in items" :key="p.id">
                            <tr @click="viewProvider(p.id)"
                                class="prov-row"
                                :class="[selectedProvider?.id === p.id ? 'prov-row-active' : '', p.is_suspicious == 1 ? 'bg-blue-50' : '']">
                                <td class="font-medium" :style="p.is_suspicious == 1 ? 'color:#2563EB' : 'color:#7d693c'" x-text="p.name"></td>
                                <td><span class="text-xs text-v2-text-light" x-text="getProviderTypeLabel(p.type)"></span></td>
                                <td class="whitespace-nowrap" x-text="formatPhoneNumber(p.phone)"></td>
                                <td class="whitespace-nowrap" x-text="formatPhoneNumber(p.fax)"></td>
                                <td class="text-xs whitespace-nowrap" x-text="p.email || '-'"></td>
                                <td><span class="text-xs" x-text="getRequestMethodLabel(p.preferred_method)"></span></td>
                                <td>
                                    <span class="status-badge" :class="'difficulty-' + p.difficulty_level" x-text="p.difficulty_level"></span>
                                </td>
                                <td x-text="p.avg_response_days ? p.avg_response_days + ' days' : '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-v2-card-border">
                    <div class="text-sm text-v2-text-light">
                        Showing <span x-text="items.length"></span> provider<span x-text="items.length === 1 ? '' : 's'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Provider Detail Modal -->
        <div x-show="selectedProvider" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.escape.window="selectedProvider = null" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="modal-v2-backdrop fixed inset-0" @click="selectedProvider = null"></div>
            <div x-show="selectedProvider" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.stop class="modal-v2 relative w-full max-w-2xl z-10" style="max-height: 90vh;">
                <template x-if="selectedProvider">
                    <div>
                        <div class="modal-v2-header">
                            <div class="flex-1 pr-4">
                                <h2 class="modal-v2-title" x-text="selectedProvider.name"></h2>
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    <span class="modal-v2-subtitle text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider" style="background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.7);" x-text="getProviderTypeLabel(selectedProvider.type)"></span>
                                    <template x-if="selectedProvider.difficulty_level">
                                        <span class="text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider" :style="getDifficultyStyle(selectedProvider.difficulty_level)" x-text="selectedProvider.difficulty_level"></span>
                                    </template>
                                    <template x-if="selectedProvider.uses_third_party == 1">
                                        <span class="text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider" style="background: rgba(201,168,76,0.15); color: #C9A84C;">ChartSwap</span>
                                    </template>
                                </div>
                            </div>
                            <button type="button" class="modal-v2-close" @click="selectedProvider = null">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="modal-v2-body space-y-5 overflow-y-auto" style="max-height: 60vh;">
                            <div>
                                <h3 class="detail-section-title">Contact Information</h3>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div class="detail-card-v2"><p class="detail-label-v2">Phone</p><p class="detail-value-v2" :class="!selectedProvider.phone && 'empty'" x-text="formatPhoneNumber(selectedProvider.phone)"></p></div>
                                    <div class="detail-card-v2"><p class="detail-label-v2">Fax</p><p class="detail-value-v2" :class="!selectedProvider.fax && 'empty'" x-text="formatPhoneNumber(selectedProvider.fax)"></p></div>
                                    <div class="detail-card-v2 col-span-2"><p class="detail-label-v2">Email</p><p class="detail-value-v2 break-all" :class="!selectedProvider.email && 'empty'" x-text="selectedProvider.email || '—'"></p></div>
                                    <template x-if="selectedProvider.address || selectedProvider.city">
                                        <div class="detail-card-v2 col-span-2"><p class="detail-label-v2">Address</p><div class="detail-value-v2"><p x-show="selectedProvider.address" x-text="selectedProvider.address"></p><p x-show="selectedProvider.city || selectedProvider.state || selectedProvider.zip"><span x-text="selectedProvider.city || ''"></span><span x-show="selectedProvider.city && selectedProvider.state">, </span><span x-text="selectedProvider.state || ''"></span><span x-show="selectedProvider.zip"> </span><span x-text="selectedProvider.zip || ''"></span></p></div></div>
                                    </template>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="detail-card-v2"><p class="detail-label-v2">Avg Response</p><p class="detail-value-v2 large" :style="{ color: getAvgColor(selectedProvider.avg_response_days) }" x-text="selectedProvider.avg_response_days ? selectedProvider.avg_response_days + 'd' : '—'"></p></div>
                                <div class="detail-card-v2"><p class="detail-label-v2">Preferred Method</p><p class="detail-value-v2 large" x-text="getRequestMethodLabel(selectedProvider.preferred_method)"></p></div>
                            </div>
                            <template x-if="selectedProvider.contacts && selectedProvider.contacts.length > 0">
                                <div>
                                    <h3 class="detail-section-title">Department Contacts</h3>
                                    <div class="space-y-1.5">
                                        <template x-for="(contact, idx) in selectedProvider.contacts" :key="idx">
                                            <div class="detail-contact-row">
                                                <span class="font-bold flex-shrink-0" style="min-width: 100px;" x-text="contact.department"></span>
                                                <span class="flex-shrink-0 text-xs font-bold px-1.5 py-0.5 rounded uppercase" :style="getContactTypeStyle(contact.contact_type)" x-text="contact.contact_type"></span>
                                                <span class="truncate min-w-0" style="color: #3D4F63;" x-text="contact.contact_value"></span>
                                                <template x-if="contact.is_primary == 1">
                                                    <span class="flex-shrink-0 ml-auto text-xs font-bold" style="color: #C9A84C;">PRIMARY</span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="modal-v2-footer">
                            <button @click="editProvider = { ...selectedProvider, contacts: selectedProvider.contacts || [] }; showProviderModal = true; selectedProvider = null" class="btn-v2-primary flex-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Provider
                            </button>
                            <button @click="deleteProvider(selectedProvider.id, selectedProvider.name)" class="btn-v2-danger">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create Provider Modal -->
        <template x-if="showCreateModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeCreateModal()">
                <div class="modal-v2-backdrop fixed inset-0" @click="closeCreateModal()"></div>
                <form @submit.prevent="createProvider()" class="modal-v2 relative w-full max-w-2xl z-10" style="max-height: 90vh; display: flex; flex-direction: column;" @click.stop>
                    <div class="modal-v2-header flex-shrink-0"><div><h3 class="modal-v2-title">New Provider</h3></div><button type="button" class="modal-v2-close" @click="closeCreateModal()"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button></div>
                    <div class="modal-v2-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                        <div class="grid grid-cols-4 gap-3">
                            <div class="col-span-2"><label class="form-v2-label">Provider Name *</label><input type="text" x-model="newProvider.name" required class="form-v2-input"></div>
                            <div><label class="form-v2-label">Type *</label><select x-model="newProvider.type" required class="form-v2-select"><option value="acupuncture">Acupuncture</option><option value="chiro">Chiropractor</option><option value="massage">Massage</option><option value="pain_management">Pain Mgmt</option><option value="pt">Physical Therapy</option><option value="er">Emergency Room</option><option value="hospital">Hospital</option><option value="physician">Physician</option><option value="imaging">Imaging Center</option><option value="pharmacy">Pharmacy</option><option value="surgery_center">Surgery Center</option><option value="other">Other</option></select></div>
                            <div><label class="form-v2-label">Difficulty</label><select x-model="newProvider.difficulty_level" class="form-v2-select"><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option></select></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div><label class="form-v2-label">Street Address</label><input type="text" x-model="newProvider.address" class="form-v2-input"></div>
                        <div class="grid grid-cols-6 gap-3">
                            <div class="col-span-3"><label class="form-v2-label">City</label><input type="text" x-model="newProvider.city" class="form-v2-input"></div>
                            <div><label class="form-v2-label">State</label><input type="text" x-model="newProvider.state" maxlength="2" placeholder="WA" class="form-v2-input uppercase"></div>
                            <div class="col-span-2"><label class="form-v2-label">ZIP</label><input type="text" x-model="newProvider.zip" maxlength="10" placeholder="98036" class="form-v2-input"></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-v2-label">Phone</label><input type="text" x-model="newProvider.phone" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Fax</label><input type="text" x-model="newProvider.fax" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Email</label><input type="email" x-model="newProvider.email" class="form-v2-input"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-v2-label">Preferred Method</label><select x-model="newProvider.preferred_method" class="form-v2-select"><option value="fax">Fax</option><option value="email">Email</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select></div>
                            <div><label class="form-v2-label">Portal URL</label><input type="url" x-model="newProvider.portal_url" class="form-v2-input"></div>
                            <div class="flex items-end pb-1"><label class="flex items-center gap-2 text-sm" style="accent-color: var(--gold);"><input type="checkbox" x-model="newProvider.uses_third_party" class="rounded"> Uses third party</label></div>
                        </div>
                        <div x-show="newProvider.uses_third_party" class="grid grid-cols-2 gap-3" x-collapse>
                            <div><label class="form-v2-label">Third Party Name</label><input type="text" x-model="newProvider.third_party_name" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Third Party Contact</label><input type="text" x-model="newProvider.third_party_contact" class="form-v2-input"></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div>
                            <div class="flex items-center justify-between mb-2"><label class="form-v2-label mb-0">Department Contacts</label><button type="button" @click="addContact(newProvider)" class="text-xs text-gold hover:text-gold-hover font-semibold">+ Add Contact</button></div>
                            <template x-for="(contact, idx) in newProvider.contacts" :key="idx">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" x-model="contact.department" placeholder="Department name" class="form-v2-input text-sm" style="flex:2;">
                                    <select x-model="contact.contact_type" class="form-v2-select text-sm" style="flex:1;"><option value="email">Email</option><option value="fax">Fax</option><option value="phone">Phone</option><option value="portal">Portal</option></select>
                                    <input type="text" x-model="contact.contact_value" :placeholder="contact.contact_type === 'email' ? 'Email address' : contact.contact_type === 'fax' ? 'Fax number' : contact.contact_type === 'phone' ? 'Phone number' : 'Portal URL'" class="form-v2-input text-sm" style="flex:2;">
                                    <button type="button" @click="setPrimary(newProvider, idx)" class="text-xs px-2 py-1 rounded font-bold flex-shrink-0" :class="contact.is_primary == 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'" x-text="contact.is_primary == 1 ? 'PRIMARY' : 'Set'"></button>
                                    <button type="button" @click="removeContact(newProvider, idx)" class="text-red-400 hover:text-red-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </template>
                            <template x-if="newProvider.contacts.length === 0"><p class="text-xs text-v2-text-light">No department contacts. Use the main phone/fax/email fields above, or add specific department contacts.</p></template>
                        </div>
                        <div><label class="form-v2-label">Notes</label><textarea x-model="newProvider.notes" rows="2" class="form-v2-textarea" placeholder="Optional notes..."></textarea></div>
                    </div>
                    <div class="modal-v2-footer flex-shrink-0">
                        <button type="button" @click="closeCreateModal()" class="btn-v2-cancel">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-v2-primary"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg><span x-text="saving ? 'Creating...' : 'Create Provider'"></span></button>
                    </div>
                </form>
            </div>
        </template>

        <!-- Edit Provider Modal -->
        <template x-if="showProviderModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeEditModal()">
                <div class="modal-v2-backdrop fixed inset-0" @click="closeEditModal()"></div>
                <form @submit.prevent="updateProvider()" class="modal-v2 relative w-full max-w-2xl z-10" style="max-height: 90vh; display: flex; flex-direction: column;" @click.stop>
                    <div class="modal-v2-header flex-shrink-0"><div><h3 class="modal-v2-title">Edit Provider</h3></div><button type="button" class="modal-v2-close" @click="closeEditModal()"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button></div>
                    <div class="modal-v2-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                        <div class="grid grid-cols-4 gap-3">
                            <div class="col-span-2"><label class="form-v2-label">Provider Name *</label><input type="text" x-model="editProvider.name" required class="form-v2-input"></div>
                            <div><label class="form-v2-label">Type *</label><select x-model="editProvider.type" required class="form-v2-select"><option value="acupuncture">Acupuncture</option><option value="chiro">Chiropractor</option><option value="massage">Massage</option><option value="pain_management">Pain Mgmt</option><option value="pt">Physical Therapy</option><option value="er">Emergency Room</option><option value="hospital">Hospital</option><option value="physician">Physician</option><option value="imaging">Imaging Center</option><option value="pharmacy">Pharmacy</option><option value="surgery_center">Surgery Center</option><option value="other">Other</option></select></div>
                            <div><label class="form-v2-label">Difficulty</label><select x-model="editProvider.difficulty_level" class="form-v2-select"><option value="easy">Easy</option><option value="medium">Medium</option><option value="hard">Hard</option></select></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div><label class="form-v2-label">Street Address</label><input type="text" x-model="editProvider.address" class="form-v2-input"></div>
                        <div class="grid grid-cols-6 gap-3">
                            <div class="col-span-3"><label class="form-v2-label">City</label><input type="text" x-model="editProvider.city" class="form-v2-input"></div>
                            <div><label class="form-v2-label">State</label><input type="text" x-model="editProvider.state" maxlength="2" placeholder="WA" class="form-v2-input uppercase"></div>
                            <div class="col-span-2"><label class="form-v2-label">ZIP</label><input type="text" x-model="editProvider.zip" maxlength="10" placeholder="98036" class="form-v2-input"></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-v2-label">Phone</label><input type="text" x-model="editProvider.phone" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Fax</label><input type="text" x-model="editProvider.fax" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Email</label><input type="email" x-model="editProvider.email" class="form-v2-input"></div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-v2-label">Preferred Method</label><select x-model="editProvider.preferred_method" class="form-v2-select"><option value="fax">Fax</option><option value="email">Email</option><option value="portal">Portal</option><option value="phone">Phone</option><option value="mail">Mail</option></select></div>
                            <div><label class="form-v2-label">Portal URL</label><input type="url" x-model="editProvider.portal_url" class="form-v2-input"></div>
                            <div class="flex items-end pb-1"><label class="flex items-center gap-2 text-sm" style="accent-color: var(--gold);"><input type="checkbox" x-model="editProvider.uses_third_party" class="rounded"> Uses third party</label></div>
                        </div>
                        <div x-show="editProvider.uses_third_party" class="grid grid-cols-2 gap-3" x-collapse>
                            <div><label class="form-v2-label">Third Party Name</label><input type="text" x-model="editProvider.third_party_name" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Third Party Contact</label><input type="text" x-model="editProvider.third_party_contact" class="form-v2-input"></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div>
                            <div class="flex items-center justify-between mb-2"><label class="form-v2-label mb-0">Department Contacts</label><button type="button" @click="addContact(editProvider)" class="text-xs text-gold hover:text-gold-hover font-semibold">+ Add Contact</button></div>
                            <template x-for="(contact, idx) in editProvider.contacts" :key="idx">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" x-model="contact.department" placeholder="Department name" class="form-v2-input text-sm" style="flex:2;">
                                    <select x-model="contact.contact_type" class="form-v2-select text-sm" style="flex:1;"><option value="email">Email</option><option value="fax">Fax</option><option value="phone">Phone</option><option value="portal">Portal</option></select>
                                    <input type="text" x-model="contact.contact_value" :placeholder="contact.contact_type === 'email' ? 'Email address' : contact.contact_type === 'fax' ? 'Fax number' : contact.contact_type === 'phone' ? 'Phone number' : 'Portal URL'" class="form-v2-input text-sm" style="flex:2;">
                                    <button type="button" @click="setPrimary(editProvider, idx)" class="text-xs px-2 py-1 rounded font-bold flex-shrink-0" :class="contact.is_primary == 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'" x-text="contact.is_primary == 1 ? 'PRIMARY' : 'Set'"></button>
                                    <button type="button" @click="removeContact(editProvider, idx)" class="text-red-400 hover:text-red-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </div>
                            </template>
                            <template x-if="editProvider.contacts.length === 0"><p class="text-xs text-v2-text-light">No department contacts added.</p></template>
                        </div>
                        <div><label class="form-v2-label">Notes</label><textarea x-model="editProvider.notes" rows="2" class="form-v2-textarea" placeholder="Optional notes..."></textarea></div>
                    </div>
                    <div class="modal-v2-footer flex-shrink-0">
                        <button type="button" @click="closeEditModal()" class="btn-v2-cancel">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-v2-primary"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span x-text="saving ? 'Saving...' : 'Update Provider'"></span></button>
                    </div>
                </form>
            </div>
        </template>

    </div><!-- /Providers Tab -->

    <!-- ===================== INSURANCE TAB ===================== -->
    <div x-show="activeTab === 'insurance'" x-cloak x-data="insuranceListPage()" x-init="loadData()">

        <!-- Top bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.300ms="loadData()"
                           placeholder="Search by name, phone, fax, or email..."
                           class="w-80 pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <select x-model="typeFilter" @change="loadData()" class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    <option value="auto">Auto</option>
                    <option value="health">Health</option>
                    <option value="workers_comp">Worker's Comp</option>
                    <option value="liability">Liability</option>
                    <option value="um_uim">UM/UIM</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button @click="showCreateModal = true" style="background:#0F1B2D; color:#fff;" class="px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Insurance Co.
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border" x-init="initScrollContainer($el)">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('name')"><div class="flex items-center gap-1">Company Name <template x-if="sortBy==='name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('type')"><div class="flex items-center gap-1">Type <template x-if="sortBy==='type'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Phone</th>
                        <th>Fax</th>
                        <th>Email</th>
                        <th>Adjusters</th>
                        <th class="cursor-pointer select-none" @click="sort('city')"><div class="flex items-center gap-1">City/State <template x-if="sortBy==='city'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="7" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr></template>
                    <template x-if="!loading && items.length === 0"><tr><td colspan="7" class="text-center text-v2-text-light py-8">No insurance companies found</td></tr></template>
                    <template x-for="c in items" :key="c.id">
                        <tr @click="viewCompany(c.id)" class="db-row" :class="selectedCompany?.id === c.id ? 'db-row-active' : ''">
                            <td class="font-medium" style="color:#7d693c" x-text="c.name"></td>
                            <td><span class="text-xs font-medium px-2 py-0.5 rounded" :style="getTypeColor(c.type)" x-text="getInsuranceTypeLabel(c.type)"></span></td>
                            <td class="whitespace-nowrap text-sm" x-text="c.phone || '-'"></td>
                            <td class="whitespace-nowrap text-sm" x-text="c.fax || '-'"></td>
                            <td class="text-xs whitespace-nowrap" x-text="c.email || '-'"></td>
                            <td class="text-center"><span class="text-xs font-medium" x-text="c.adjuster_count || '0'"></span></td>
                            <td class="text-sm whitespace-nowrap" x-text="[c.city, c.state].filter(Boolean).join(', ') || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="px-6 py-3 border-t border-v2-card-border">
                <div class="text-sm text-v2-text-light">Showing <span x-text="items.length"></span> compan<span x-text="items.length === 1 ? 'y' : 'ies'"></span></div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="selectedCompany" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="selectedCompany = null">
            <div class="modal-v2-backdrop fixed inset-0" @click="selectedCompany = null"></div>
            <div @click.stop class="modal-v2 relative w-full max-w-lg z-10">
                <template x-if="selectedCompany">
                    <div>
                        <div class="modal-v2-header">
                            <div class="flex-1 pr-4">
                                <h2 class="modal-v2-title" x-text="selectedCompany.name"></h2>
                                <span class="text-xs font-bold px-2 py-0.5 rounded uppercase mt-1 inline-block" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.7);" x-text="getInsuranceTypeLabel(selectedCompany.type)"></span>
                            </div>
                            <button type="button" class="modal-v2-close" @click="selectedCompany = null"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="modal-v2-body space-y-4" style="max-height:60vh; overflow-y:auto;">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="detail-card-v2"><p class="detail-label-v2">Phone</p><p class="detail-value-v2" x-text="selectedCompany.phone || '—'"></p></div>
                                <div class="detail-card-v2"><p class="detail-label-v2">Fax</p><p class="detail-value-v2" x-text="selectedCompany.fax || '—'"></p></div>
                                <div class="detail-card-v2 col-span-2"><p class="detail-label-v2">Email</p><p class="detail-value-v2 break-all" x-text="selectedCompany.email || '—'"></p></div>
                                <template x-if="selectedCompany.website"><div class="detail-card-v2 col-span-2"><p class="detail-label-v2">Website</p><p class="detail-value-v2 break-all" x-text="selectedCompany.website"></p></div></template>
                                <template x-if="selectedCompany.address || selectedCompany.city">
                                    <div class="detail-card-v2 col-span-2"><p class="detail-label-v2">Address</p><div class="detail-value-v2"><p x-text="selectedCompany.address || ''"></p><p x-text="[selectedCompany.city, selectedCompany.state].filter(Boolean).join(', ') + (selectedCompany.zip ? ' ' + selectedCompany.zip : '')"></p></div></div>
                                </template>
                            </div>
                            <template x-if="selectedCompany.adjusters && selectedCompany.adjusters.length > 0">
                                <div>
                                    <h3 class="detail-section-title">Adjusters</h3>
                                    <div class="space-y-1">
                                        <template x-for="adj in selectedCompany.adjusters" :key="adj.id">
                                            <div class="flex items-center justify-between px-3 py-2 rounded-lg" style="background:#f8f7f4;">
                                                <span class="text-sm font-medium" x-text="adj.last_name + ', ' + adj.first_name"></span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-v2-text-light" x-text="adj.title || ''"></span>
                                                    <span class="text-xs px-1.5 py-0.5 rounded" :class="adj.is_active == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" x-text="adj.is_active == 1 ? 'Active' : 'Inactive'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-if="selectedCompany.notes"><div><h3 class="detail-section-title">Notes</h3><p class="text-sm text-v2-text-mid" x-text="selectedCompany.notes"></p></div></template>
                        </div>
                        <div class="modal-v2-footer">
                            <button @click="openEditModal()" class="btn-v2-primary flex-1">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button @click="deleteCompany(selectedCompany.id, selectedCompany.name)" class="btn-v2-danger">Delete</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create/Edit Insurance Modal -->
        <template x-if="showCreateModal || showEditModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showCreateModal ? closeCreateModal() : closeEditModal()">
                <div class="modal-v2-backdrop fixed inset-0" @click="showCreateModal ? closeCreateModal() : closeEditModal()"></div>
                <form @submit.prevent="showCreateModal ? createCompany() : updateCompany()" class="modal-v2 relative w-full max-w-lg z-10" style="max-height:90vh; display:flex; flex-direction:column;" @click.stop>
                    <div class="modal-v2-header flex-shrink-0">
                        <h3 class="modal-v2-title" x-text="showEditModal ? 'Edit Insurance Company' : 'New Insurance Company'"></h3>
                        <button type="button" class="modal-v2-close" @click="showCreateModal ? closeCreateModal() : closeEditModal()"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="modal-v2-body" style="overflow-y:auto; flex:1; min-height:0;">
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="form-v2-label">Company Name *</label><input type="text" x-model="showEditModal ? editCompany.name : newCompany.name" required class="form-v2-input"></div>
                            <div><label class="form-v2-label">Type *</label><select x-model="showEditModal ? editCompany.type : newCompany.type" required class="form-v2-select"><option value="auto">Auto</option><option value="health">Health</option><option value="workers_comp">Worker's Comp</option><option value="liability">Liability</option><option value="um_uim">UM/UIM</option><option value="other">Other</option></select></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-v2-label">Phone</label><input type="text" x-model="showEditModal ? editCompany.phone : newCompany.phone" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Fax</label><input type="text" x-model="showEditModal ? editCompany.fax : newCompany.fax" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Email</label><input type="email" x-model="showEditModal ? editCompany.email : newCompany.email" class="form-v2-input"></div>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div><label class="form-v2-label">Address</label><input type="text" x-model="showEditModal ? editCompany.address : newCompany.address" class="form-v2-input"></div>
                        <div class="grid grid-cols-6 gap-3">
                            <div class="col-span-3"><label class="form-v2-label">City</label><input type="text" x-model="showEditModal ? editCompany.city : newCompany.city" class="form-v2-input"></div>
                            <div><label class="form-v2-label">State</label><input type="text" x-model="showEditModal ? editCompany.state : newCompany.state" maxlength="2" class="form-v2-input uppercase"></div>
                            <div class="col-span-2"><label class="form-v2-label">ZIP</label><input type="text" x-model="showEditModal ? editCompany.zip : newCompany.zip" maxlength="10" class="form-v2-input"></div>
                        </div>
                        <div><label class="form-v2-label">Website</label><input type="url" x-model="showEditModal ? editCompany.website : newCompany.website" class="form-v2-input" placeholder="https://..."></div>
                        <div><label class="form-v2-label">Notes</label><textarea x-model="showEditModal ? editCompany.notes : newCompany.notes" rows="2" class="form-v2-textarea"></textarea></div>
                    </div>
                    <div class="modal-v2-footer flex-shrink-0">
                        <button type="button" @click="showCreateModal ? closeCreateModal() : closeEditModal()" class="btn-v2-cancel">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-v2-primary"><span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span></button>
                    </div>
                </form>
            </div>
        </template>

    </div><!-- /Insurance Tab -->

    <!-- ===================== ADJUSTERS TAB ===================== -->
    <div x-show="activeTab === 'adjusters'" x-cloak x-data="adjustersListPage()" x-init="init()">

        <!-- Top bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.300ms="loadData()"
                           placeholder="Search by name, email, or insurance company..."
                           class="w-80 pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                </div>
                <select x-model="companyFilter" @change="loadData()" class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Companies</option>
                    <template x-for="co in insuranceCompanies" :key="co.id"><option :value="co.id" x-text="co.name"></option></template>
                </select>
                <select x-model="typeFilter" @change="loadData()" class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    <option value="pip">PIP</option>
                    <option value="um">UM</option>
                    <option value="uim">UIM</option>
                    <option value="3rd_party">3rd Party</option>
                    <option value="liability">Liability</option>
                    <option value="pd">PD</option>
                    <option value="bi">BI</option>
                </select>
                <select x-model="activeFilter" @change="loadData()" class="border border-v2-card-border rounded-lg px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <button @click="showCreateModal = true" style="background:#0F1B2D; color:#fff;" class="px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Adjuster
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border" x-init="initScrollContainer($el)">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="cursor-pointer select-none" @click="sort('last_name')"><div class="flex items-center gap-1">Name <template x-if="sortBy==='last_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th class="cursor-pointer select-none" @click="sort('title')"><div class="flex items-center gap-1">Title <template x-if="sortBy==='title'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Type</th>
                        <th class="cursor-pointer select-none" @click="sort('insurance_company_name')"><div class="flex items-center gap-1">Insurance Company <template x-if="sortBy==='insurance_company_name'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Phone</th>
                        <th class="cursor-pointer select-none" @click="sort('email')"><div class="flex items-center gap-1">Email <template x-if="sortBy==='email'"><svg class="w-3 h-3" :class="sortDir==='asc'?'':'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.707a1 1 0 011.414 0L10 11l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg></template></div></th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading"><tr><td colspan="7" class="text-center py-8"><div class="spinner mx-auto"></div></td></tr></template>
                    <template x-if="!loading && items.length === 0"><tr><td colspan="7" class="text-center text-v2-text-light py-8">No adjusters found</td></tr></template>
                    <template x-for="a in items" :key="a.id">
                        <tr @click="viewAdjuster(a.id)" class="db-row" :class="selectedAdjuster?.id === a.id ? 'db-row-active' : ''">
                            <td class="font-medium" style="color:#7d693c" x-text="a.last_name + ', ' + a.first_name"></td>
                            <td class="text-sm text-v2-text-mid" x-text="a.title || '-'"></td>
                            <td class="text-sm"><span x-show="a.adjuster_type" class="text-xs px-1.5 py-0.5 rounded font-medium bg-blue-50 text-blue-700" x-text="getTypeLabel(a.adjuster_type)"></span><span x-show="!a.adjuster_type" class="text-v2-text-light">-</span></td>
                            <td class="text-sm" x-text="a.insurance_company_name || '-'"></td>
                            <td class="whitespace-nowrap text-sm" x-text="a.phone || '-'"></td>
                            <td class="text-xs whitespace-nowrap" x-text="a.email || '-'"></td>
                            <td><span class="text-xs px-1.5 py-0.5 rounded font-medium" :class="a.is_active == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" x-text="a.is_active == 1 ? 'Active' : 'Inactive'"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="px-6 py-3 border-t border-v2-card-border">
                <div class="text-sm text-v2-text-light">Showing <span x-text="items.length"></span> adjuster<span x-text="items.length === 1 ? '' : 's'"></span></div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div x-show="selectedAdjuster" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="selectedAdjuster = null">
            <div class="modal-v2-backdrop fixed inset-0" @click="selectedAdjuster = null"></div>
            <div @click.stop class="modal-v2 relative w-full max-w-md z-10">
                <template x-if="selectedAdjuster">
                    <div>
                        <div class="modal-v2-header">
                            <div class="flex-1 pr-4">
                                <h2 class="modal-v2-title" x-text="selectedAdjuster.first_name + ' ' + selectedAdjuster.last_name"></h2>
                                <div class="flex items-center gap-2 mt-1">
                                    <template x-if="selectedAdjuster.title"><span class="text-xs" style="color:rgba(255,255,255,0.7);" x-text="selectedAdjuster.title"></span></template>
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium" :class="selectedAdjuster.is_active == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" x-text="selectedAdjuster.is_active == 1 ? 'Active' : 'Inactive'"></span>
                                </div>
                            </div>
                            <button type="button" class="modal-v2-close" @click="selectedAdjuster = null"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <div class="modal-v2-body space-y-4">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="detail-card-v2"><p class="detail-label-v2">Type</p><p class="detail-value-v2" x-text="getTypeLabel(selectedAdjuster.adjuster_type)"></p></div>
                                <div class="detail-card-v2"><p class="detail-label-v2">Insurance Company</p><p class="detail-value-v2" x-text="selectedAdjuster.insurance_company_name || '—'"></p></div>
                                <div class="detail-card-v2"><p class="detail-label-v2">Phone</p><p class="detail-value-v2" x-text="selectedAdjuster.phone || '—'"></p></div>
                                <div class="detail-card-v2"><p class="detail-label-v2">Fax</p><p class="detail-value-v2" x-text="selectedAdjuster.fax || '—'"></p></div>
                                <div class="detail-card-v2 col-span-2"><p class="detail-label-v2">Email</p><p class="detail-value-v2 break-all" x-text="selectedAdjuster.email || '—'"></p></div>
                            </div>
                            <template x-if="selectedAdjuster.notes"><div><h3 class="detail-section-title">Notes</h3><p class="text-sm text-v2-text-mid" x-text="selectedAdjuster.notes"></p></div></template>
                        </div>
                        <div class="modal-v2-footer">
                            <button @click="openEditModal()" class="btn-v2-primary flex-1">Edit</button>
                            <button @click="toggleActive(selectedAdjuster.id, selectedAdjuster.is_active)" class="btn-v2-cancel" x-text="selectedAdjuster.is_active == 1 ? 'Deactivate' : 'Activate'"></button>
                            <button @click="deleteAdjuster(selectedAdjuster.id, selectedAdjuster.first_name + ' ' + selectedAdjuster.last_name)" class="btn-v2-danger">Delete</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Create/Edit Adjuster Modal -->
        <template x-if="showCreateModal || showEditModal">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showCreateModal ? closeCreateModal() : closeEditModal()">
                <div class="modal-v2-backdrop fixed inset-0" @click="showCreateModal ? closeCreateModal() : closeEditModal()"></div>
                <form @submit.prevent="showCreateModal ? createAdjuster() : updateAdjuster()" class="modal-v2 relative w-full max-w-md z-10" @click.stop>
                    <div class="modal-v2-header">
                        <h3 class="modal-v2-title" x-text="showEditModal ? 'Edit Adjuster' : 'New Adjuster'"></h3>
                        <button type="button" class="modal-v2-close" @click="showCreateModal ? closeCreateModal() : closeEditModal()"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="modal-v2-body">
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="form-v2-label">First Name *</label><input type="text" x-model="showEditModal ? editAdjuster.first_name : newAdjuster.first_name" required class="form-v2-input"></div>
                            <div><label class="form-v2-label">Last Name *</label><input type="text" x-model="showEditModal ? editAdjuster.last_name : newAdjuster.last_name" required class="form-v2-input"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="form-v2-label">Title</label><input type="text" x-model="showEditModal ? editAdjuster.title : newAdjuster.title" class="form-v2-input" placeholder="e.g., Claims Adjuster"></div>
                            <div><label class="form-v2-label">Type</label>
                                <select x-model="showEditModal ? editAdjuster.adjuster_type : newAdjuster.adjuster_type" class="form-v2-select">
                                    <option value="">None</option>
                                    <option value="pip">PIP</option>
                                    <option value="um">UM</option>
                                    <option value="uim">UIM</option>
                                    <option value="3rd_party">3rd Party</option>
                                    <option value="liability">Liability</option>
                                    <option value="pd">PD</option>
                                    <option value="bi">BI</option>
                                </select>
                            </div>
                        </div>
                        <div><label class="form-v2-label">Insurance Company</label>
                            <select x-model="showEditModal ? editAdjuster.insurance_company_id : newAdjuster.insurance_company_id" class="form-v2-select">
                                <option value="">None</option>
                                <template x-for="co in insuranceCompanies" :key="co.id"><option :value="co.id" x-text="co.name"></option></template>
                            </select>
                        </div>
                        <div class="form-v2-divider"></div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="form-v2-label">Phone</label><input type="text" x-model="showEditModal ? editAdjuster.phone : newAdjuster.phone" class="form-v2-input"></div>
                            <div><label class="form-v2-label">Fax</label><input type="text" x-model="showEditModal ? editAdjuster.fax : newAdjuster.fax" class="form-v2-input"></div>
                        </div>
                        <div><label class="form-v2-label">Email</label><input type="email" x-model="showEditModal ? editAdjuster.email : newAdjuster.email" class="form-v2-input"></div>
                        <div><label class="form-v2-label">Notes</label><textarea x-model="showEditModal ? editAdjuster.notes : newAdjuster.notes" rows="2" class="form-v2-textarea"></textarea></div>
                    </div>
                    <div class="modal-v2-footer">
                        <button type="button" @click="showCreateModal ? closeCreateModal() : closeEditModal()" class="btn-v2-cancel">Cancel</button>
                        <button type="submit" :disabled="saving" class="btn-v2-primary"><span x-text="saving ? 'Saving...' : (showEditModal ? 'Update' : 'Create')"></span></button>
                    </div>
                </form>
            </div>
        </template>

    </div><!-- /Adjusters Tab -->

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
