            <!-- Activity Log section -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border" x-data="{logOpen: false}">
                <div class="px-6 py-3 flex items-center justify-between cursor-pointer" @click="logOpen = !logOpen">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="logOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <h3 class="font-semibold text-v2-text text-sm">Activity Log</h3>
                        <span class="text-xs text-v2-text-light" x-text="'(' + notes.length + ')'"></span>
                    </div>
                    <select x-model="noteFilterProvider" @change="loadNotes()" @click.stop
                        class="border border-v2-card-border rounded-lg px-2 py-1 text-xs">
                        <option value="">All Providers</option>
                        <template x-for="prov in providers" :key="prov.id">
                            <option :value="prov.id" x-text="prov.provider_name"></option>
                        </template>
                    </select>
                </div>
                <div class="px-6 pb-4" x-show="logOpen" x-collapse>
                    <!-- Add note form -->
                    <form @submit.prevent="addNote()" class="mb-4 space-y-2">
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
