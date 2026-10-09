<script setup lang="ts">
import CategoryFilter from '@/components/marketing/CategoryFilter.vue';
import EmptyState from '@/components/marketing/EmptyState.vue';
import Pagination from '@/components/marketing/Pagination.vue';
import SearchInput from '@/components/marketing/SearchInput.vue';
import ToolCard from '@/components/marketing/ToolCard.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    filters: { q: string; category: string; access: string; sort: string };
    categories: Array<{ slug: string; name: string; description: string }>;
    tools: { data: Array<any>; meta: { current_page: number; last_page: number; total: number } };
}>();

const filters = reactive({ ...props.filters });

function apply() {
    router.get('/tools', { ...filters }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Tools">
        <meta name="description" content="Search QuickSolve tools by name, category, and free or premium access." />
    </Head>
    <MarketingLayout>
        <div class="mx-auto max-w-6xl px-4 py-12">
            <nav class="text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="/" class="hover:text-blue-700">Home</a>
                <span aria-hidden="true"> / </span>
                <span>Tools</span>
            </nav>
            <h1 class="mt-3 text-3xl font-semibold">Tools</h1>
            <p class="mt-2 max-w-2xl text-slate-600">Search the catalog. Results come from the database, including category and free or premium filters.</p>
            <form class="mt-6 grid gap-4 md:grid-cols-4" @submit.prevent="apply">
                <SearchInput v-model="filters.q" label="Search" placeholder="Name or description" />
                <CategoryFilter v-model="filters.category" :categories="categories" />
                <label class="text-sm">
                    <span class="mb-1 block font-medium">Access</span>
                    <select v-model="filters.access" class="w-full rounded-lg border px-3 py-2">
                        <option value="">Free and premium</option>
                        <option value="free">Free</option>
                        <option value="premium">Premium</option>
                    </select>
                </label>
                <label class="text-sm">
                    <span class="mb-1 block font-medium">Sort</span>
                    <select v-model="filters.sort" class="w-full rounded-lg border px-3 py-2">
                        <option value="popularity">Popularity</option>
                        <option value="name">Name</option>
                    </select>
                </label>
                <Button class="md:col-span-4 md:w-fit" type="submit">Apply filters</Button>
            </form>
            <EmptyState v-if="tools.data.length === 0" class="mt-8" title="No tools match" message="Try another category or clear the premium filter. Some categories are still waiting for a published tool." />
            <div v-else class="mt-8 grid gap-4 md:grid-cols-3">
                <ToolCard v-for="tool in tools.data" :key="tool.slug" :tool="tool" />
            </div>
            <Pagination class="mt-8" :meta="tools.meta" path="/tools" :query="filters" />
        </div>
    </MarketingLayout>
</template>
