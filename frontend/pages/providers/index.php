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
                    <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="loadData()"
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
        <div class="bg-white rounded-xl shadow-sm border border-v2-card-border overflow-hidden">
                <div class="overflow-x-auto">
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
                            <template x-if="!loading && providers.length === 0">
                                <tr><td colspan="8" class="text-center text-v2-text-light py-8">No providers found</td></tr>
                            </template>
                            <template x-for="p in providers" :key="p.id">
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
                </div>

                <div class="px-6 py-3 border-t border-v2-card-border">
                    <div class="text-sm text-v2-text-light">
                        Showing <span x-text="providers.length"></span> provider<span x-text="providers.length === 1 ? '' : 's'"></span>
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
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-black/40" @click="selectedProvider = null"></div>

        <div
            x-show="selectedProvider"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
            class="relative w-full max-w-2xl bg-white rounded-lg shadow-2xl overflow-hidden"
            style="max-height: 90vh;"
        >
            <template x-if="selectedProvider">
                <div>
                    <!-- Header -->
                    <div class="px-6 py-5" style="background: #0F1B2D;">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 pr-4">
                                <h2 class="text-lg font-black text-white leading-tight" x-text="selectedProvider.name"></h2>
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    <span
                                        class="text-xs font-bold px-2.5 py-0.5 rounded uppercase tracking-wider"
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
                            <button @click="selectedProvider = null" class="p-1 rounded" style="color: rgba(255,255,255,0.5);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-5 overflow-y-auto" style="max-height: 60vh;">
                        <!-- Contact Information -->
                        <div>
                            <h3 class="text-xs font-black uppercase mb-3" style="color: #5A6B82; letter-spacing: 0.1em;">Contact Information</h3>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="rounded px-3 py-2.5" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                    <p class="text-xs font-bold uppercase" style="color: #5A6B82; letter-spacing: 0.1em;">Phone</p>
                                    <p class="text-sm font-semibold mt-0.5" :style="{ color: selectedProvider.phone ? '#0F1B2D' : '#5A6B82' }" x-text="formatPhoneNumber(selectedProvider.phone)"></p>
                                </div>
                                <div class="rounded px-3 py-2.5" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                    <p class="text-xs font-bold uppercase" style="color: #5A6B82; letter-spacing: 0.1em;">Fax</p>
                                    <p class="text-sm font-semibold mt-0.5" :style="{ color: selectedProvider.fax ? '#0F1B2D' : '#5A6B82' }" x-text="formatPhoneNumber(selectedProvider.fax)"></p>
                                </div>
                                <div class="col-span-2 rounded px-3 py-2.5" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                    <p class="text-xs font-bold uppercase" style="color: #5A6B82; letter-spacing: 0.1em;">Email</p>
                                    <p class="text-sm font-semibold mt-0.5 break-all" :style="{ color: selectedProvider.email ? '#0F1B2D' : '#5A6B82' }" x-text="selectedProvider.email || '—'"></p>
                                </div>
                                <template x-if="selectedProvider.address || selectedProvider.city">
                                    <div class="col-span-2 rounded px-3 py-2.5" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                        <p class="text-xs font-bold uppercase" style="color: #5A6B82; letter-spacing: 0.1em;">Address</p>
                                        <div class="text-sm font-semibold mt-0.5" style="color: #0F1B2D;">
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
                            <div class="rounded px-4 py-3" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                <p class="text-xs font-bold uppercase" style="color: #5A6B82; letter-spacing: 0.1em;">Avg Response</p>
                                <p class="text-xl font-black mt-0.5" :style="{ color: getAvgColor(selectedProvider.avg_response_days) }" x-text="selectedProvider.avg_response_days ? selectedProvider.avg_response_days + 'd' : '—'"></p>
                            </div>
                            <div class="rounded px-4 py-3" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                <p class="text-xs font-bold uppercase" style="color: #5A6B82; letter-spacing: 0.1em;">Preferred Method</p>
                                <p class="text-xl font-black mt-0.5" style="color: #0F1B2D;" x-text="getRequestMethodLabel(selectedProvider.preferred_method)"></p>
                            </div>
                        </div>

                        <!-- Department Contacts -->
                        <template x-if="selectedProvider.contacts && selectedProvider.contacts.length > 0">
                            <div>
                                <h3 class="text-xs font-black uppercase mb-3" style="color: #5A6B82; letter-spacing: 0.1em;">Department Contacts</h3>
                                <div class="space-y-1.5">
                                    <template x-for="(contact, idx) in selectedProvider.contacts" :key="idx">
                                        <div class="flex items-center gap-3 px-3 py-2 rounded text-sm" style="background: #F5F5F0; border: 1px solid #E5E5E0;">
                                            <span class="font-bold flex-shrink-0" style="color: #0F1B2D; min-width: 100px;" x-text="contact.department"></span>
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
                    <div class="px-6 py-4 flex items-center gap-3" style="border-top: 1px solid #E5E5E0; background: #F5F5F0;">
                        <div class="flex items-center gap-3 w-full">
                            <button
                                @click="editProvider = selectedProvider; showProviderModal = true; selectedProvider = null"
                                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded text-sm font-bold"
                                style="background: #0F1B2D; color: #C9A84C;"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Provider
                            </button>
                            <button
                                @click="deleteProvider(selectedProvider.id, selectedProvider.name)"
                                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded text-sm font-bold"
                                style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Create Provider Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showCreateModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold">New Provider</h3>
                <button @click="showCreateModal = false" class="text-v2-text-light hover:text-v2-text-mid">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="createProvider()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-v2-text mb-1">Provider Name *</label>
                        <input type="text" x-model="newProvider.name" required
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Type *</label>
                        <select x-model="newProvider.type" required
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="acupuncture">Acupuncture</option>
                            <option value="chiro">Chiropractor</option>
                            <option value="massage">Massage</option>
                            <option value="pain_management">Pain Management</option>
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
                        <label class="block text-sm font-medium text-v2-text mb-1">Preferred Method</label>
                        <select x-model="newProvider.preferred_method"
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="fax">Fax</option>
                            <option value="email">Email</option>
                            <option value="portal">Portal</option>
                            <option value="phone">Phone</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Street Address</label>
                    <input type="text" x-model="newProvider.address"
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-v2-text mb-1">City</label>
                        <input type="text" x-model="newProvider.city"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">State</label>
                        <input type="text" x-model="newProvider.state" maxlength="2" placeholder="WA"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm uppercase">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">ZIP Code</label>
                        <input type="text" x-model="newProvider.zip" maxlength="10" placeholder="98036"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Phone</label>
                        <input type="text" x-model="newProvider.phone"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Fax</label>
                        <input type="text" x-model="newProvider.fax"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Email</label>
                        <input type="email" x-model="newProvider.email"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Portal URL</label>
                        <input type="url" x-model="newProvider.portal_url"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Difficulty</label>
                        <select x-model="newProvider.difficulty_level"
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
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
                            <label class="block text-sm font-medium text-v2-text mb-1">Third Party Name</label>
                            <input type="text" x-model="newProvider.third_party_name"
                                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-v2-text mb-1">Third Party Contact</label>
                            <input type="text" x-model="newProvider.third_party_contact"
                                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Notes</label>
                    <textarea x-model="newProvider.notes" rows="2"
                              class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">
                        <span x-text="saving ? 'Creating...' : 'Create Provider'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Provider Modal -->
    <div x-show="showProviderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="modal-overlay fixed inset-0" @click="showProviderModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10" @click.stop>
            <div class="px-6 py-4 border-b border-v2-card-border flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold">Edit Provider</h3>
                <button @click="showProviderModal = false" class="text-v2-text-light hover:text-v2-text-mid">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="updateProvider()" class="p-6 space-y-4" x-show="editProvider">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-v2-text mb-1">Provider Name *</label>
                        <input type="text" x-model="editProvider.name" required
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Type *</label>
                        <select x-model="editProvider.type" required
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="acupuncture">Acupuncture</option>
                            <option value="chiro">Chiropractor</option>
                            <option value="massage">Massage</option>
                            <option value="pain_management">Pain Management</option>
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
                        <label class="block text-sm font-medium text-v2-text mb-1">Preferred Method</label>
                        <select x-model="editProvider.preferred_method"
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="fax">Fax</option>
                            <option value="email">Email</option>
                            <option value="portal">Portal</option>
                            <option value="phone">Phone</option>
                            <option value="mail">Mail</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Street Address</label>
                    <input type="text" x-model="editProvider.address"
                           class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-v2-text mb-1">City</label>
                        <input type="text" x-model="editProvider.city"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">State</label>
                        <input type="text" x-model="editProvider.state" maxlength="2" placeholder="WA"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm uppercase">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">ZIP Code</label>
                        <input type="text" x-model="editProvider.zip" maxlength="10" placeholder="98036"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Phone</label>
                        <input type="text" x-model="editProvider.phone"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Fax</label>
                        <input type="text" x-model="editProvider.fax"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Email</label>
                        <input type="email" x-model="editProvider.email"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Portal URL</label>
                        <input type="url" x-model="editProvider.portal_url"
                               class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-v2-text mb-1">Difficulty</label>
                        <select x-model="editProvider.difficulty_level"
                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>

                <!-- Third party -->
                <div>
                    <label class="flex items-center gap-2 text-sm mb-2">
                        <input type="checkbox" x-model="editProvider.uses_third_party" class="rounded"> Uses third party for records
                    </label>
                    <div x-show="editProvider.uses_third_party" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-v2-text mb-1">Third Party Name</label>
                            <input type="text" x-model="editProvider.third_party_name"
                                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-v2-text mb-1">Third Party Contact</label>
                            <input type="text" x-model="editProvider.third_party_contact"
                                   class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Notes</label>
                    <textarea x-model="editProvider.notes" rows="2"
                              class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showProviderModal = false" class="px-4 py-2 text-sm border rounded-lg hover:bg-v2-bg">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm text-white bg-gold rounded-lg hover:bg-gold-hover disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : 'Update Provider'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function providersListPage() {
    return {
        providers: [],
        loading: true,
        searchQuery: '',
        typeFilter: '',
        difficultyFilter: '',
        sortBy: '',
        sortDir: 'asc',
        showCreateModal: false,
        showDetailModal: false,
        showProviderModal: false,
        saving: false,
        detailProvider: null,
        selectedProvider: null,
        editProvider: {
            id: null, name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
            phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
            third_party_name: '', third_party_contact: '', notes: ''
        },
        newProvider: {
            name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
            phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
            third_party_name: '', third_party_contact: '', notes: ''
        },

        getDifficultyStyle(level) {
            const styles = {
                easy:   { background: '#F0FDF4', color: '#166534' },
                medium: { background: '#FFFBEB', color: '#D97706' },
                hard:   { background: '#FEF2F2', color: '#DC2626' }
            };
            return styles[level] || {};
        },

        getAvgColor(days) {
            if (!days) return '#5A6B82';
            if (days > 21) return '#DC2626';
            if (days > 10) return '#D97706';
            return '#166534';
        },

        getContactTypeStyle(type) {
            const styles = {
                email:  { background: '#EFF6FF', color: '#1E40AF' },
                fax:    { background: '#F5F3FF', color: '#6B21A8' },
                phone:  { background: '#ECFEFF', color: '#0E7490' },
                portal: { background: '#FFF7ED', color: '#C2410C' }
            };
            return styles[type] || { background: '#F5F5F0', color: '#5A6B82' };
        },

        async loadData() {
            this.loading = true;
            const params = buildQueryString({
                search: this.searchQuery,
                type: this.typeFilter,
                difficulty_level: this.difficultyFilter,
                sort_by: this.sortBy,
                sort_dir: this.sortDir
            });
            try {
                const res = await api.get('providers' + params);
                this.providers = res.data || [];
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
            this.loadData();
        },

        async viewProvider(id) {
            try {
                const res = await api.get('providers/' + id);
                this.detailProvider = res.data;
                this.selectedProvider = res.data;
            } catch (e) {
                showToast('Failed to load provider', 'error');
            }
        },

        async deleteProvider(id, name) {
            if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
            try {
                await api.delete('providers/' + id);
                showToast('Provider deleted');
                this.selectedProvider = null;
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to delete provider', 'error');
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
                    name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
                    phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
                    third_party_name: '', third_party_contact: '', notes: ''
                };
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create provider', 'error');
            }
            this.saving = false;
        },

        async updateProvider() {
            if (!this.editProvider.id) return;
            this.saving = true;
            try {
                const data = { ...this.editProvider };
                data.uses_third_party = data.uses_third_party ? 1 : 0;
                await api.put('providers/' + data.id, data);
                showToast('Provider updated successfully');
                this.showProviderModal = false;
                this.editProvider = {
                    id: null, name: '', type: 'hospital', preferred_method: 'fax', address: '', city: '', state: '', zip: '',
                    phone: '', fax: '', email: '', portal_url: '', difficulty_level: 'medium', uses_third_party: false,
                    third_party_name: '', third_party_contact: '', notes: ''
                };
                this.loadData();
            } catch (e) {
                showToast(e.data?.message || 'Failed to update provider', 'error');
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
