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
