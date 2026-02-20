// MRMS - API Helper Functions

const API_BASE = '/MRMS/backend/api';

async function apiCall(endpoint, options = {}) {
    const url = `${API_BASE}/${endpoint}`;
    const config = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        ...options,
    };

    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }

    try {
        const response = await fetch(url, config);
        const data = await response.json();

        if (response.status === 401) {
            window.location.href = '/MRMS/frontend/pages/auth/login.php';
            return null;
        }

        if (!response.ok) {
            throw { response, data };
        }

        return data;
    } catch (error) {
        if (error.data) {
            // This is an API error with a proper response, just throw it
            throw error;
        }
        // Only log unexpected network errors, not API validation errors
        if (error.name !== 'AbortError') {
            console.error('API call failed:', error);
            showToast('Network error. Please try again.', 'error');
        }
        throw error;
    }
}

// Convenience methods
const api = {
    get: (endpoint) => apiCall(endpoint, { method: 'GET' }),
    post: (endpoint, body) => apiCall(endpoint, { method: 'POST', body }),
    put: (endpoint, body) => apiCall(endpoint, { method: 'PUT', body }),
    delete: (endpoint) => apiCall(endpoint, { method: 'DELETE' }),
    upload: async (endpoint, formData, onProgress) => {
        const url = `${API_BASE}/${endpoint}`;
        try {
            // Use XMLHttpRequest if progress callback is provided
            if (onProgress && typeof onProgress === 'function') {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            onProgress(Math.round(percentComplete));
                        }
                    });

                    xhr.addEventListener('load', () => {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            if (xhr.status === 401) {
                                window.location.href = '/MRMS/frontend/pages/auth/login.php';
                                return;
                            }
                            if (xhr.status >= 200 && xhr.status < 300) {
                                resolve(data);
                            } else {
                                reject({ response: { status: xhr.status }, data });
                            }
                        } catch (error) {
                            reject(error);
                        }
                    });

                    xhr.addEventListener('error', () => {
                        reject(new Error('Upload failed'));
                    });

                    xhr.open('POST', url);
                    xhr.send(formData);
                });
            }

            // Fall back to fetch if no progress callback
            const response = await fetch(url, { method: 'POST', body: formData });
            const data = await response.json();
            if (response.status === 401) {
                window.location.href = '/MRMS/frontend/pages/auth/login.php';
                return null;
            }
            if (!response.ok) throw { response, data };
            return data;
        } catch (error) {
            if (error.data) throw error;
            console.error('Upload failed:', error);
            showToast('Upload failed. Please try again.', 'error');
            throw error;
        }
    },
};

// Toast notification
function showToast(message, type = 'success', duration = 3000) {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500',
    };

    const icons = {
        success: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`,
        error: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`,
        warning: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>`,
        info: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>`,
    };

    const toast = document.createElement('div');
    toast.className = `toast ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3`;
    toast.innerHTML = `${icons[type]}<span>${message}</span>`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Format date for display
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        timeZone: 'UTC',
    });
}

// Calculate days elapsed
function daysElapsed(dateStr) {
    if (!dateStr) return null;
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    return diff;
}

// Status label mapping
const STATUS_LABELS = {
    not_started: 'Not Started',
    requesting: 'Requesting',
    follow_up: 'Follow Up',
    action_needed: 'Action Needed',
    received_partial: 'Partial',
    on_hold: 'On Hold',
    received_complete: 'Complete',
    verified: 'Verified',
    collecting: 'Collecting',
    in_review: 'In Review',
    verification: 'Verification',
    completed: 'Completed',
    closed: 'Closed',
};

// Valid forward transitions (used by the status dropdown)
const FORWARD_TRANSITIONS = {
    collecting:   ['in_review'],
    in_review:    ['verification', 'completed'],
    verification: ['completed'],
    completed:    ['closed'],
    closed:       [],
};

// Valid backward transitions (used by send-back modal)
const BACKWARD_TRANSITIONS = {
    collecting:   [],
    in_review:    ['collecting'],
    verification: ['collecting', 'in_review'],
    completed:    ['collecting', 'in_review', 'verification'],
    closed:       ['collecting', 'in_review', 'verification', 'completed'],
};

function getStatusLabel(status) {
    return STATUS_LABELS[status] || status;
}

// Debounce function for search inputs
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// URL query params helper
function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Confirm dialog
function confirmAction(message) {
    return new Promise((resolve) => {
        if (confirm(message)) {
            resolve(true);
        } else {
            resolve(false);
        }
    });
}
