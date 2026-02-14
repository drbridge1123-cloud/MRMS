<!-- Search Input Component -->
<!-- Usage: Include and customize the x-model and @input handler -->
<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="w-4 h-4 text-v2-text-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    <input type="text"
           x-model="searchQuery"
           @input.debounce.300ms="loadData(1)"
           placeholder="<?= $searchPlaceholder ?? 'Search...' ?>"
           class="w-full pl-10 pr-4 py-2 border border-v2-card-border rounded-lg text-sm focus:ring-2 focus:ring-gold focus:border-gold outline-none">
    <template x-if="searchQuery">
        <button @click="searchQuery = ''; loadData(1)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-v2-text-light hover:text-v2-text-mid">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </template>
</div>
