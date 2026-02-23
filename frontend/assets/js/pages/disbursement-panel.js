function disbursementPanel(caseId) {
    return {
        open: false,
        loading: true,
        settlementData: null,
        pipInfo: null,

        settings: {
            settlement_amount: 0,
            attorney_fee_percent: 1/3,
            coverage_3rd_party: false,
            coverage_um: false,
            coverage_uim: false,
            policy_limit: false,
            um_uim_limit: false,
            pip_subrogation_amount: 0,
            pip_insurance_company: '',
            settlement_method: null,
        },

        bestOffers: { '3rd_party': 0, um: 0, uim: 0, dv: 0 },
        medicalBills: { total_charges: 0, total_balance: 0, providers: [] },
        medicalBalance: 0,
        healthSubrogation: 0,
        expenses: { reimbursable: 0, litigation: 0, total: 0 },

        // Calculated results
        calculated: null,
        mahlerCalc: { gross: 0, fee: 0, costs: 0, afe: 0, attorneyPercent: 0, attorneySharePip: 0, carrierShare: 0, medicalBalance: 0, subrogation: 0, clientNet: 0 },
        hammCalc: { gross: 0, fee: 0, costs: 0, afe: 0, attorneyPercent: 0, hammFee: 0, medicalBalance: 0, subrogation: 0, clientNet: 0 },
        disbursementLines: [],

        _saveTimer: null,

        async init() {
            await this.loadSettlementData();
            this.loading = false;
        },

        async loadSettlementData() {
            try {
                const res = await api.get(`settlement/${caseId}`);
                if (res.success) {
                    this.settlementData = res;
                    this.settings = { ...this.settings, ...res.settings };
                    // Map DB decimal to precise fraction
                    const fp = parseFloat(this.settings.attorney_fee_percent);
                    if (fp > 0.33 && fp < 0.34) this.settings.attorney_fee_percent = 1/3;
                    else if (fp === 0.4) this.settings.attorney_fee_percent = 0.4;
                    this.bestOffers = res.best_offers;
                    this.medicalBills = res.medical_bills;
                    this.medicalBalance = res.medical_balance;
                    this.healthSubrogation = res.health_subrogation;
                    this.expenses = res.expenses;
                    this.pipInfo = res.pip_info;

                    // Auto-fill PIP fields from MBDS if not set
                    if (!this.settings.pip_insurance_company && this.pipInfo?.pip1_name) {
                        this.settings.pip_insurance_company = this.pipInfo.pip1_name;
                    }
                    if (!this.settings.pip_subrogation_amount && this.pipInfo?.pip1_total > 0) {
                        this.settings.pip_subrogation_amount = this.pipInfo.pip1_total;
                    }

                    this.calculate();
                }
            } catch (e) {
                console.error('Failed to load settlement data:', e);
            }
        },

        fmtMoney(val) {
            if (val === null || val === undefined) return '$0.00';
            const abs = Math.abs(val);
            const formatted = '$' + abs.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return val < 0 ? '-' + formatted : formatted;
        },

        setFeePercent(pct) {
            this.settings.attorney_fee_percent = pct;
            this.onSettingsChange();
        },

        selectMethod(method) {
            this.settings.settlement_method = method;
            this.onSettingsChange();
        },

        showMahler() {
            return this.settings.pip_subrogation_amount > 0 && this.settings.coverage_3rd_party;
        },

        showHamm() {
            return this.settings.pip_subrogation_amount > 0 && (this.settings.coverage_um || this.settings.coverage_uim);
        },

        onSettingsChange() {
            this.calculate();
            this.debouncedSave();
        },

        debouncedSave() {
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this.saveSettings(), 500);
        },

        async saveSettings() {
            try {
                await api.put(`settlement/${caseId}`, this.settings);
            } catch (e) {
                console.error('Failed to save settlement settings:', e);
            }
        },

        calculate() {
            // Calculate all methods
            this.mahlerCalc = this.getMahlerCalc();
            this.hammCalc = this.getHammCalc();

            const canMahler = this.showMahler();
            const canHamm = this.showHamm();

            // Clear method if conditions no longer met
            let method = this.settings.settlement_method;
            if (method === 'mahler' && !canMahler) method = null;
            if (method === 'hamm' && !canHamm) method = null;

            // Determine which calculation to use
            if (method === 'mahler') {
                this.calculated = this.mahlerCalc;
            } else if (method === 'hamm') {
                this.calculated = this.hammCalc;
            } else if (canMahler && !canHamm) {
                // Only Mahler available → auto-select
                this.calculated = this.mahlerCalc;
                this.settings.settlement_method = 'mahler';
            } else if (canHamm && !canMahler) {
                // Only Hamm available → auto-select
                this.calculated = this.hammCalc;
                this.settings.settlement_method = 'hamm';
            } else {
                // Standard: no PIP method or both available (user must pick)
                this.calculated = this.getStandardCalc();
                if (!canMahler && !canHamm) this.settings.settlement_method = null;
            }

            this.buildDisbursementLines();
        },

        getMahlerCalc() {
            const gross = this.bestOffers['3rd_party'] || this.settings.settlement_amount || 0;
            const feePercent = this.settings.attorney_fee_percent || 1/3;
            const fee = Math.round(gross * feePercent * 100) / 100;
            const costs = this.expenses.reimbursable || 0;
            const afe = fee + costs;
            const attorneyPercent = gross > 0 ? afe / gross : 0;
            const pip = this.settings.pip_subrogation_amount || 0;
            const attorneySharePip = Math.round(pip * attorneyPercent * 100) / 100;
            const carrierShare = this.settings.policy_limit
                ? 0
                : Math.round(pip * (1 - attorneyPercent) * 100) / 100;
            const medBal = this.medicalBalance || 0;
            const subrogation = this.healthSubrogation || 0;
            const totalDeductions = fee + costs + carrierShare + medBal + subrogation;
            const clientNet = Math.round((gross - totalDeductions) * 100) / 100;

            return {
                method: 'mahler',
                gross, fee, costs, afe, attorneyPercent, attorneySharePip, carrierShare,
                medicalBalance: medBal, subrogation, totalDeductions, clientNet,
            };
        },

        getHammCalc() {
            const umOffer = Math.max(this.bestOffers['um'] || 0, this.bestOffers['uim'] || 0);
            const pip = this.settings.pip_subrogation_amount || 0;
            const gross = umOffer + pip;
            const feePercent = this.settings.attorney_fee_percent || 1/3;
            const fee = Math.round(gross * feePercent * 100) / 100;
            const costs = this.expenses.reimbursable || 0;
            const afe = fee + costs;
            const attorneyPercent = gross > 0 ? afe / gross : 0;
            const hammFee = Math.round(attorneyPercent * pip * 100) / 100;
            const medBal = this.medicalBalance || 0;
            const subrogation = this.healthSubrogation || 0;
            const totalDeductions = fee + costs + hammFee + medBal + subrogation;
            const clientNet = Math.round((umOffer - fee - costs - hammFee - medBal - subrogation) * 100) / 100;

            return {
                method: 'hamm',
                gross, fee, costs, afe, attorneyPercent, hammFee,
                medicalBalance: medBal, subrogation, totalDeductions, clientNet,
            };
        },

        getStandardCalc() {
            // Gross = manual settlement amount, or sum of best offers from selected coverages
            let gross = this.settings.settlement_amount || 0;
            if (!gross) {
                // Sum best offers from active coverages
                if (this.settings.coverage_3rd_party) gross += this.bestOffers['3rd_party'] || 0;
                if (this.settings.coverage_um) gross += this.bestOffers['um'] || 0;
                if (this.settings.coverage_uim) gross += this.bestOffers['uim'] || 0;
                // If no coverage selected, use all offers
                if (!gross) gross = Object.values(this.bestOffers).reduce((s, v) => s + v, 0);
            }
            const feePercent = this.settings.attorney_fee_percent || 1/3;
            const fee = Math.round(gross * feePercent * 100) / 100;
            const costs = this.expenses.reimbursable || 0;
            const medBal = this.medicalBalance || 0;
            const subrogation = this.healthSubrogation || 0;
            const totalDeductions = fee + costs + medBal + subrogation;
            const clientNet = Math.round((gross - totalDeductions) * 100) / 100;

            return {
                method: 'standard',
                gross, fee, costs, afe: fee + costs, attorneyPercent: 0,
                medicalBalance: medBal, subrogation, totalDeductions, clientNet,
            };
        },

        buildDisbursementLines() {
            const c = this.calculated;
            if (!c) { this.disbursementLines = []; return; }

            const lines = [];

            // Settlement Proceeds
            lines.push({ section: true, label: 'Settlement Proceeds' });
            if (c.method === 'mahler') {
                lines.push({ label: '3rd Party Settlement', amount: c.gross, indent: true });
            } else if (c.method === 'hamm') {
                lines.push({ label: 'UM/UIM Settlement + PIP', amount: c.gross, indent: true });
            } else {
                lines.push({ label: 'Settlement Proceeds', amount: c.gross, indent: true });
            }

            // Attorney Fees & Costs
            lines.push({ section: true, label: 'Attorney Fees & Costs' });
            const feeLabel = this.settings.attorney_fee_percent >= 0.34
                ? 'Attorney Fee (40%)'
                : 'Attorney Fee (33.33%)';
            lines.push({ label: feeLabel, amount: -c.fee, indent: true });
            if (c.costs > 0) {
                lines.push({ label: 'Costs (Reimbursable)', amount: -c.costs, indent: true });
            }

            // PIP section (method-specific)
            if (c.method === 'mahler' && this.settings.pip_subrogation_amount > 0) {
                lines.push({ section: true, label: 'PIP Subrogation (Mahler)' });
                if (this.settings.policy_limit) {
                    lines.push({ label: 'Carrier Share — Waived (Policy Limit)', amount: 0, indent: true });
                } else {
                    const company = this.settings.pip_insurance_company || 'PIP Carrier';
                    lines.push({ label: `Carrier Share → ${company}`, amount: -c.carrierShare, indent: true });
                }
            } else if (c.method === 'hamm' && this.settings.pip_subrogation_amount > 0) {
                lines.push({ section: true, label: 'PIP Subrogation (Hamm)' });
                lines.push({ label: 'Hamm Fee (PIP Recovery)', amount: -c.hammFee, indent: true });
            }

            // Medical Bills & Liens
            if (this.medicalBills.providers.length > 0) {
                lines.push({ section: true, label: 'Medical Bills & Liens' });
                for (const p of this.medicalBills.providers) {
                    const amt = p.negotiated_amount !== undefined ? p.negotiated_amount : p.balance;
                    if (amt > 0) {
                        lines.push({ label: p.name, amount: -amt, indent: true });
                    }
                }
            }

            // Health Subrogation
            if (c.subrogation > 0) {
                lines.push({ section: true, label: 'Subrogation' });
                lines.push({ label: 'Health Insurance Subrogation', amount: -c.subrogation, indent: true });
            }

            // Total
            lines.push({ isTotal: true, label: 'CLIENT NET PROCEEDS', amount: c.clientNet });

            this.disbursementLines = lines;
        },

        _printPage(title, rows) {
            const w = window.open('', '_blank', 'width=800,height=600');
            w.document.write(`<!DOCTYPE html><html><head><title>${title}</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'IBM Plex Sans', sans-serif; font-size: 13px; color: #1a2535; margin: 40px; }
    h1 { font-size: 18px; color: #0F1B2D; margin-bottom: 4px; }
    .meta { font-size: 12px; color: #8a8a82; margin-bottom: 24px; }
    table { width: 100%; border-collapse: collapse; border: 1px solid #ddddd8; }
    td { border-bottom: 1px solid #ddddd8; }
    .section td { background: #f5f5f0; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #8a8a82; padding: 6px 16px; }
    .total td { background: #0F1B2D; color: #fff; font-weight: 700; font-size: 14px; padding: 10px 16px; }
    .amt { text-align: right; font-family: 'IBM Plex Mono', monospace; }
    .neg { color: #b83232; }
    .pos { color: #2a6b4a; }
    @media print { body { margin: 20px; } }
</style></head><body>
<h1>${title}</h1>
<div class="meta">Printed: ${new Date().toLocaleDateString()}</div>
<table>${rows}</table>
<script>window.print();<\/script>
</body></html>`);
            w.document.close();
        },

        _buildRows(lines) {
            let rows = '';
            for (const line of lines) {
                if (line.section) {
                    rows += `<tr class="section"><td colspan="2">${line.label}</td></tr>`;
                } else if (line.isTotal) {
                    rows += `<tr class="total"><td style="padding:10px 16px;">${line.label}</td><td class="amt" style="padding:10px 16px; font-family:'IBM Plex Mono',monospace;">${this.fmtMoney(line.amount)}</td></tr>`;
                } else {
                    const cls = line.amount < 0 ? 'neg' : (line.amount > 0 ? 'pos' : '');
                    rows += `<tr><td style="padding:6px 16px; ${line.indent ? 'padding-left:32px;' : ''}">${line.label}</td><td class="amt ${cls}" style="padding:6px 16px;">${this.fmtMoney(line.amount)}</td></tr>`;
                }
            }
            return rows;
        },

        printDisbursement() {
            if (!this.calculated) return;
            this._printPage('Settlement Disbursement Statement', this._buildRows(this.disbursementLines));
        },

        printMethod(method) {
            const calc = method === 'mahler' ? this.mahlerCalc : this.hammCalc;
            if (!calc) return;

            const lines = [];
            if (method === 'mahler') {
                lines.push({ section: true, label: 'Mahler Method — 3rd Party + PIP Subrogation' });
                lines.push({ label: 'Gross (3rd Party Settlement)', amount: calc.gross, indent: true });
                lines.push({ label: `Attorney Fee (${(this.settings.attorney_fee_percent * 100).toFixed(2)}%)`, amount: -calc.fee, indent: true });
                lines.push({ label: 'Costs (Reimbursable)', amount: -calc.costs, indent: true });
                lines.push({ section: true, label: 'Attorney Fee Equivalent (AFE)' });
                lines.push({ label: 'AFE (Fee + Costs)', amount: calc.afe, indent: true });
                lines.push({ label: `Attorney % of Gross (${(calc.attorneyPercent * 100).toFixed(2)}%)`, amount: null, indent: true });
                lines.push({ section: true, label: 'PIP Subrogation' });
                const pip = this.settings.pip_subrogation_amount || 0;
                const company = this.settings.pip_insurance_company || 'PIP Carrier';
                lines.push({ label: 'PIP Subrogation Amount', amount: pip, indent: true });
                lines.push({ label: `Attorney Share of PIP (${(calc.attorneyPercent * 100).toFixed(2)}%)`, amount: calc.attorneySharePip, indent: true });
                if (this.settings.policy_limit) {
                    lines.push({ label: `Carrier Share → ${company} (Waived — Policy Limit)`, amount: 0, indent: true });
                } else {
                    lines.push({ label: `Carrier Share → ${company}`, amount: -calc.carrierShare, indent: true });
                }
                lines.push({ section: true, label: 'Deductions' });
                lines.push({ label: 'Medical Balance', amount: -calc.medicalBalance, indent: true });
                if (calc.subrogation > 0) {
                    lines.push({ label: 'Health Insurance Subrogation', amount: -calc.subrogation, indent: true });
                }
                lines.push({ isTotal: true, label: 'CLIENT NET PROCEEDS', amount: calc.clientNet });
            } else {
                const umOffer = Math.max(this.bestOffers['um'] || 0, this.bestOffers['uim'] || 0);
                const pip = this.settings.pip_subrogation_amount || 0;
                lines.push({ section: true, label: 'Hamm Method — UM/UIM + PIP Subrogation' });
                lines.push({ label: 'UM/UIM Offer', amount: umOffer, indent: true });
                lines.push({ label: 'PIP Subrogation', amount: pip, indent: true });
                lines.push({ label: 'Combined Gross (UM/UIM + PIP)', amount: calc.gross, indent: true });
                lines.push({ label: `Attorney Fee (${(this.settings.attorney_fee_percent * 100).toFixed(2)}%)`, amount: -calc.fee, indent: true });
                lines.push({ label: 'Costs (Reimbursable)', amount: -calc.costs, indent: true });
                lines.push({ section: true, label: 'Hamm Fee Calculation' });
                lines.push({ label: 'AFE (Fee + Costs)', amount: calc.afe, indent: true });
                lines.push({ label: `Attorney % of Gross (${(calc.attorneyPercent * 100).toFixed(2)}%)`, amount: null, indent: true });
                lines.push({ label: 'Hamm Fee (Attorney % × PIP)', amount: -calc.hammFee, indent: true });
                lines.push({ section: true, label: 'Deductions' });
                lines.push({ label: 'Medical Balance', amount: -calc.medicalBalance, indent: true });
                if (calc.subrogation > 0) {
                    lines.push({ label: 'Health Insurance Subrogation', amount: -calc.subrogation, indent: true });
                }
                lines.push({ isTotal: true, label: 'CLIENT NET PROCEEDS', amount: calc.clientNet });
            }

            // Handle null amounts (info-only rows)
            const printLines = lines.map(l => ({
                ...l,
                amount: l.amount === null ? undefined : l.amount,
            }));

            const title = method === 'mahler' ? 'Mahler Method Calculation' : 'Hamm Method Calculation';
            let rows = '';
            for (const line of printLines) {
                if (line.section) {
                    rows += `<tr class="section"><td colspan="2">${line.label}</td></tr>`;
                } else if (line.isTotal) {
                    rows += `<tr class="total"><td style="padding:10px 16px;">${line.label}</td><td class="amt" style="padding:10px 16px; font-family:'IBM Plex Mono',monospace;">${this.fmtMoney(line.amount)}</td></tr>`;
                } else {
                    const amtStr = line.amount !== undefined ? this.fmtMoney(line.amount) : '';
                    const cls = (line.amount || 0) < 0 ? 'neg' : ((line.amount || 0) > 0 ? 'pos' : '');
                    rows += `<tr><td style="padding:6px 16px; ${line.indent ? 'padding-left:32px;' : ''}">${line.label}</td><td class="amt ${cls}" style="padding:6px 16px;">${amtStr}</td></tr>`;
                }
            }
            this._printPage(title, rows);
        },
    };
}
