<?php
$pageTitle = 'Login';
ob_start();
?>

<div class="w-full max-w-md" x-data="{
    username: '',
    password: '',
    loading: false,
    error: '',

    async login() {
        this.error = '';
        this.loading = true;
        try {
            const res = await api.post('auth/login', {
                username: this.username,
                password: this.password
            });
            if (res.success) {
                window.location.href = '/MRMS/frontend/pages/dashboard/index.php';
            }
        } catch (e) {
            this.error = e.data?.message || 'Login failed. Please try again.';
        }
        this.loading = false;
    }
}">
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-navy rounded-2xl mb-4">
                <span class="text-gold font-bold text-2xl">MR</span>
            </div>
            <h1 class="text-2xl font-bold text-v2-text">MRMS</h1>
            <p class="text-v2-text-light text-sm mt-1">Medical Records Management System</p>
        </div>

        <!-- Error message -->
        <template x-if="error">
            <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm mb-4" x-text="error"></div>
        </template>

        <!-- Login form -->
        <form @submit.prevent="login()">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Username</label>
                    <input type="text" x-model="username" required autofocus
                           class="w-full px-4 py-2.5 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold focus:border-gold outline-none"
                           placeholder="Enter your username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-v2-text mb-1">Password</label>
                    <input type="password" x-model="password" required
                           class="w-full px-4 py-2.5 border border-v2-card-border rounded-lg focus:ring-2 focus:ring-gold focus:border-gold outline-none"
                           placeholder="Enter your password">
                </div>
                <button type="submit"
                        :disabled="loading"
                        class="w-full bg-gold text-white py-2.5 rounded-lg font-medium hover:bg-gold-hover transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                    <template x-if="loading">
                        <div class="spinner" style="width:18px;height:18px;border-width:2px;"></div>
                    </template>
                    <span x-text="loading ? 'Signing in...' : 'Sign In'"></span>
                </button>
            </div>
        </form>

        <p class="text-center text-xs text-v2-text-light mt-6">Bridge Law & Associates</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/auth.php';
?>
