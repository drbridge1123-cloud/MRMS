// MRMS - Alpine.js Global Stores

document.addEventListener('alpine:init', () => {

    // Current user store
    Alpine.store('auth', {
        user: null,
        loading: true,

        async init() {
            try {
                const res = await api.get('auth/me');
                this.user = res.data;
            } catch (e) {
                this.user = null;
            }
            this.loading = false;
        },

        get isAdmin() {
            return this.user?.role === 'admin';
        },

        get isManager() {
            return this.user?.role === 'manager';
        },

        get isAccounting() {
            return this.user?.role === 'accounting';
        },

        get isStaff() {
            return this.user?.role === 'staff';
        },

        get isAdminOrManager() {
            return this.user?.role === 'admin' || this.user?.role === 'manager';
        },

        get permissions() {
            return this.user?.permissions || [];
        },

        hasPermission(page) {
            if (this.user?.role === 'admin') return true;
            return this.permissions.includes(page);
        },

        async logout() {
            try {
                await api.post('auth/logout');
            } catch (e) {}
            window.location.href = '/MRMS/frontend/pages/auth/login.php';
        }
    });

    // Messages store (unified notifications + messaging)
    Alpine.store('messages', {
        unreadCount: 0,

        async loadCount() {
            try {
                const res = await api.get('messages?filter=unread');
                this.unreadCount = res.unread_count || 0;
            } catch (e) {}
        }
    });

    // Poll messages every 30 seconds
    Alpine.store('messages').loadCount();
    setInterval(() => Alpine.store('messages').loadCount(), 30000);

    // Sidebar state
    Alpine.store('sidebar', {
        collapsed: false,

        toggle() {
            this.collapsed = !this.collapsed;
        }
    });
});
