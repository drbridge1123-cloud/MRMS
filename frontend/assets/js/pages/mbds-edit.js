function mbdsEditPage() {
    return {
        caseId: getQueryParam('case_id'),
        caseData: null,
        report: null,
        lines: [],
        settings: {
            pip1_name: '', pip2_name: '', health1_name: '', health2_name: '', health3_name: '',
            has_wage_loss: false, has_essential_service: false, has_health_subrogation: false, has_health_subrogation2: false,
            notes: ''
        },
        totals: { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, health3: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 },
        loading: true,
        saving: false,
        _saveTimers: {},
        _editingField: null,
        expandedNote: null,
        notePopoverPos: { top: 0, right: 0 },

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
                    health3_name: res.data.health3_name || '',
                    has_wage_loss: !!res.data.has_wage_loss,
                    has_essential_service: !!res.data.has_essential_service,
                    has_health_subrogation: !!res.data.has_health_subrogation,
                    has_health_subrogation2: !!res.data.has_health_subrogation2,
                    notes: res.data.notes || ''
                };
                this.recalcTotals();
            } catch (e) {
                if (e.response?.status === 404) {
                    await this.createReport();
                } else {
                    showToast('Failed to load report', 'error');
                }
            }
        },

        async createReport() {
            try {
                const caseRes = await api.get('cases/' + this.caseId);
                this.caseData = {
                    case_number: caseRes.data.case_number,
                    client_name: caseRes.data.client_name,
                    doi: caseRes.data.doi,
                    case_status: caseRes.data.status
                };
                await api.post('mbds/' + this.caseId);
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
                await this.loadReport();
            } catch (e) {
                showToast('Failed to save settings', 'error');
            }
            this.saving = false;
        },

        // ==========================================
        // Currency formatting helpers
        // ==========================================

        formatCurrency(v) {
            const num = Number(v) || 0;
            if (num === 0) return '$0';
            const hasDecimals = num !== Math.floor(num);
            return '$' + num.toLocaleString('en-US', {
                minimumFractionDigits: hasDecimals ? 2 : 0,
                maximumFractionDigits: 2
            });
        },

        parseCurrency(s) {
            return parseFloat(String(s).replace(/[$,]/g, '')) || 0;
        },

        cellVal(lineId, field, value) {
            if (this._editingField === lineId + '_' + field) return '';
            return this.formatCurrency(value);
        },

        startCellEdit(el, line, field) {
            this._editingField = line.id + '_' + field;
            this.$nextTick(() => {
                el.value = line[field] || '';
                el.select();
            });
        },

        endCellEdit(el, line, field) {
            line[field] = this.parseCurrency(el.value);
            this._editingField = null;
            this.debounceSaveLine(line);
        },

        openNote(e, rowId) {
            if (this.expandedNote === rowId) { this.expandedNote = null; return; }
            const rect = e.currentTarget.getBoundingClientRect();
            const popW = 320, popH = 200;
            let top = rect.top - popH - 4;
            if (top < 8) top = rect.bottom + 4;
            let right = window.innerWidth - rect.right;
            if (right < 0) right = 8;
            this.notePopoverPos = { top: top + 'px', right: right + 'px' };
            this.expandedNote = rowId;
        },

        // ==========================================
        // Category grouping
        // ==========================================

        getCategoryForLine(line) {
            if (line.line_type === 'health_subrogation') return 'health_subrogation';
            if (line.line_type === 'health_subrogation2') return 'health_subrogation2';
            if (line.line_type === 'wage_loss') return 'wage_loss';
            if (line.line_type === 'essential_service') return 'essential_service';
            if (line.line_type === 'rx') return 'rx';
            // Provider lines — group by provider_type
            if (['hospital', 'er', 'surgery_center', 'physician'].includes(line.provider_type)) return 'hospital';
            return 'treatment';
        },

        getCategoryLabel(cat) {
            const labels = {
                'health_subrogation': 'HEALTH SUBROGATION #1',
                'health_subrogation2': 'HEALTH SUBROGATION #2',
                'wage_loss': 'WAGE LOSS',
                'essential_service': 'ESSENTIAL SERVICE',
                'treatment': 'TREATMENT PROVIDERS',
                'hospital': 'HOSPITAL / PHYSICIANS',
                'rx': 'RX'
            };
            return labels[cat] || cat.toUpperCase();
        },

        get displayRows() {
            const categoryOrder = ['health_subrogation', 'health_subrogation2', 'wage_loss', 'essential_service', 'treatment', 'hospital', 'rx'];
            const grouped = {};

            for (const line of this.lines) {
                const cat = this.getCategoryForLine(line);
                if (!grouped[cat]) grouped[cat] = [];
                grouped[cat].push(line);
            }

            const rows = [];
            for (const cat of categoryOrder) {
                if (!grouped[cat] || grouped[cat].length === 0) continue;
                rows.push({ _type: 'header', _key: 'hdr_' + cat, label: this.getCategoryLabel(cat), _lineRef: {} });
                for (const line of grouped[cat]) {
                    rows.push({ _type: 'line', _key: 'ln_' + line.id, ...line, _lineRef: line });
                }
            }
            return rows;
        },

        get insuranceColspan() {
            let c = 0;
            if (this.settings.pip1_name) c++;
            if (this.settings.pip2_name) c++;
            if (this.settings.health1_name) c++;
            if (this.settings.health2_name) c++;
            if (this.settings.health3_name) c++;
            return c;
        },

        get totalCols() {
            // Provider + Charges + insurance cols + Discount + Office Paid + Client Paid + Balance + Dates + Visits + Note + Delete
            return 2 + this.insuranceColspan + 7 + (this.report?.status === 'draft' ? 1 : 0);
        },

        // ==========================================
        // Calculations
        // ==========================================

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
                - (line.health1_amount || 0) - (line.health2_amount || 0) - (line.health3_amount || 0)
                - (line.discount || 0) - (line.office_paid || 0) - (line.client_paid || 0)) * 100) / 100;
        },

        recalcTotals() {
            const t = { charges: 0, pip1: 0, pip2: 0, health1: 0, health2: 0, health3: 0, discount: 0, officePaid: 0, clientPaid: 0, balance: 0 };
            for (const l of this.lines) {
                t.charges += l.charges || 0;
                t.pip1 += l.pip1_amount || 0;
                t.pip2 += l.pip2_amount || 0;
                t.health1 += l.health1_amount || 0;
                t.health2 += l.health2_amount || 0;
                t.health3 += l.health3_amount || 0;
                t.discount += l.discount || 0;
                t.officePaid += l.office_paid || 0;
                t.clientPaid += l.client_paid || 0;
            }
            t.balance = Math.round((t.charges - t.pip1 - t.pip2 - t.health1 - t.health2 - t.health3 - t.discount - t.officePaid - t.clientPaid) * 100) / 100;
            for (const k in t) t[k] = Math.round(t[k] * 100) / 100;
            this.totals = t;
        },

        // ==========================================
        // CRUD
        // ==========================================

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
                    health3_amount: line.health3_amount || 0,
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
        },

        getStatusLabel(status) {
            const labels = { new: 'NEW', requesting: 'REQUESTING', follow_up: 'FOLLOW UP', in_review: 'IN REVIEW', completed: 'COMPLETED', on_hold: 'ON HOLD', closed: 'CLOSED' };
            return labels[status] || status?.toUpperCase() || '';
        },

        formatDate(d) {
            if (!d) return '';
            return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        printMbds() {
            const s = this.settings;
            const cd = this.caseData;

            // Build dynamic insurance columns
            const insCols = [];
            if (s.pip1_name) insCols.push({ key: 'pip1_amount', label: 'PIP #1 (' + s.pip1_name + ')' });
            if (s.pip2_name) insCols.push({ key: 'pip2_amount', label: 'PIP #2 (' + s.pip2_name + ')' });
            if (s.health1_name) insCols.push({ key: 'health1_amount', label: 'Health #1 (' + s.health1_name + ')' });
            if (s.health2_name) insCols.push({ key: 'health2_amount', label: 'Health #2 (' + s.health2_name + ')' });
            if (s.health3_name) insCols.push({ key: 'health3_amount', label: 'Health #3 (' + s.health3_name + ')' });

            // Header row
            let thead = '<th>Provider</th><th class="r">Charges</th>';
            insCols.forEach(c => { thead += '<th class="r">' + c.label + '</th>'; });
            thead += '<th class="r">Discount</th><th class="r">Office Paid</th><th class="r">Client Paid</th><th class="r">Balance</th><th>Dates</th><th>Visits</th>';

            // Data rows
            let tbody = '';
            for (const row of this.displayRows) {
                if (row._type === 'header') {
                    const colCount = 7 + insCols.length + 2;
                    tbody += '<tr class="cat"><td colspan="' + colCount + '">' + row.label + '</td></tr>';
                } else {
                    const bal = this.calcBalance(row);
                    const balClass = bal < 0 ? 'neg' : (bal > 0 ? 'pos' : 'zero');
                    tbody += '<tr>';
                    tbody += '<td class="prov">' + (row.provider_name || '') + '</td>';
                    tbody += '<td class="r">' + this.formatCurrency(row.charges) + '</td>';
                    insCols.forEach(c => { tbody += '<td class="r">' + this.formatCurrency(row[c.key]) + '</td>'; });
                    tbody += '<td class="r">' + this.formatCurrency(row.discount) + '</td>';
                    tbody += '<td class="r">' + this.formatCurrency(row.office_paid) + '</td>';
                    tbody += '<td class="r">' + this.formatCurrency(row.client_paid) + '</td>';
                    tbody += '<td class="r ' + balClass + '">' + this.formatCurrency(bal) + '</td>';
                    tbody += '<td class="dates">' + (row.treatment_dates || '') + '</td>';
                    tbody += '<td class="ctr">' + (row.visits || '') + '</td>';
                    tbody += '</tr>';
                }
            }

            // Total row
            const t = this.totals;
            const totKeys = { pip1: 'pip1_amount', pip2: 'pip2_amount', health1: 'health1_amount', health2: 'health2_amount', health3: 'health3_amount' };
            tbody += '<tr class="total"><td>TOTAL</td>';
            tbody += '<td class="r">' + this.formatCurrency(t.charges) + '</td>';
            insCols.forEach(c => {
                const tKey = Object.keys(totKeys).find(k => totKeys[k] === c.key);
                tbody += '<td class="r">' + this.formatCurrency(t[tKey] || 0) + '</td>';
            });
            tbody += '<td class="r">' + this.formatCurrency(t.discount) + '</td>';
            tbody += '<td class="r">' + this.formatCurrency(t.officePaid) + '</td>';
            tbody += '<td class="r">' + this.formatCurrency(t.clientPaid) + '</td>';
            const balClass = t.balance < 0 ? 'neg' : (t.balance > 0 ? 'pos' : 'zero');
            tbody += '<td class="r ' + balClass + '">' + this.formatCurrency(t.balance) + '</td>';
            tbody += '<td colspan="2"></td></tr>';

            // Notes
            const notes = s.notes ? '<div class="notes"><strong>Notes:</strong> ' + s.notes + '</div>' : '';

            const html = `<!DOCTYPE html><html><head><title>MBDS - ${cd.case_number}</title>
<style>
    @page { size: landscape; margin: 15mm; }
    body { font-family: Arial, sans-serif; font-size: 11px; }
    h2 { margin: 0 0 2px; font-size: 16px; }
    .sub { color: #666; margin-bottom: 12px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f0f0f0; padding: 5px 6px; text-align: left; font-size: 10px; text-transform: uppercase; border-bottom: 2px solid #333; white-space: nowrap; }
    th.r, td.r { text-align: right; }
    td { padding: 4px 6px; border-bottom: 1px solid #ddd; }
    td.prov { font-weight: 600; }
    td.dates { font-size: 10px; white-space: nowrap; }
    td.ctr { text-align: center; }
    tr.cat td { background: #f7f7f7; font-weight: bold; font-size: 10px; letter-spacing: 0.5px; padding: 6px; border-top: 2px solid #ccc; }
    tr.total td { font-weight: bold; border-top: 2px solid #333; background: #f5f5f5; }
    .neg { color: #dc2626; }
    .pos { color: #d97706; }
    .zero { color: #16a34a; }
    .notes { margin-top: 12px; font-size: 12px; }
</style>
</head><body>
    <h2>Medical Billing Data Summary</h2>
    <div class="sub">${cd.client_name} &mdash; ${cd.case_number}${cd.doi ? ' | DOI: ' + this.formatDate(cd.doi) : ''}</div>
    <table><thead><tr>${thead}</tr></thead><tbody>${tbody}</tbody></table>
    ${notes}
</body></html>`;

            const w = window.open('', '_blank');
            w.document.write(html);
            w.document.close();
            w.onload = () => { w.print(); };
        }
    };
}
