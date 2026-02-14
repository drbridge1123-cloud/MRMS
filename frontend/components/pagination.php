<!-- Alpine.js Pagination Component -->
<!-- Expects: pagination object { total, page, per_page, total_pages } and a loadPage(n) method -->
<template x-if="pagination && pagination.total_pages > 1">
    <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-v2-card-border sm:px-6">
        <div class="text-sm text-v2-text-light">
            Showing <span x-text="((pagination.page - 1) * pagination.per_page) + 1"></span>
            to <span x-text="Math.min(pagination.page * pagination.per_page, pagination.total)"></span>
            of <span x-text="pagination.total"></span> results
        </div>
        <div class="flex gap-1">
            <!-- Previous -->
            <button @click="loadPage(pagination.page - 1)"
                    :disabled="pagination.page <= 1"
                    :class="pagination.page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-v2-bg'"
                    class="px-3 py-1.5 text-sm border border-v2-card-border rounded-md bg-white text-v2-text">
                Prev
            </button>

            <!-- Page numbers -->
            <template x-for="p in pagination.total_pages" :key="p">
                <template x-if="p === 1 || p === pagination.total_pages || (p >= pagination.page - 2 && p <= pagination.page + 2)">
                    <button @click="loadPage(p)"
                            :class="p === pagination.page ? 'bg-gold text-white border-gold' : 'bg-white text-v2-text hover:bg-v2-bg border-v2-card-border'"
                            class="px-3 py-1.5 text-sm border rounded-md"
                            x-text="p">
                    </button>
                </template>
            </template>

            <!-- Next -->
            <button @click="loadPage(pagination.page + 1)"
                    :disabled="pagination.page >= pagination.total_pages"
                    :class="pagination.page >= pagination.total_pages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-v2-bg'"
                    class="px-3 py-1.5 text-sm border border-v2-card-border rounded-md bg-white text-v2-text">
                Next
            </button>
        </div>
    </div>
</template>
