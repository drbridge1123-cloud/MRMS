<div x-data="{ open: false }" class="relative">
    <button @click="open = !open; if(open) $store.notifications.load()" style="position:relative; display:inline-flex; align-items:center; padding:6px; background:none; border:none; cursor:pointer;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="bellGrad" x1="7" y1="3" x2="17" y2="20" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#E8D48B"/>
                    <stop offset="40%" stop-color="#C9A84C"/>
                    <stop offset="100%" stop-color="#A68A30"/>
                </linearGradient>
                <linearGradient id="bellHighlight" x1="9" y1="4" x2="13" y2="12" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#fff" stop-opacity="0.45"/>
                    <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                </linearGradient>
                <filter id="bellShadow" x="-2" y="-1" width="28" height="28">
                    <feDropShadow dx="0" dy="1" stdDeviation="1" flood-color="#8B7330" flood-opacity="0.35"/>
                </filter>
            </defs>
            <!-- Bell body -->
            <path d="M12 2.5a1.5 1.5 0 0 0-1.5 1.5v.3A6 6 0 0 0 5 10c0 2.7-.6 4.6-1.24 5.87A1.06 1.06 0 0 0 4.7 17.2h14.6a1.06 1.06 0 0 0 .94-1.33C19.6 14.6 19 12.7 19 10a6 6 0 0 0-5.5-5.7V4A1.5 1.5 0 0 0 12 2.5z" fill="url(#bellGrad)" filter="url(#bellShadow)"/>
            <!-- Highlight -->
            <path d="M9.5 5.5A5 5 0 0 1 15 8c0 1.5.2 3 .5 4.2H8C7 10 6.8 8 7.2 6.5c.3-1 .8-1 2.3-1z" fill="url(#bellHighlight)"/>
            <!-- Clapper -->
            <path d="M9.17 18.2a3 3 0 0 0 5.66 0H9.17z" fill="url(#bellGrad)"/>
        </svg>
        <template x-if="$store.notifications.unreadCount > 0">
            <span style="position:absolute; top:-4px; right:-5px; background:#e74c3c; color:#fff; font-size:8px; font-weight:800; padding:1px 4px; border-radius:9px; line-height:1.3; min-width:14px; text-align:center;"
                  x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"></span>
        </template>
    </button>

    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false" x-transition
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-v2-card-border z-50">
        <div class="flex items-center justify-between px-4 py-3 border-b border-v2-card-border">
            <h3 class="font-semibold text-v2-text">Notifications</h3>
            <button @click="$store.notifications.markAllRead()"
                    class="text-xs text-gold hover:text-gold-hover"
                    x-show="$store.notifications.unreadCount > 0">
                Mark all read
            </button>
        </div>

        <div class="max-h-80 overflow-y-auto">
            <template x-if="$store.notifications.items.length === 0">
                <div class="px-4 py-8 text-center text-v2-text-light text-sm">
                    No new notifications
                </div>
            </template>

            <template x-for="notification in $store.notifications.items" :key="notification.id">
                <div class="px-4 py-3 border-b border-v2-card-border hover:bg-v2-bg flex items-start gap-3"
                     :class="notification.type?.startsWith('escalation_') ? 'notif-' + notification.type : 'notif-default'">
                    <div class="flex-1 min-w-0">
                        <template x-if="notification.type?.startsWith('escalation_')">
                            <span class="escalation-badge mb-1" :class="{
                                'escalation-action-needed': notification.type === 'escalation_action_needed',
                                'escalation-manager': notification.type === 'escalation_manager',
                                'escalation-admin escalation-pulse': notification.type === 'escalation_admin'
                            }" x-text="notification.type === 'escalation_admin' ? 'Admin' : (notification.type === 'escalation_manager' ? 'Manager' : 'Action Needed')"></span>
                        </template>
                        <p class="text-sm text-v2-text" x-text="notification.message"></p>
                        <p class="text-xs text-v2-text-light mt-1" x-text="timeAgo(notification.created_at)"></p>
                    </div>
                    <button @click="$store.notifications.markRead(notification.id)"
                            class="text-v2-text-light hover:text-v2-text-mid flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
