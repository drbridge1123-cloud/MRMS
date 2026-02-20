// MRMS - Shared Utilities
// All list pages share these functions. Change here → affects all pages.

/**
 * List page base mixin (used by 7+ pages)
 * Usage: return { ...listPageBase('tracker/list', { defaultSort: 'deadline', ... }), ...pageSpecific }
 */
function listPageBase(apiEndpoint, options = {}) {
    const config = {
        defaultSort: options.defaultSort || 'created_at',
        defaultDir: options.defaultDir || 'desc',
        perPage: options.perPage || 25,
        filtersToParams: options.filtersToParams || function() { return {}; },
    };

    return {
        // Common state
        items: [],
        pagination: null,
        loading: true,
        search: '',
        sortBy: config.defaultSort,
        sortDir: config.defaultDir,

        // Load data from API
        async loadData(page = 1) {
            this.loading = true;
            try {
                const filterParams = config.filtersToParams.call(this);
                const params = buildQueryString({
                    search: this.search,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir,
                    page: page,
                    per_page: config.perPage,
                    ...filterParams
                });
                const res = await api.get(apiEndpoint + params);
                this.items = res.data || [];
                this.pagination = res.pagination || null;
                if (res.summary) this.summary = res.summary;
                if (res.staff) this.staffList = res.staff;
            } catch (e) {
                console.error('loadData error:', e);
            }
            this.loading = false;
        },

        // Toggle sort column
        sort(column) {
            if (this.sortBy === column) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = column;
                this.sortDir = 'asc';
            }
            this.loadData(1);
        },

        // Reset all filters to defaults
        resetFilters() {
            this.search = '';
            this.sortBy = config.defaultSort;
            this.sortDir = config.defaultDir;
            if (this._resetPageFilters) this._resetPageFilters();
            this.loadData(1);
        },

        // Check if any filter is active (for showing reset button)
        hasActiveFilters() {
            return this.search !== '' ||
                   (this._hasPageFilters ? this._hasPageFilters() : false);
        },
    };
}

/**
 * Dynamic scroll container (used by 3+ table pages)
 * Sets max-height to fill remaining viewport and enables vertical scroll.
 * Usage in HTML: x-init="initScrollContainer($el)"
 */
function initScrollContainer(el, bottomPadding = 16) {
    function update() {
        const t = el.getBoundingClientRect().top;
        el.style.maxHeight = (window.innerHeight - t - bottomPadding) + 'px';
        el.style.overflowY = 'auto';
    }
    requestAnimationFrame(update);
    window.addEventListener('resize', debounce(update, 100));
}

/**
 * Sort indicator arrow for table headers
 * Usage in HTML: x-html="sortIcon('column_name')"
 */
function sortIcon(sortBy, sortDir, column) {
    if (sortBy !== column) return '<span class="text-v2-text-light/30 ml-1">&#8597;</span>';
    return sortDir === 'asc'
        ? '<span class="text-gold ml-1">&#8593;</span>'
        : '<span class="text-gold ml-1">&#8595;</span>';
}
