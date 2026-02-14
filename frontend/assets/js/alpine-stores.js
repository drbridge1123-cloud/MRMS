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

        async logout() {
            try {
                await api.post('auth/logout');
            } catch (e) {}
            window.location.href = '/MRMS/frontend/pages/auth/login.php';
        }
    });

    // Notifications store
    Alpine.store('notifications', {
        items: [],
        unreadCount: 0,
        loading: false,

        async load() {
            this.loading = true;
            try {
                const res = await api.get('notifications?unread_only=1');
                this.items = res.data || [];
                this.unreadCount = this.items.length;
            } catch (e) {
                this.items = [];
            }
            this.loading = false;
        },

        async markRead(id) {
            try {
                await api.put(`notifications/${id}/read`);
                this.items = this.items.filter(n => n.id !== id);
                this.unreadCount = this.items.length;
            } catch (e) {}
        },

        async markAllRead() {
            try {
                await api.put('notifications/read-all');
                this.items = [];
                this.unreadCount = 0;
            } catch (e) {}
        }
    });

    // Sidebar state
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('sidebar_collapsed') === 'true',

        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed);
        }
    });
});
