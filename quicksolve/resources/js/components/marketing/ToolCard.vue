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
    <Link :href="`/tools/${tool.slug}`" class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ tool.category?.name }}</p>
            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="tool.access_type === 'premium' ? 'bg-blue-50 text-blue-800' : 'bg-slate-100 text-slate-700'">
                {{ tool.access_type === 'premium' ? 'Pro' : 'Free' }}
            </span>
        </div>
        <h3 class="mt-3 text-lg font-semibold text-slate-950 dark:text-white">{{ tool.name }}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ tool.description }}</p>
    </Link>
</template>
