<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAuth();
$pageTitle = 'Provider Database';
$currentPage = 'providers';
$pageScripts = ['/MRMS/frontend/assets/js/pages/providers.js'];
ob_start();
?>

<div x-data="providersListPage()" x-init="loadData()">

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

        <button @click="showCreateModal = true"
                class="bg-gold text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gold-hover flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Provider
        </button>
    </div>

    <!-- Providers Table (full width) -->
    <div>
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border">
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
                                <tr class="cursor-pointer" @click="viewProvider(p.id)" :class="[selectedProvider?.id === p.id ? 'bg-v2-bg' : 'hover:bg-v2-bg', p.is_suspicious == 1 ? 'bg-blue-50' : '']">
                                    <td class="font-medium" :class="p.is_suspicious == 1 ? 'text-blue-600' : 'text-gold'" x-text="p.name"></td>
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
    <div
        x-show="selectedProvider"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="selectedProvider = null"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="modal-v2-backdrop fixed inset-0" @click="selectedProvider = null"></div>
        <div
            x-show="selectedProvider"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
            class="modal-v2 relative w-full max-w-2xl z-10"
            style="max-height: 90vh;"
        >
            <template x-if="selectedProvider">
                <div>
                    <!-- Header -->
                    <div class="modal-v2-header">
                        <div class="flex-1 pr-4">
                            <h2 class="modal-v2-title" x-text="selectedProvider.name"></h2>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span
                                    class="modal-v2-subtitle text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider"
                                    style="background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.7);"
                                    x-text="getProviderTypeLabel(selectedProvider.type)"
                                ></span>
                                <template x-if="selectedProvider.difficulty_level">
                                    <span
                                        class="text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider"
                                        :style="getDifficultyStyle(selectedProvider.difficulty_level)"
                                        x-text="selectedProvider.difficulty_level"
                                    ></span>
                                </template>
                                <template x-if="selectedProvider.uses_third_party == 1">
                                    <span
                                        class="text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider"
                                        style="background: rgba(201,168,76,0.15); color: #C9A84C;"
                                    >ChartSwap</span>
                                </template>
                            </div>
                        </div>
                        <button type="button" class="modal-v2-close" @click="selectedProvider = null">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-v2-body space-y-5 overflow-y-auto" style="max-height: 60vh;">
                        <!-- Contact Information -->
                        <div>
                            <h3 class="detail-section-title">Contact Information</h3>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="detail-card-v2">
                                    <p class="detail-label-v2">Phone</p>
                                    <p class="detail-value-v2" :class="!selectedProvider.phone && 'empty'" x-text="formatPhoneNumber(selectedProvider.phone)"></p>
                                </div>
                                <div class="detail-card-v2">
                                    <p class="detail-label-v2">Fax</p>
                                    <p class="detail-value-v2" :class="!selectedProvider.fax && 'empty'" x-text="formatPhoneNumber(selectedProvider.fax)"></p>
                                </div>
                                <div class="detail-card-v2 col-span-2">
                                    <p class="detail-label-v2">Email</p>
                                    <p class="detail-value-v2 break-all" :class="!selectedProvider.email && 'empty'" x-text="selectedProvider.email || '—'"></p>
                                </div>
                                <template x-if="selectedProvider.address || selectedProvider.city">
                                    <div class="detail-card-v2 col-span-2">
                                        <p class="detail-label-v2">Address</p>
                                        <div class="detail-value-v2">
                                            <p x-show="selectedProvider.address" x-text="selectedProvider.address"></p>
                                            <p x-show="selectedProvider.city || selectedProvider.state || selectedProvider.zip">
                                                <span x-text="selectedProvider.city || ''"></span><span x-show="selectedProvider.city && selectedProvider.state">, </span><span x-text="selectedProvider.state || ''"></span><span x-show="selectedProvider.zip"> </span><span x-text="selectedProvider.zip || ''"></span>
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="detail-card-v2">
                                <p class="detail-label-v2">Avg Response</p>
                                <p class="detail-value-v2 large" :style="{ color: getAvgColor(selectedProvider.avg_response_days) }" x-text="selectedProvider.avg_response_days ? selectedProvider.avg_response_days + 'd' : '—'"></p>
                            </div>
                            <div class="detail-card-v2">
                                <p class="detail-label-v2">Preferred Method</p>
                                <p class="detail-value-v2 large" x-text="getRequestMethodLabel(selectedProvider.preferred_method)"></p>
                            </div>
                        </div>

                        <!-- Department Contacts -->
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

                    <!-- Footer -->
                    <div class="modal-v2-footer">
                        <button
                            @click="editProvider = { ...selectedProvider, contacts: selectedProvider.contacts || [] }; showProviderModal = true; selectedProvider = null"
                            class="btn-v2-primary flex-1"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit Provider
                        </button>
                        <button
                            @click="deleteProvider(selectedProvider.id, selectedProvider.name)"
                            class="btn-v2-danger"
                        >
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
                <div class="modal-v2-header flex-shrink-0">
                    <div>
                        <h3 class="modal-v2-title">New Provider</h3>
                    </div>
                    <button type="button" class="modal-v2-close" @click="closeCreateModal()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                    <!-- Row 1: Name + Type + Method + Difficulty -->
                    <div class="grid grid-cols-4 gap-3">
                        <div class="col-span-2">
                            <label class="form-v2-label">Provider Name *</label>
                            <input type="text" x-model="newProvider.name" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Type *</label>
                            <select x-model="newProvider.type" required class="form-v2-select">
                                <option value="acupuncture">Acupuncture</option>
                                <option value="chiro">Chiropractor</option>
                                <option value="massage">Massage</option>
                                <option value="pain_management">Pain Mgmt</option>
                                <option value="pt">Physical Therapy</option>
                                <option value="er">Emergency Room</option>
                                <option value="hospital">Hospital</option>
                                <option value="physician">Physician</option>
                                <option value="imaging">Imaging Center</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="surgery_center">Surgery Center</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-v2-label">Difficulty</label>
                            <select x-model="newProvider.difficulty_level" class="form-v2-select">
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 2: Address -->
                    <div>
                        <label class="form-v2-label">Street Address</label>
                        <input type="text" x-model="newProvider.address" class="form-v2-input">
                    </div>
                    <div class="grid grid-cols-6 gap-3">
                        <div class="col-span-3">
                            <label class="form-v2-label">City</label>
                            <input type="text" x-model="newProvider.city" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">State</label>
                            <input type="text" x-model="newProvider.state" maxlength="2" placeholder="WA" class="form-v2-input uppercase">
                        </div>
                        <div class="col-span-2">
                            <label class="form-v2-label">ZIP</label>
                            <input type="text" x-model="newProvider.zip" maxlength="10" placeholder="98036" class="form-v2-input">
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 3: Phone + Fax + Email -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-v2-label">Phone</label>
                            <input type="text" x-model="newProvider.phone" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Fax</label>
                            <input type="text" x-model="newProvider.fax" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Email</label>
                            <input type="email" x-model="newProvider.email" class="form-v2-input">
                        </div>
                    </div>

                    <!-- Row 4: Method + Portal URL + Third Party -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-v2-label">Preferred Method</label>
                            <select x-model="newProvider.preferred_method" class="form-v2-select">
                                <option value="fax">Fax</option>
                                <option value="email">Email</option>
                                <option value="portal">Portal</option>
                                <option value="phone">Phone</option>
                                <option value="mail">Mail</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-v2-label">Portal URL</label>
                            <input type="url" x-model="newProvider.portal_url" class="form-v2-input">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm" style="accent-color: var(--gold);">
                                <input type="checkbox" x-model="newProvider.uses_third_party" class="rounded"> Uses third party for records
                            </label>
                        </div>
                    </div>

                    <!-- Third party fields (conditional) -->
                    <div x-show="newProvider.uses_third_party" class="grid grid-cols-2 gap-3" x-collapse>
                        <div>
                            <label class="form-v2-label">Third Party Name</label>
                            <input type="text" x-model="newProvider.third_party_name" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Third Party Contact</label>
                            <input type="text" x-model="newProvider.third_party_contact" class="form-v2-input">
                        </div>
                    </div>

                    <!-- Department Contacts -->
                    <div class="form-v2-divider"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="form-v2-label mb-0">Department Contacts</label>
                            <button type="button" @click="addContact(newProvider)" class="text-xs text-gold hover:text-gold-hover font-semibold">+ Add Contact</button>
                        </div>
                        <template x-for="(contact, idx) in newProvider.contacts" :key="idx">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" x-model="contact.department" placeholder="Department name" class="form-v2-input text-sm" style="flex:2;">
                                <select x-model="contact.contact_type" class="form-v2-select text-sm" style="flex:1;">
                                    <option value="email">Email</option>
                                    <option value="fax">Fax</option>
                                    <option value="phone">Phone</option>
                                    <option value="portal">Portal</option>
                                </select>
                                <input type="text" x-model="contact.contact_value" :placeholder="contact.contact_type === 'email' ? 'Email address' : contact.contact_type === 'fax' ? 'Fax number' : contact.contact_type === 'phone' ? 'Phone number' : 'Portal URL'" class="form-v2-input text-sm" style="flex:2;">
                                <button type="button" @click="setPrimary(newProvider, idx)"
                                        class="text-xs px-2 py-1 rounded font-bold flex-shrink-0"
                                        :class="contact.is_primary == 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'"
                                        x-text="contact.is_primary == 1 ? 'PRIMARY' : 'Set'"></button>
                                <button type="button" @click="removeContact(newProvider, idx)" class="text-red-400 hover:text-red-600 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="newProvider.contacts.length === 0">
                            <p class="text-xs text-v2-text-light">No department contacts. Use the main phone/fax/email fields above, or add specific department contacts.</p>
                        </template>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-v2-label">Notes</label>
                        <textarea x-model="newProvider.notes" rows="2" class="form-v2-textarea" placeholder="Optional notes about this provider..."></textarea>
                    </div>
                </div>

                <div class="modal-v2-footer flex-shrink-0">
                    <button type="button" @click="closeCreateModal()" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="saving ? 'Creating...' : 'Create Provider'"></span>
                    </button>
                </div>
            </form>
        </div>
    </template>

    <!-- Edit Provider Modal -->
    <template x-if="showProviderModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeEditModal()">
            <div class="modal-v2-backdrop fixed inset-0" @click="closeEditModal()"></div>
            <form @submit.prevent="updateProvider()" class="modal-v2 relative w-full max-w-2xl z-10" style="max-height: 90vh; display: flex; flex-direction: column;" @click.stop>
                <div class="modal-v2-header flex-shrink-0">
                    <div>
                        <h3 class="modal-v2-title">Edit Provider</h3>
                    </div>
                    <button type="button" class="modal-v2-close" @click="closeEditModal()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-v2-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                    <!-- Row 1: Name + Type + Method + Difficulty -->
                    <div class="grid grid-cols-4 gap-3">
                        <div class="col-span-2">
                            <label class="form-v2-label">Provider Name *</label>
                            <input type="text" x-model="editProvider.name" required class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Type *</label>
                            <select x-model="editProvider.type" required class="form-v2-select">
                                <option value="acupuncture">Acupuncture</option>
                                <option value="chiro">Chiropractor</option>
                                <option value="massage">Massage</option>
                                <option value="pain_management">Pain Mgmt</option>
                                <option value="pt">Physical Therapy</option>
                                <option value="er">Emergency Room</option>
                                <option value="hospital">Hospital</option>
                                <option value="physician">Physician</option>
                                <option value="imaging">Imaging Center</option>
                                <option value="pharmacy">Pharmacy</option>
                                <option value="surgery_center">Surgery Center</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-v2-label">Difficulty</label>
                            <select x-model="editProvider.difficulty_level" class="form-v2-select">
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 2: Address -->
                    <div>
                        <label class="form-v2-label">Street Address</label>
                        <input type="text" x-model="editProvider.address" class="form-v2-input">
                    </div>
                    <div class="grid grid-cols-6 gap-3">
                        <div class="col-span-3">
                            <label class="form-v2-label">City</label>
                            <input type="text" x-model="editProvider.city" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">State</label>
                            <input type="text" x-model="editProvider.state" maxlength="2" placeholder="WA" class="form-v2-input uppercase">
                        </div>
                        <div class="col-span-2">
                            <label class="form-v2-label">ZIP</label>
                            <input type="text" x-model="editProvider.zip" maxlength="10" placeholder="98036" class="form-v2-input">
                        </div>
                    </div>

                    <div class="form-v2-divider"></div>

                    <!-- Row 3: Phone + Fax + Email -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-v2-label">Phone</label>
                            <input type="text" x-model="editProvider.phone" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Fax</label>
                            <input type="text" x-model="editProvider.fax" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Email</label>
                            <input type="email" x-model="editProvider.email" class="form-v2-input">
                        </div>
                    </div>

                    <!-- Row 4: Method + Portal URL + Third Party -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-v2-label">Preferred Method</label>
                            <select x-model="editProvider.preferred_method" class="form-v2-select">
                                <option value="fax">Fax</option>
                                <option value="email">Email</option>
                                <option value="portal">Portal</option>
                                <option value="phone">Phone</option>
                                <option value="mail">Mail</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-v2-label">Portal URL</label>
                            <input type="url" x-model="editProvider.portal_url" class="form-v2-input">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 text-sm" style="accent-color: var(--gold);">
                                <input type="checkbox" x-model="editProvider.uses_third_party" class="rounded"> Uses third party for records
                            </label>
                        </div>
                    </div>

                    <!-- Third party fields (conditional) -->
                    <div x-show="editProvider.uses_third_party" class="grid grid-cols-2 gap-3" x-collapse>
                        <div>
                            <label class="form-v2-label">Third Party Name</label>
                            <input type="text" x-model="editProvider.third_party_name" class="form-v2-input">
                        </div>
                        <div>
                            <label class="form-v2-label">Third Party Contact</label>
                            <input type="text" x-model="editProvider.third_party_contact" class="form-v2-input">
                        </div>
                    </div>

                    <!-- Department Contacts -->
                    <div class="form-v2-divider"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="form-v2-label mb-0">Department Contacts</label>
                            <button type="button" @click="addContact(editProvider)" class="text-xs text-gold hover:text-gold-hover font-semibold">+ Add Contact</button>
                        </div>
                        <template x-for="(contact, idx) in editProvider.contacts" :key="idx">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" x-model="contact.department" placeholder="Department name" class="form-v2-input text-sm" style="flex:2;">
                                <select x-model="contact.contact_type" class="form-v2-select text-sm" style="flex:1;">
                                    <option value="email">Email</option>
                                    <option value="fax">Fax</option>
                                    <option value="phone">Phone</option>
                                    <option value="portal">Portal</option>
                                </select>
                                <input type="text" x-model="contact.contact_value" :placeholder="contact.contact_type === 'email' ? 'Email address' : contact.contact_type === 'fax' ? 'Fax number' : contact.contact_type === 'phone' ? 'Phone number' : 'Portal URL'" class="form-v2-input text-sm" style="flex:2;">
                                <button type="button" @click="setPrimary(editProvider, idx)"
                                        class="text-xs px-2 py-1 rounded font-bold flex-shrink-0"
                                        :class="contact.is_primary == 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'"
                                        x-text="contact.is_primary == 1 ? 'PRIMARY' : 'Set'"></button>
                                <button type="button" @click="removeContact(editProvider, idx)" class="text-red-400 hover:text-red-600 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="editProvider.contacts.length === 0">
                            <p class="text-xs text-v2-text-light">No department contacts added.</p>
                        </template>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-v2-label">Notes</label>
                        <textarea x-model="editProvider.notes" rows="2" class="form-v2-textarea" placeholder="Optional notes about this provider..."></textarea>
                    </div>
                </div>

                <div class="modal-v2-footer flex-shrink-0">
                    <button type="button" @click="closeEditModal()" class="btn-v2-cancel">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-v2-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="saving ? 'Saving...' : 'Update Provider'"></span>
                    </button>
                </div>
            </form>
        </div>
    </template>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
