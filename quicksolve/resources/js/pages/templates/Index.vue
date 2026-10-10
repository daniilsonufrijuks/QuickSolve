<script setup lang="ts">
import CategoryFilter from '@/components/marketing/CategoryFilter.vue';
import EmptyState from '@/components/marketing/EmptyState.vue';
import Pagination from '@/components/marketing/Pagination.vue';
import SearchInput from '@/components/marketing/SearchInput.vue';
import TemplateCard from '@/components/marketing/TemplateCard.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    filters: { q: string; category: string };
    categories: Array<{ slug: string; name: string }>;
    templates: { data: Array<any>; meta: { current_page: number; last_page: number; total: number } };
}>();

const filters = reactive({ ...props.filters });

function apply() {
    router.get('/templates', { ...filters }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Templates" />
    <MarketingLayout>
        <div class="s1w0rxx4">
            <nav class="syp5vk7"><a href="/">Home</a> / Templates</nav>
            <h1 class="s129hsln">Templates</h1>
            <p class="szvr2j3">Digital downloads with a real file behind each published product. Payment is confirmed before the download is available.</p>
            <form class="s8emdm2" @submit.prevent="apply">
                <SearchInput v-model="filters.q" label="Search" />
                <CategoryFilter v-model="filters.category" :categories="categories" />
                <Button class="sjp0jbu" type="submit">Apply</Button>
            </form>
            <EmptyState v-if="templates.data.length === 0" class="s200pe" title="Nothing in this category yet" message="Published downloads will show up here. Empty categories are left empty on purpose." />
            <div v-else class="s147ntv0">
                <TemplateCard v-for="template in templates.data" :key="template.slug" :template="template" />
            </div>
            <Pagination class="s200pe" :meta="templates.meta" path="/templates" :query="filters" />
        </div>
    </MarketingLayout>
</template>

<style scoped>
.s1w0rxx4 {
    @apply mx-auto max-w-6xl px-4 py-12;
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}

.s129hsln {
    @apply mt-3 text-3xl font-semibold;
}

.szvr2j3 {
    @apply mt-2 max-w-2xl text-slate-600;
}

.s8emdm2 {
    @apply mt-6 grid gap-4 md:grid-cols-3;
}

.sjp0jbu {
    @apply self-end;
}

.s200pe {
    @apply mt-8;
}

.s147ntv0 {
    @apply mt-8 grid gap-4 md:grid-cols-3;
}
</style>
