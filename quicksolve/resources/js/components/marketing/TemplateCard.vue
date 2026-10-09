<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    template: {
        name: string;
        slug: string;
        description: string;
        formatted_price: string;
        preview_image?: string | null;
        category?: { name?: string | null };
    };
}>();
</script>

<template>
    <Link :href="`/templates/${template.slug}`" class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <img v-if="template.preview_image" :src="template.preview_image" :alt="`${template.name} preview`" class="h-40 w-full object-cover" />
        <div class="flex flex-1 flex-col p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ template.category?.name }}</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ template.name }}</h3>
            <p class="mt-2 flex-1 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ template.description }}</p>
            <p class="mt-4 text-sm font-semibold text-slate-950 dark:text-white">{{ template.formatted_price }}</p>
        </div>
    </Link>
</template>
