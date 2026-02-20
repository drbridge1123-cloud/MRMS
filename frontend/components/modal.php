<!-- Reusable Modal Component (Alpine.js) -->
<!-- Usage: Set x-data with showModal boolean, use @open-modal.window to listen -->
<template x-teleport="body">
    <div x-show="showModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <!-- Overlay -->
        <div class="modal-v2-backdrop fixed inset-0" @click="showModal = false"></div>

        <!-- Modal content -->
        <div class="modal-v2 relative w-full max-w-lg z-10" @click.stop>
            <!-- Slot content goes here -->
        </div>
    </div>
</template>
