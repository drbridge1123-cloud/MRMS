
            <!-- Documents Section -->
            <div class="bg-white rounded-xl shadow-sm border border-v2-card-border mb-6"
                x-data="{...documentUploader(caseId), docsOpen: false}" x-init="init()">
                <div class="px-6 py-3 flex items-center justify-between cursor-pointer" @click="docsOpen = !docsOpen">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-v2-text-light transition-transform" :class="docsOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <h3 class="font-semibold text-v2-text text-sm">Documents</h3>
                        <span class="text-xs text-v2-text-light" x-text="'(' + documents.length + ')'"></span>
                    </div>
                    <button @click.stop="docsOpen = true; $nextTick(() => $refs.fileInput.click())"
                        class="bg-gold text-white px-2.5 py-1 rounded-lg text-xs hover:bg-gold-hover">
                        Upload
                    </button>
                </div>
                <div class="px-6 pb-4" x-show="docsOpen" x-collapse>
                    <!-- Upload Area -->
                    <div class="mb-4"
                        @dragover="handleDragOver($event)"
                        @dragleave="handleDragLeave()"
                        @drop="handleDrop($event)">
                        <!-- Drag and Drop Zone -->
                        <div class="border-2 border-dashed rounded-lg p-3 text-center transition-colors"
                            :class="dragOver ? 'border-gold bg-gold/5' : 'border-v2-card-border hover:border-gold/50'">
                            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.tif,.tiff"
                                class="hidden">

                            <template x-if="!selectedFile">
                                <div>
                                    <p class="text-xs text-v2-text-light">
                                        Drag files here or
                                        <button type="button" @click="$refs.fileInput.click()"
                                            class="text-gold hover:text-gold-hover font-medium">browse</button>
                                        — PDF, DOC, XLS, JPG, PNG, TIFF (max 10MB)
                                    </p>
                                </div>
                            </template>

                            <template x-if="selectedFile">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between bg-v2-bg rounded-lg p-3">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <div class="text-left">
                                                <p class="text-sm font-medium text-v2-text" x-text="selectedFile.name"></p>
                                                <p class="text-xs text-v2-text-light" x-text="(selectedFile.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="selectedFile = null"
                                            class="text-v2-text-light hover:text-red-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Document Type Selection -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-v2-text mb-1">Document Type</label>
                                            <select x-model="uploadForm.document_type"
                                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                                                <option value="hipaa_authorization">Wet-Signed HIPPA Release</option>
                                                <option value="signed_release">E-Signed HIPPA Release</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-v2-text mb-1">Notes (Optional)</label>
                                            <input type="text" x-model="uploadForm.notes" placeholder="Add notes..."
                                                class="w-full px-3 py-2 border border-v2-card-border rounded-lg text-sm">
                                        </div>
                                    </div>

                                    <!-- Provider Template Mode -->
                                    <div class="border-t border-v2-card-border pt-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="uploadForm.is_provider_template"
                                                class="rounded border-gray-300 text-gold focus:ring-gold">
                                            <span class="text-xs font-medium text-v2-text">
                                                This is a provider name template
                                                <span class="text-v2-text-light">(allows changing provider name for different providers)</span>
                                            </span>
                                        </label>

                                        <!-- Template Coordinate Picker (shown when template mode is enabled) -->
                                        <template x-if="uploadForm.is_provider_template">
                                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg space-y-3">

                                                <!-- Selection Mode Tabs -->
                                                <div class="flex items-center gap-1 bg-blue-100 rounded-lg p-0.5">
                                                    <button type="button" @click="selectionMode = 'provider_name'"
                                                        class="flex-1 px-3 py-1.5 rounded-md text-xs font-medium transition-colors"
                                                        :class="selectionMode === 'provider_name' ? 'bg-white text-blue-800 shadow-sm' : 'text-blue-600 hover:text-blue-800'">
                                                        Provider Name
                                                        <span x-show="hasCoordinates()" class="text-green-600 ml-1">&#10003;</span>
                                                    </button>
                                                    <button type="button" @click="selectionMode = 'date'; uploadForm.use_date_overlay = true"
                                                        class="flex-1 px-3 py-1.5 rounded-md text-xs font-medium transition-colors"
                                                        :class="selectionMode === 'date' ? 'bg-white text-emerald-800 shadow-sm' : 'text-blue-600 hover:text-blue-800'">
                                                        Date (Optional)
                                                        <span x-show="hasDateCoordinates()" class="text-green-600 ml-1">&#10003;</span>
                                                    </button>
                                                </div>

                                                <!-- Current mode info -->
                                                <div class="flex items-center justify-between">
                                                    <p class="text-xs font-medium" :class="selectionMode === 'date' ? 'text-emerald-700' : 'text-blue-800'"
                                                        x-text="selectionMode === 'date' ? 'Select Date Location' : 'Select Provider Name Location'"></p>
                                                    <div class="flex gap-2">
                                                        <template x-if="selectionMode === 'provider_name' && hasCoordinates()">
                                                            <button type="button" @click="resetProviderSelection()"
                                                                class="text-xs text-blue-600 hover:text-blue-800 underline">Reset</button>
                                                        </template>
                                                        <template x-if="selectionMode === 'date' && hasDateCoordinates()">
                                                            <button type="button" @click="resetDateSelection()"
                                                                class="text-xs text-emerald-600 hover:text-emerald-800 underline">Reset</button>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- PDF Preview Canvas (only for PDF files) -->
                                                <template x-if="isPdfFile()">
                                                    <div>
                                                        <!-- Loading indicator -->
                                                        <div x-show="pdfLoading && !pdfRendered" class="flex items-center justify-center py-8 bg-white rounded border border-blue-200">
                                                            <div class="spinner mr-2"></div>
                                                            <span class="text-xs text-v2-text-light">Loading PDF preview...</span>
                                                        </div>

                                                        <!-- Canvas container -->
                                                        <div class="relative rounded overflow-hidden border-2 transition-colors"
                                                            :class="selectionMode === 'date' ? 'border-emerald-300' : 'border-blue-300'"
                                                            style="cursor: crosshair;" x-ref="pdfCanvasContainer">
                                                            <canvas x-ref="pdfCanvas" class="block"></canvas>
                                                            <canvas x-ref="pdfOverlay" class="absolute top-0 left-0"
                                                                @mousedown="onCanvasMouseDown($event)"
                                                                @mousemove="onCanvasMouseMove($event)"
                                                                @mouseup="onCanvasMouseUp($event)"
                                                                @mouseleave="onCanvasMouseUp($event)"
                                                                @touchstart.prevent="onCanvasMouseDown($event)"
                                                                @touchmove.prevent="onCanvasMouseMove($event)"
                                                                @touchend.prevent="onCanvasMouseUp($event)"></canvas>
                                                        </div>

                                                        <!-- Instructions for current mode -->
                                                        <template x-if="pdfRendered">
                                                            <div class="mt-2 space-y-1">
                                                                <template x-if="selectionMode === 'provider_name' && !hasCoordinates()">
                                                                    <p class="text-xs text-blue-600">
                                                                        Drag on the PDF to select the Provider Name area.
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'provider_name' && hasCoordinates()">
                                                                    <p class="text-xs text-green-600">
                                                                        Provider Name area selected (blue). You can also select a Date area.
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'date' && !hasDateCoordinates()">
                                                                    <p class="text-xs text-emerald-600">
                                                                        Drag on the PDF to select the Date area (today's date will be inserted).
                                                                    </p>
                                                                </template>
                                                                <template x-if="selectionMode === 'date' && hasDateCoordinates()">
                                                                    <p class="text-xs text-green-600">
                                                                        Date area selected (green).
                                                                    </p>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                <!-- Non-PDF file: manual inputs -->
                                                <template x-if="!isPdfFile()">
                                                    <p class="text-xs text-blue-600 italic">
                                                        Upload a PDF file to use the visual coordinate picker.
                                                    </p>
                                                </template>

                                                <!-- Font Size -->
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center gap-2">
                                                        <label class="text-xs text-blue-700 whitespace-nowrap">Provider Font:</label>
                                                        <input type="number" x-model.number="uploadForm.provider_name_font_size" min="6" max="36"
                                                            class="w-16 px-2 py-1 border border-blue-300 rounded text-sm">
                                                    </div>
                                                    <template x-if="uploadForm.use_date_overlay">
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-xs text-emerald-700 whitespace-nowrap">Date Font:</label>
                                                            <input type="number" x-model.number="uploadForm.date_font_size" min="6" max="36"
                                                                class="w-16 px-2 py-1 border border-emerald-300 rounded text-sm">
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Fine-tune coordinates -->
                                                <template x-if="hasCoordinates()">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-medium text-blue-600">Provider Name (mm):</p>
                                                        <div class="flex gap-2 text-[10px]">
                                                            <label class="flex items-center gap-1 text-blue-500">X<input type="number" x-model.number="uploadForm.provider_name_x" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-blue-500">Y<input type="number" x-model.number="uploadForm.provider_name_y" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-blue-500">W<input type="number" x-model.number="uploadForm.provider_name_width" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-blue-500">H<input type="number" x-model.number="uploadForm.provider_name_height" step="0.5" class="w-14 px-1 py-0.5 border border-blue-200 rounded text-[10px] font-mono"></label>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="hasDateCoordinates()">
                                                    <div class="space-y-1">
                                                        <p class="text-[10px] font-medium text-emerald-600">Date (mm):</p>
                                                        <div class="flex gap-2 text-[10px]">
                                                            <label class="flex items-center gap-1 text-emerald-500">X<input type="number" x-model.number="uploadForm.date_x" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-emerald-500">Y<input type="number" x-model.number="uploadForm.date_y" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-emerald-500">W<input type="number" x-model.number="uploadForm.date_width" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                            <label class="flex items-center gap-1 text-emerald-500">H<input type="number" x-model.number="uploadForm.date_height" step="0.5" class="w-14 px-1 py-0.5 border border-emerald-200 rounded text-[10px] font-mono"></label>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Progress Bar -->
                                    <template x-if="uploading">
                                        <div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                <div class="bg-gold h-2 rounded-full transition-all duration-300"
                                                    :style="'width: ' + uploadProgress + '%'"></div>
                                            </div>
                                            <p class="text-xs text-center text-v2-text-light" x-text="uploadProgress + '%'"></p>
                                        </div>
                                    </template>

                                    <!-- Upload Button -->
                                    <button type="button" @click="uploadDocument()" :disabled="uploading"
                                        class="w-full px-4 py-2 bg-gold text-white rounded-lg text-sm hover:bg-gold-hover disabled:opacity-50">
                                        <span x-show="!uploading">Upload Document</span>
                                        <span x-show="uploading">Uploading...</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Documents List -->
                    <template x-if="loading">
                        <div class="flex justify-center py-8">
                            <div class="spinner"></div>
                        </div>
                    </template>

                    <template x-if="!loading && documents.length === 0">
                        <p class="text-sm text-v2-text-light text-center py-3">No documents uploaded yet</p>
                    </template>

                    <template x-if="!loading && documents.length > 0">
                        <div class="space-y-2">
                            <template x-for="doc in documents" :key="doc.id">
                                <div class="flex items-center justify-between p-3 border border-v2-card-border rounded-lg hover:bg-v2-bg transition-colors">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gold/10 rounded flex items-center justify-center">
                                                <span class="text-xs font-bold text-gold" x-text="getFileExtension(doc.original_file_name)"></span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-v2-text truncate" x-text="doc.original_file_name"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs px-2 py-0.5 rounded-full" :class="getDocumentTypeBadgeClass(doc.document_type)"
                                                    x-text="getDocumentTypeLabel(doc.document_type)"></span>
                                                <template x-if="doc.is_provider_template == 1">
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800" title="Provider name can be changed">
                                                        📋 Template
                                                    </span>
                                                </template>
                                                <span class="text-xs text-v2-text-light" x-text="doc.file_size_formatted"></span>
                                                <span class="text-xs text-v2-text-light" x-text="formatDate(doc.created_at)"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <!-- Generate for Provider button (only for templates) -->
                                        <template x-if="doc.is_provider_template == 1">
                                            <button @click="promptGenerateProviderVersion(doc)" title="Generate for Provider"
                                                class="p-2 text-v2-text-light hover:text-blue-600 hover:bg-blue-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </button>
                                        </template>
                                        <button @click="downloadDocument(doc.id)" title="Download"
                                            class="p-2 text-v2-text-light hover:text-gold hover:bg-gold/10 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button @click="deleteDocument(doc.id, doc.original_file_name)" title="Delete"
                                            class="p-2 text-v2-text-light hover:text-red-500 hover:bg-red-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
