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
        <div class="s1w0rxx4">
            <nav class="syp5vk7" aria-label="Breadcrumb">
                <a href="/" class="s1d2oiue">Home</a>
                <span aria-hidden="true"> / </span>
                <span>Tools</span>
            </nav>
            <h1 class="s129hsln">Tools</h1>
            <p class="szvr2j3">Search the catalog. Results come from the database, including category and free or premium filters.</p>
            <form class="s8emdm3" @submit.prevent="apply">
                <SearchInput v-model="filters.q" label="Search" placeholder="Name or description" />
                <CategoryFilter v-model="filters.category" :categories="categories" />
                <label class="s1bkxu62">
                    <span class="s1ms3ozd">Access</span>
                    <select v-model="filters.access" class="s1wl1yqe">
                        <option value="">Free and premium</option>
                        <option value="free">Free</option>
                        <option value="premium">Premium</option>
                    </select>
                </label>
                <label class="s1bkxu62">
                    <span class="s1ms3ozd">Sort</span>
                    <select v-model="filters.sort" class="s1wl1yqe">
                        <option value="popularity">Popularity</option>
                        <option value="name">Name</option>
                    </select>
                </label>
                <Button class="s17yx0dz" type="submit">Apply filters</Button>
            </form>
            <EmptyState v-if="tools.data.length === 0" class="s200pe" title="No tools match" message="Try another category or clear the premium filter. Some categories are still waiting for a published tool." />
            <div v-else class="s147ntv0">
                <ToolCard v-for="tool in tools.data" :key="tool.slug" :tool="tool" />
            </div>
            <Pagination class="s200pe" :meta="tools.meta" path="/tools" :query="filters" />
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

.s1d2oiue {
    @apply hover:text-blue-700;
}

.s129hsln {
    @apply mt-3 text-3xl font-semibold;
}

.szvr2j3 {
    @apply mt-2 max-w-2xl text-slate-600;
}

.s8emdm3 {
    @apply mt-6 grid gap-4 md:grid-cols-4;
}

.s1bkxu62 {
    @apply text-sm;
}

.s1ms3ozd {
    @apply mb-1 block font-medium;
}

.s1wl1yqe {
    @apply w-full rounded-lg border px-3 py-2;
}

.s17yx0dz {
    @apply md:col-span-4 md:w-fit;
}

.s200pe {
    @apply mt-8;
}

.s147ntv0 {
    @apply mt-8 grid gap-4 md:grid-cols-3;
}
</style>
