<?php
require_once __DIR__ . '/../../../backend/helpers/auth.php';
requireAdmin();
$pageTitle = 'Data Management';
$currentPage = 'admin-data';
ob_start();
?>

<div x-data="dataManagementPage()">

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Data Management</h1>
        <p class="text-gray-500 mt-1">Export, import and manage your data</p>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-gray-200 mb-6">
        <button @click="activeTab = 'export'"
                class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === 'export' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export
        </button>
        <button @click="activeTab = 'import'"
                class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                :class="activeTab === 'import' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m4-8l-4-4m0 0L13 8m4-4v12"/>
            </svg>
            Import
        </button>
    </div>

    <!-- Export Tab -->
    <div x-show="activeTab === 'export'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800">Export Data</h2>
            <p class="text-sm text-gray-500 mt-1 mb-6">Download your data as CSV files</p>

            <!-- Export Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

                <!-- Cases Card -->
                <div @click="toggleExport('cases')"
                     class="relative border-2 rounded-xl p-6 cursor-pointer transition-all hover:shadow-md text-center"
                     :class="selectedExports.includes('cases') ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300'">
                    <!-- Check icon -->
                    <div x-show="selectedExports.includes('cases')" class="absolute top-3 right-3">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Cases</div>
                    <div class="text-xs text-gray-500 mt-1">All case information</div>
                </div>

                <!-- Providers Card -->
                <div @click="toggleExport('providers')"
                     class="relative border-2 rounded-xl p-6 cursor-pointer transition-all hover:shadow-md text-center"
                     :class="selectedExports.includes('providers') ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300'">
                    <div x-show="selectedExports.includes('providers')" class="absolute top-3 right-3">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Providers</div>
                    <div class="text-xs text-gray-500 mt-1">Healthcare providers</div>
                </div>

            </div>

            <!-- Download Button -->
            <button @click="exportData()"
                    :disabled="selectedExports.length === 0 || exporting"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span x-text="exporting ? 'Downloading...' : 'Download CSV Files'"></span>
            </button>
        </div>
    </div>

    <!-- Import Tab -->
    <div x-show="activeTab === 'import'" x-cloak>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800">Import Data</h2>
            <p class="text-sm text-gray-500 mt-1 mb-6">Upload CSV files to import data</p>

            <!-- Data Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Select data type</label>
                <div class="flex gap-3">
                    <button @click="importType = 'cases'"
                            class="flex items-center gap-2 px-4 py-2.5 border-2 rounded-lg transition-all text-sm font-medium"
                            :class="importType === 'cases' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Cases
                    </button>
                    <button @click="importType = 'providers'"
                            class="flex items-center gap-2 px-4 py-2.5 border-2 rounded-lg transition-all text-sm font-medium"
                            :class="importType === 'providers' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Providers
                    </button>
                </div>
            </div>

            <!-- Template Download -->
            <div class="mb-6 p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Need a template?</span> Download a blank CSV with the correct column headers.
                </div>
                <button @click="downloadTemplate()"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Template
                </button>
            </div>

            <!-- File Upload Area -->
            <div class="mb-6">
                <div @click="$refs.fileInput.click()"
                     @dragover.prevent="dragover = true"
                     @dragleave.prevent="dragover = false"
                     @drop.prevent="handleDrop($event)"
                     class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all"
                     :class="dragover ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'">
                    <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept=".csv" class="hidden">

                    <template x-if="!importFile">
                        <div>
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-gray-600 font-medium">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-400 mt-1">CSV files only (max 5MB)</p>
                        </div>
                    </template>

                    <template x-if="importFile">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-left">
                                <p class="text-sm font-medium text-gray-800" x-text="importFile.name"></p>
                                <p class="text-xs text-gray-500" x-text="formatFileSize(importFile.size)"></p>
                            </div>
                            <button @click.stop="importFile = null; $refs.fileInput.value = ''" class="ml-2 text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Upload Button -->
            <button @click="importData()"
                    :disabled="!importFile || importing"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m4-8l-4-4m0 0L13 8m4-4v12"/>
                </svg>
                <span x-text="importing ? 'Importing...' : 'Upload & Import'"></span>
            </button>

            <!-- Import Results -->
            <template x-if="importResult">
                <div class="mt-6">
                    <!-- Success message -->
                    <template x-if="importResult.imported > 0 && importResult.skipped === 0">
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm text-green-700 font-medium" x-text="importResult.imported + ' records imported successfully'"></span>
                        </div>
                    </template>

                    <!-- Partial success -->
                    <template x-if="importResult.imported > 0 && importResult.skipped > 0">
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                            </svg>
                            <span class="text-sm text-amber-700 font-medium" x-text="importResult.imported + ' imported, ' + importResult.skipped + ' skipped'"></span>
                        </div>
                    </template>

                    <!-- All skipped -->
                    <template x-if="importResult.imported === 0 && importResult.skipped > 0">
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-sm text-red-700 font-medium" x-text="'All ' + importResult.skipped + ' rows were skipped due to errors'"></span>
                        </div>
                    </template>

                    <!-- Error details table -->
                    <template x-if="importResult.errors && importResult.errors.length > 0">
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Error Details</h4>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 w-20">Row</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="err in importResult.errors" :key="err.row">
                                            <tr class="border-t border-gray-100">
                                                <td class="px-4 py-2 text-gray-600 font-mono" x-text="'#' + err.row"></td>
                                                <td class="px-4 py-2 text-red-600" x-text="err.message"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function dataManagementPage() {
    return {
        activeTab: 'export',
        // Export
        selectedExports: [],
        exporting: false,
        // Import
        importType: 'cases',
        importFile: null,
        importing: false,
        importResult: null,
        dragover: false,

        toggleExport(type) {
            const idx = this.selectedExports.indexOf(type);
            if (idx >= 0) {
                this.selectedExports.splice(idx, 1);
            } else {
                this.selectedExports.push(type);
            }
        },

        async exportData() {
            this.exporting = true;
            for (const type of this.selectedExports) {
                const url = `/MRMS/backend/api/${type}/export`;
                window.open(url, '_blank');
                // Small delay between downloads
                await new Promise(r => setTimeout(r, 500));
            }
            this.exporting = false;
            showToast('Export started', 'success');
        },

        downloadTemplate() {
            const url = `/MRMS/backend/api/${this.importType}/export?template=1`;
            window.open(url, '_blank');
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (!file.name.endsWith('.csv')) {
                    showToast('Please select a CSV file', 'error');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showToast('File size must be under 5MB', 'error');
                    return;
                }
                this.importFile = file;
                this.importResult = null;
            }
        },

        handleDrop(event) {
            this.dragover = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                if (!file.name.endsWith('.csv')) {
                    showToast('Please drop a CSV file', 'error');
                    return;
                }
                this.importFile = file;
                this.importResult = null;
            }
        },

        async importData() {
            if (!this.importFile) return;
            this.importing = true;
            this.importResult = null;

            const formData = new FormData();
            formData.append('file', this.importFile);

            try {
                const res = await api.upload(`${this.importType}/import`, formData);
                this.importResult = res.data;
                if (res.data.imported > 0) {
                    showToast(res.message, 'success');
                }
            } catch (e) {
                showToast(e.data?.message || 'Import failed', 'error');
            }

            this.importing = false;
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
