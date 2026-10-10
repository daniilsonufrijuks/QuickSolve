<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    tool: {
        name: string;
        slug: string;
        description: string;
        access_type: string;
        category?: { name?: string | null };
    };
}>();
</script>

<template>
    <Link :href="`/tools/${tool.slug}`" class="sm0ov8z">
        <div class="s1g4q79s">
            <p class="s1e0233s">{{ tool.category?.name }}</p>
            <span class="s1lh8vfn" :class="tool.access_type === 'premium' ? 'bg-blue-50 text-blue-800' : 'bg-slate-100 text-blue-800'">
                {{ tool.access_type === 'premium' ? 'Pro' : 'Free' }}
            </span>
        </div>
        <h3 class="s1dcbn2s">{{ tool.name }}</h3>
        <p class="s1u3njk">{{ tool.description }}</p>
    </Link>
</template>

<style scoped>
.sm0ov8z {
    @apply flex h-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 dark:border-slate-800 dark:bg-slate-900;
}

.s1g4q79s {
    @apply flex items-center justify-between gap-3;
}

.s1e0233s {
    @apply text-xs font-medium uppercase tracking-wide text-slate-500;
}

.s1lh8vfn {
    @apply rounded-full px-2 py-0.5 text-xs font-medium;
}

.s1dcbn2s {
    @apply mt-3 text-lg font-semibold text-slate-950 dark:text-white;
}

.s1u3njk {
    @apply mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300;
}
</style>
