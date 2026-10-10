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
    <Link :href="`/templates/${template.slug}`" class="s1k1gdv5">
        <img v-if="template.preview_image" :src="template.preview_image" :alt="`${template.name} preview`" class="s1s2yip1" />
        <div class="sb1o7vk">
            <p class="s1e0233s">{{ template.category?.name }}</p>
            <h3 class="s163jks5">{{ template.name }}</h3>
            <p class="sjypt7v">{{ template.description }}</p>
            <p class="sp9so1g">{{ template.formatted_price }}</p>
        </div>
    </Link>
</template>

<style scoped>
.s1k1gdv5 {
    @apply flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900;
}

.s1s2yip1 {
    @apply h-40 w-full object-cover;
}

.sb1o7vk {
    @apply flex flex-1 flex-col p-5;
}

.s1e0233s {
    @apply text-xs font-medium uppercase tracking-wide text-slate-500;
}

.s163jks5 {
    @apply mt-2 text-lg font-semibold text-slate-950 dark:text-white;
}

.sjypt7v {
    @apply mt-2 flex-1 text-sm leading-6 text-slate-600 dark:text-slate-300;
}

.sp9so1g {
    @apply mt-4 text-sm font-semibold text-slate-950 dark:text-white;
}
</style>
