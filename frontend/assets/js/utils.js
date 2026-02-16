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
    acupuncture: 'Acupuncture',
    massage: 'Massage',
    pain_management: 'Pain Management',
    pt: 'Physical Therapy',
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

// Format phone number to (XXX) XXX-XXXX
function formatPhoneNumber(phone) {
    if (!phone) return '-';

    // Remove all non-digit characters
    const cleaned = phone.replace(/\D/g, '');

    // Format based on length
    if (cleaned.length === 10) {
        return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
    } else if (cleaned.length === 11 && cleaned[0] === '1') {
        // Handle numbers starting with 1
        return `(${cleaned.slice(1, 4)}) ${cleaned.slice(4, 7)}-${cleaned.slice(7)}`;
    }

    // Return original if format is unexpected
    return phone;
}
