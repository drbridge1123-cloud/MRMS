function dashboardPage() {
    return {
        summary: {},
        followups: [],
        overdueItems: [],
        escalations: [],
        cases: [],
        staffMetrics: {},
        systemHealth: {},
        providerAnalytics: {},
        providerInsightsExpanded: false,
        loading: true,

        async init() {
            await Promise.all([
                this.loadSummary(),
                this.loadFollowups(),
                this.loadOverdue(),
                this.loadEscalations(),
                this.loadCases(),
                this.loadStaffMetrics(),
                this.loadSystemHealth(),
                this.loadProviderAnalytics()
            ]);
            this.loading = false;
        },

        async loadSummary() {
            try {
                const res = await api.get('dashboard/summary');
                this.summary = res.data || {};
            } catch (e) {}
        },

        async loadFollowups() {
            try {
                const res = await api.get('dashboard/followup-due');
                this.followups = res.data || [];
            } catch (e) {}
        },

        async loadOverdue() {
            try {
                const res = await api.get('dashboard/overdue');
                this.overdueItems = res.data || [];
            } catch (e) {}
        },

        async loadEscalations() {
            try {
                const res = await api.get('dashboard/escalations');
                this.escalations = res.data || [];
            } catch (e) {}
        },

        async loadCases() {
            try {
                const res = await api.get('cases?per_page=10');
                this.cases = res.data || [];
            } catch (e) {}
        },

        async loadStaffMetrics() {
            try {
                const res = await api.get('dashboard/staff-metrics');
                this.staffMetrics = res.data || {};
            } catch (e) {}
        },

        async loadSystemHealth() {
            try {
                const res = await api.get('dashboard/system-health');
                this.systemHealth = res.data || {};
            } catch (e) {}
        },

        async loadProviderAnalytics() {
            try {
                const res = await api.get('dashboard/provider-analytics');
                this.providerAnalytics = res.data || {};
            } catch (e) {}
        },

        toggleProviderInsights() {
            this.providerInsightsExpanded = !this.providerInsightsExpanded;
        }
    };
}
