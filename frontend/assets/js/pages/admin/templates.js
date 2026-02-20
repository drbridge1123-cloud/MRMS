function templatesPage() {
    return {
        templates: [],
        loading: false,
        filterType: '',
        activeOnly: true,

        showModal: false,
        editingTemplate: null,
        form: {
            name: '',
            description: '',
            template_type: 'medical_records',
            subject_template: '',
            body_template: '',
            is_default: false,
            change_notes: ''
        },

        showPreviewModal: false,
        previewHtml: '',

        showVersionsModal: false,
        versions: [],

        async init() {
            await this.loadTemplates();
        },

        async loadTemplates() {
            this.loading = true;
            try {
                let params = [];
                if (this.filterType) params.push(`type=${this.filterType}`);
                if (this.activeOnly) params.push('active_only=1');

                const query = params.length > 0 ? '?' + params.join('&') : '';
                const res = await api.get('templates' + query);
                this.templates = res.data || [];
            } catch (e) {
                showToast('Failed to load templates: ' + (e.response?.data?.error || e.message), 'error');
            }
            this.loading = false;
        },

        openCreateModal() {
            this.editingTemplate = null;
            this.form = {
                name: '',
                description: '',
                template_type: 'medical_records',
                subject_template: '',
                body_template: '',
                is_default: false,
                change_notes: ''
            };
            this.showModal = true;
        },

        editTemplate(template) {
            this.editingTemplate = template;
            this.form = {
                name: template.name,
                description: template.description || '',
                template_type: template.template_type,
                subject_template: template.subject_template || '',
                body_template: template.body_template,
                is_default: template.is_default == 1,
                change_notes: ''
            };
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.editingTemplate = null;
        },

        async saveTemplate() {
            if (!this.form.name || !this.form.body_template) {
                showToast('Name and body template are required', 'error');
                return;
            }

            try {
                if (this.editingTemplate) {
                    await api.put(`templates/${this.editingTemplate.id}`, this.form);
                    showToast('Template updated successfully', 'success');
                } else {
                    await api.post('templates', this.form);
                    showToast('Template created successfully', 'success');
                }
                this.closeModal();
                await this.loadTemplates();
            } catch (e) {
                showToast('Failed to save template: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async deleteTemplate(template) {
            if (!confirm(`Delete template "${template.name}"?`)) return;

            try {
                await api.delete(`templates/${template.id}`);
                showToast('Template deleted successfully', 'success');
                await this.loadTemplates();
            } catch (e) {
                showToast('Failed to delete template: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        async previewTemplate(template) {
            try {
                const fullTemplate = await api.get(`templates/${template.id}`);
                const res = await api.post('templates/preview', {
                    body_template: fullTemplate.data.body_template
                });
                this.previewHtml = res.data.html;
                this.showPreviewModal = true;
            } catch (e) {
                showToast('Failed to preview: ' + (e.data?.message || e.message), 'error');
            }
        },

        async previewCurrent() {
            if (!this.form.body_template) {
                showToast('Body template is required for preview', 'error');
                return;
            }

            try {
                const res = await api.post('templates/preview', {
                    body_template: this.form.body_template
                });
                this.previewHtml = res.data.html;
                this.showPreviewModal = true;
            } catch (e) {
                showToast('Failed to preview: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closePreviewModal() {
            this.showPreviewModal = false;
        },

        async viewVersions(template) {
            try {
                const res = await api.get(`templates/${template.id}/versions`);
                this.versions = res.data.versions || [];
                this.showVersionsModal = true;
            } catch (e) {
                showToast('Failed to load versions: ' + (e.response?.data?.error || e.message), 'error');
            }
        },

        closeVersionsModal() {
            this.showVersionsModal = false;
            this.versions = [];
        }
    };
}
