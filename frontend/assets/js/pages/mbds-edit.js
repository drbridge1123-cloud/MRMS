function mbdsEditPage() {
    return {
        caseId: getQueryParam('case_id'),
        caseData: null,
        report: null,
        lines: [],
        settings: {
            pip1_name: '', pip2_name: '', health1_name: '', health2_name: '',
            has_wage_loss: false, has_essential_service: false, has_health_subrogation: false,
            notes: ''
        },
        totals: { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 },
        loading: true,
        saving: false,
        _saveTimers: {},

        async init() {
            if (!this.caseId) {
                window.location.href = '/MRMS/frontend/pages/cases/index.php';
                return;
            }
            await this.loadReport();
            this.loading = false;
        },

        async loadReport() {
            try {
                const res = await api.get('mbds/' + this.caseId);
                this.report = res.data;
                this.caseData = {
                    case_number: res.data.case_number,
                    client_name: res.data.client_name,
                    doi: res.data.doi,
                    case_status: res.data.case_status
                };
                this.lines = res.data.lines || [];
                this.settings = {
                    pip1_name: res.data.pip1_name || '',
                    pip2_name: res.data.pip2_name || '',
                    health1_name: res.data.health1_name || '',
                    health2_name: res.data.health2_name || '',
                    has_wage_loss: !!res.data.has_wage_loss,
                    has_essential_service: !!res.data.has_essential_service,
                    has_health_subrogation: !!res.data.has_health_subrogation,
                    notes: res.data.notes || ''
                };
                this.recalcTotals();
            } catch (e) {
                // Report doesn't exist yet — create it
                if (e.response?.status === 404) {
                    await this.createReport();
                } else {
                    showToast('Failed to load report', 'error');
                }
            }
        },

        async createReport() {
            try {
                // First get case info
                const caseRes = await api.get('cases/' + this.caseId);
                this.caseData = {
                    case_number: caseRes.data.case_number,
                    client_name: caseRes.data.client_name,
                    doi: caseRes.data.doi,
                    case_status: caseRes.data.status
                };

                const res = await api.post('mbds/' + this.caseId);
                showToast('MBDS report created');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed to create report', 'error');
            }
        },

        async saveSettings() {
            if (!this.report) return;
            this.saving = true;
            try {
                await api.put('mbds/' + this.report.id, this.settings);
                // Reload to get updated lines (toggle may add/remove lines)
                await this.loadReport();
            } catch (e) {
                showToast('Failed to save settings', 'error');
            }
            this.saving = false;
        },

        formatDateInput(e, line) {
            const input = e.target;
            const digits = input.value.replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < digits.length && i < 12; i++) {
                if (i === 2 || i === 4 || i === 8 || i === 10) formatted += '/';
                if (i === 6) formatted += '-';
                formatted += digits[i];
            }
            line.treatment_dates = formatted;
            input.value = formatted;
        },

        calcBalance(line) {
            return Math.round(((line.charges || 0) - (line.pip1_amount || 0) - (line.pip2_amount || 0)
                - (line.health1_amount || 0) - (line.health2_amount || 0)
                - (line.discount || 0) - (line.office_paid || 0) - (line.client_paid || 0)) * 100) / 100;
        },

        recalcTotals() {
            const t = { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 };
            for (const l of this.lines) {
                t.charges += l.charges || 0;
                t.pip1 += l.pip1_amount || 0;
                t.pip2 += l.pip2_amount || 0;
                t.health1 += l.health1_amount || 0;
                t.health2 += l.health2_amount || 0;
                t.discount += l.discount || 0;
                t.officePaid += l.office_paid || 0;
                t.clientPaid += l.client_paid || 0;
            }
            t.balance = Math.round((t.charges - t.pip1 - t.pip2 - t.health1 - t.health2 - t.discount - t.officePaid - t.clientPaid) * 100) / 100;
            // Round all
            for (const k in t) t[k] = Math.round(t[k] * 100) / 100;
            this.totals = t;
        },

        debounceSaveLine(line) {
            this.recalcTotals();
            clearTimeout(this._saveTimers[line.id]);
            this._saveTimers[line.id] = setTimeout(() => this.saveLine(line), 500);
        },

        async saveLine(line) {
            this.saving = true;
            try {
                await api.put('mbds-lines/' + line.id, {
                    charges: line.charges || 0,
                    pip1_amount: line.pip1_amount || 0,
                    pip2_amount: line.pip2_amount || 0,
                    health1_amount: line.health1_amount || 0,
                    health2_amount: line.health2_amount || 0,
                    discount: line.discount || 0,
                    office_paid: line.office_paid || 0,
                    client_paid: line.client_paid || 0,
                    treatment_dates: line.treatment_dates || '',
                    visits: line.visits || '',
                    note: line.note || ''
                });
            } catch (e) {
                showToast('Failed to save', 'error');
            }
            this.saving = false;
        },

        async addLine(type) {
            try {
                const name = type === 'rx' ? 'RX' : prompt('Provider/line name:');
                if (!name) return;
                await api.post('mbds/' + this.report.id + '/lines', {
                    line_type: type,
                    provider_name: name
                });
                await this.loadReport();
            } catch (e) {
                showToast('Failed to add line', 'error');
            }
        },

        async deleteLine(line) {
            if (!confirm('Delete "' + line.provider_name + '"?')) return;
            try {
                await api.delete('mbds-lines/' + line.id);
                await this.loadReport();
                showToast('Line deleted');
            } catch (e) {
                showToast('Failed to delete', 'error');
            }
        },

        async markComplete() {
            if (!confirm('Mark this MBDS report as complete? This will move the case to Completed status.')) return;
            try {
                await api.post('mbds/' + this.report.id + '/complete');
                showToast('Report marked as completed');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        async approveReport() {
            if (!confirm('Approve this report and close the case?')) return;
            try {
                await api.post('mbds/' + this.report.id + '/approve');
                showToast('Report approved — case closed');
                await this.loadReport();
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        },

        async reopenDraft() {
            if (!confirm('Reopen this report as draft?')) return;
            try {
                await api.put('mbds/' + this.report.id, { status: 'draft' });
                await this.loadReport();
                showToast('Report reopened as draft');
            } catch (e) {
                showToast(e.data?.message || 'Failed', 'error');
            }
        }
    };
}
