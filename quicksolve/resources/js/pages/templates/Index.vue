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
        <div class="mx-auto max-w-6xl px-4 py-12">
            <nav class="text-sm text-slate-500"><a href="/">Home</a> / Templates</nav>
            <h1 class="mt-3 text-3xl font-semibold">Templates</h1>
            <p class="mt-2 max-w-2xl text-slate-600">Digital downloads with a real file behind each published product. Payment is confirmed before the download is available.</p>
            <form class="mt-6 grid gap-4 md:grid-cols-3" @submit.prevent="apply">
                <SearchInput v-model="filters.q" label="Search" />
                <CategoryFilter v-model="filters.category" :categories="categories" />
                <Button class="self-end" type="submit">Apply</Button>
            </form>
            <EmptyState v-if="templates.data.length === 0" class="mt-8" title="Nothing in this category yet" message="Published downloads will show up here. Empty categories are left empty on purpose." />
            <div v-else class="mt-8 grid gap-4 md:grid-cols-3">
                <TemplateCard v-for="template in templates.data" :key="template.slug" :template="template" />
            </div>
            <Pagination class="mt-8" :meta="templates.meta" path="/templates" :query="filters" />
        </div>
    </MarketingLayout>
</template>
