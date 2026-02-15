// MRMS - Utility Functions

// Provider type labels
const PROVIDER_TYPES = {
    hospital: 'Hospital',
    er: 'Emergency Room',
    chiro: 'Chiropractor',
    imaging: 'Imaging Center',
    physician: 'Physician',
    surgery_center: 'Surgery Center',
    pharmacy: 'Pharmacy',
    other: 'Other',
};

// Request method labels
const REQUEST_METHODS = {
    email: 'Email',
    fax: 'Fax',
    portal: 'Portal',
    phone: 'Phone',
    mail: 'Mail',
    chartswap: 'ChartSwap',
    online: 'Online',
};

// Request type labels
const REQUEST_TYPES = {
    initial: 'Initial Request',
    follow_up: 'Follow-Up',
    re_request: 'Re-Request',
    rfd: 'RFD',
};

// Note type labels
const NOTE_TYPES = {
    general: 'General',
    follow_up: 'Follow-Up',
    issue: 'Issue',
    handoff: 'Handoff',
};

// Difficulty labels
const DIFFICULTY_LEVELS = {
    easy: 'Easy',
    medium: 'Medium',
    hard: 'Hard',
};

function getProviderTypeLabel(type) {
    return PROVIDER_TYPES[type] || type;
}

function getRequestMethodLabel(method) {
    return REQUEST_METHODS[method] || method;
}

// Build query string from object
function buildQueryString(params) {
    const filtered = Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined);
    return filtered.length ? '?' + new URLSearchParams(filtered).toString() : '';
}

// Relative time display
function timeAgo(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
    return formatDate(dateStr);
}

// Truncate text
function truncate(str, len = 50) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
}
