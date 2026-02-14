<div x-data="{ open: false }" class="relative">
    <button @click="open = !open; if(open) $store.notifications.load()" class="relative p-2 text-gray-500 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <template x-if="$store.notifications.unreadCount > 0">
            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"
                  x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"></span>
        </template>
    </button>

    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false" x-transition
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Notifications</h3>
            <button @click="$store.notifications.markAllRead()"
                    class="text-xs text-blue-600 hover:text-blue-800"
                    x-show="$store.notifications.unreadCount > 0">
                Mark all read
            </button>
        </div>

        <div class="max-h-80 overflow-y-auto">
            <template x-if="$store.notifications.items.length === 0">
                <div class="px-4 py-8 text-center text-gray-400 text-sm">
                    No new notifications
                </div>
            </template>

            <template x-for="notification in $store.notifications.items" :key="notification.id">
                <div class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50 flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800" x-text="notification.message"></p>
                        <p class="text-xs text-gray-400 mt-1" x-text="timeAgo(notification.created_at)"></p>
                    </div>
                    <button @click="$store.notifications.markRead(notification.id)"
                            class="text-gray-400 hover:text-gray-600 flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
