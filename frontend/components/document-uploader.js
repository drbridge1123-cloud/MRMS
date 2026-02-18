/**
 * Document Uploader Component
 *
 * Reusable Alpine.js component for uploading documents to cases.
 * Supports drag-and-drop, progress tracking, and file validation.
 *
 * Usage:
 *   <div x-data="documentUploader(123)" x-init="init()">
 *       <!-- Use component properties and methods -->
 *   </div>
 */

/**
 * Create document uploader Alpine component
 * @param {number} caseId - Case ID to upload documents to
 * @param {number|null} caseProviderId - Optional provider ID
 * @returns {object} Alpine.js component
 */
function documentUploader(caseId, caseProviderId = null) {
    return {
        // State
        caseId: caseId,
        caseProviderId: caseProviderId,
        documents: [],
        loading: false,
        uploading: false,
        uploadProgress: 0,
        dragOver: false,
        selectedFile: null,
        uploadForm: {
            document_type: 'other',
            notes: '',
            is_provider_template: false,
            provider_name_x: null,
            provider_name_y: null,
            provider_name_width: null,
            provider_name_height: null,
            provider_name_font_size: 12,
            use_date_overlay: false,
            date_x: null,
            date_y: null,
            date_width: null,
            date_height: null,
            date_font_size: 12
        },
        showCoordinatePicker: false,
        pdfPreviewUrl: null,

        /**
         * Initialize component
         */
        async init() {
            await this.loadDocuments();

            // Watch for document type changes
            this.$watch('uploadForm.document_type', (value) => {
                this.onDocumentTypeChange(value);
            });
        },

        /**
         * Handle document type change - auto-fill coordinates for HIPAA Authorization
         * @param {string} documentType
         */
        onDocumentTypeChange(documentType) {
            if (documentType === 'hipaa_authorization') {
                // Wet-Signed HIPPA Release defaults
                this.uploadForm.is_provider_template = true;
                this.uploadForm.provider_name_x = 80;
                this.uploadForm.provider_name_y = 91;
                this.uploadForm.provider_name_width = 150;
                this.uploadForm.provider_name_height = 5;
                this.uploadForm.provider_name_font_size = 12;
            } else if (documentType === 'signed_release') {
                // E-Signed HIPPA Release defaults
                this.uploadForm.is_provider_template = true;
                this.uploadForm.provider_name_x = 80;
                this.uploadForm.provider_name_y = 100;
                this.uploadForm.provider_name_width = 150;
                this.uploadForm.provider_name_height = 5;
                this.uploadForm.provider_name_font_size = 12;
            } else {
                // Other - reset template settings
                this.uploadForm.is_provider_template = false;
                this.uploadForm.provider_name_x = null;
                this.uploadForm.provider_name_y = null;
                this.uploadForm.provider_name_width = null;
                this.uploadForm.provider_name_height = null;
                this.uploadForm.provider_name_font_size = 12;
            }
        },

        /**
         * Load documents for this case
         */
        async loadDocuments() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ case_id: this.caseId });
                if (this.caseProviderId) {
                    params.append('case_provider_id', this.caseProviderId);
                }

                const response = await api.get(`documents?${params.toString()}`);
                if (response.success) {
                    this.documents = response.data.documents || [];
                }
            } catch (error) {
                console.error('Failed to load documents:', error);
                showToast('Failed to load documents', 'error');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Handle file input change
         * @param {Event} event - File input change event
         */
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file;
            }
        },

        /**
         * Handle drag over event
         * @param {DragEvent} event
         */
        handleDragOver(event) {
            event.preventDefault();
            this.dragOver = true;
        },

        /**
         * Handle drag leave event
         */
        handleDragLeave() {
            this.dragOver = false;
        },

        /**
         * Handle file drop
         * @param {DragEvent} event
         */
        handleDrop(event) {
            event.preventDefault();
            this.dragOver = false;

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.selectedFile = files[0];
                // Trigger upload automatically after drop
                this.uploadDocument();
            }
        },

        /**
         * Upload selected document
         */
        async uploadDocument() {
            if (!this.selectedFile) {
                showToast('Please select a file', 'warning');
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024;
            if (this.selectedFile.size > maxSize) {
                showToast('File size exceeds 10MB limit', 'error');
                return;
            }

            // Validate file type
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'image/tiff'
            ];
            if (!allowedTypes.includes(this.selectedFile.type)) {
                showToast('Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TIFF', 'error');
                return;
            }

            // Validate template coordinates if provider template is enabled
            if (this.uploadForm.is_provider_template) {
                if (!this.uploadForm.provider_name_x || !this.uploadForm.provider_name_y ||
                    !this.uploadForm.provider_name_width || !this.uploadForm.provider_name_height) {
                    showToast('Please set provider name coordinates for the template', 'warning');
                    return;
                }
            }

            this.uploading = true;
            this.uploadProgress = 0;

            try {
                const formData = new FormData();
                formData.append('file', this.selectedFile);
                formData.append('case_id', this.caseId);
                formData.append('document_type', this.uploadForm.document_type);
                if (this.caseProviderId) {
                    formData.append('case_provider_id', this.caseProviderId);
                }
                if (this.uploadForm.notes) {
                    formData.append('notes', this.uploadForm.notes);
                }

                // Add provider template fields if enabled
                if (this.uploadForm.is_provider_template) {
                    formData.append('is_provider_template', '1');
                    if (this.uploadForm.provider_name_x) formData.append('provider_name_x', this.uploadForm.provider_name_x);
                    if (this.uploadForm.provider_name_y) formData.append('provider_name_y', this.uploadForm.provider_name_y);
                    if (this.uploadForm.provider_name_width) formData.append('provider_name_width', this.uploadForm.provider_name_width);
                    if (this.uploadForm.provider_name_height) formData.append('provider_name_height', this.uploadForm.provider_name_height);
                    formData.append('provider_name_font_size', this.uploadForm.provider_name_font_size);
                }

                // Add date overlay fields if enabled
                if (this.uploadForm.use_date_overlay) {
                    formData.append('use_date_overlay', '1');
                    if (this.uploadForm.date_x) formData.append('date_x', this.uploadForm.date_x);
                    if (this.uploadForm.date_y) formData.append('date_y', this.uploadForm.date_y);
                    if (this.uploadForm.date_width) formData.append('date_width', this.uploadForm.date_width);
                    if (this.uploadForm.date_height) formData.append('date_height', this.uploadForm.date_height);
                    formData.append('date_font_size', this.uploadForm.date_font_size);
                }

                // Upload with progress tracking
                const response = await api.upload('documents/upload', formData, (progress) => {
                    this.uploadProgress = progress;
                });

                if (response.success) {
                    showToast('Document uploaded successfully', 'success');

                    // Reset form
                    this.selectedFile = null;
                    this.uploadForm.document_type = 'other';
                    this.uploadForm.notes = '';
                    this.uploadProgress = 0;

                    // Reload documents
                    await this.loadDocuments();

                    // Dispatch event for parent components
                    this.$dispatch('document-uploaded', {
                        document: response.data
                    });
                }
            } catch (error) {
                console.error('Upload failed:', error);
                showToast(error.data?.message || 'Upload failed', 'error');
            } finally {
                this.uploading = false;
            }
        },

        /**
         * Download document
         * @param {number} documentId
         */
        async downloadDocument(documentId) {
            try {
                // Open download in new window
                const url = `/MRMS/backend/api/documents/${documentId}/download`;
                window.open(url, '_blank');
            } catch (error) {
                console.error('Download failed:', error);
                showToast('Download failed', 'error');
            }
        },

        /**
         * Delete document
         * @param {number} documentId
         * @param {string} fileName
         */
        async deleteDocument(documentId, fileName) {
            if (!confirm(`Are you sure you want to delete "${fileName}"?`)) {
                return;
            }

            try {
                const response = await api.delete(`documents/${documentId}`);
                if (response.success) {
                    showToast('Document deleted', 'success');
                    await this.loadDocuments();

                    // Dispatch event for parent components
                    this.$dispatch('document-deleted', {
                        documentId: documentId
                    });
                }
            } catch (error) {
                console.error('Delete failed:', error);
                showToast(error.data?.message || 'Delete failed', 'error');
            }
        },

        /**
         * Get document type label
         * @param {string} type
         * @returns {string}
         */
        getDocumentTypeLabel(type) {
            const labels = {
                'hipaa_authorization': 'Wet-Signed HIPPA Release',
                'signed_release': 'E-Signed HIPPA Release',
                'other': 'Other'
            };
            return labels[type] || type;
        },

        /**
         * Get document type badge class
         * @param {string} type
         * @returns {string}
         */
        getDocumentTypeBadgeClass(type) {
            const classes = {
                'hipaa_authorization': 'bg-blue-100 text-blue-800',
                'signed_release': 'bg-green-100 text-green-800',
                'other': 'bg-gray-100 text-gray-800'
            };
            return classes[type] || classes['other'];
        },

        /**
         * Get file extension from filename
         * @param {string} filename
         * @returns {string}
         */
        getFileExtension(filename) {
            const parts = filename.split('.');
            return parts.length > 1 ? parts.pop().toUpperCase() : '';
        },

        /**
         * Format date
         * @param {string} dateStr
         * @returns {string}
         */
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        },

        /**
         * Generate provider-specific version of a template document
         * @param {number} documentId - Template document ID
         * @param {string} providerName - Provider name to insert
         */
        async generateProviderVersion(documentId, providerName) {
            if (!providerName || providerName.trim() === '') {
                showToast('Please enter provider name', 'warning');
                return;
            }

            try {
                const response = await api.post('documents/generate-provider-version', {
                    document_id: documentId,
                    provider_name: providerName,
                    case_id: this.caseId
                });

                if (response.success) {
                    showToast('Provider-specific document generated', 'success');
                    await this.loadDocuments();

                    // Dispatch event for parent components
                    this.$dispatch('document-generated', {
                        document: response.data
                    });
                }
            } catch (error) {
                console.error('Generation failed:', error);
                showToast(error.data?.message || 'Failed to generate document', 'error');
            }
        },

        /**
         * Prompt user to generate provider version
         * @param {object} doc - Template document
         */
        promptGenerateProviderVersion(doc) {
            // Check if coordinates are configured
            if (!doc.provider_name_x || !doc.provider_name_y || !doc.provider_name_width || !doc.provider_name_height) {
                showToast('Template coordinates not configured. Please re-upload with coordinates set.', 'warning');
                return;
            }

            const providerName = prompt(`Enter provider name for ${doc.original_file_name}:`);
            if (providerName) {
                this.generateProviderVersion(doc.id, providerName.trim());
            }
        }
    };
}
