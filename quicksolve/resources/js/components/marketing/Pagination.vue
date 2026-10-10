<script setup lang="ts">
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    meta: { current_page: number; last_page: number };
    path: string;
    query?: Record<string, string>;
}>();

function go(page: number) {
    router.get(props.path, { ...(props.query ?? {}), page }, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <nav v-if="meta.last_page > 1" class="s1g4q79t" aria-label="Pagination">
        <button class="s1ico1gr" type="button" :disabled="meta.current_page <= 1" @click="go(meta.current_page - 1)">Previous</button>
        <p class="syp5waw">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
        <button class="s1ico1gr" type="button" :disabled="meta.current_page >= meta.last_page" @click="go(meta.current_page + 1)">Next</button>
    </nav>
</template>

<style scoped>
.s1g4q79t {
    @apply flex items-center justify-between gap-4;
}

.s1ico1gr {
    @apply rounded-md border px-3 py-2 text-sm disabled:opacity-40;
}

.syp5waw {
    @apply text-sm text-slate-600;
}
</style>
