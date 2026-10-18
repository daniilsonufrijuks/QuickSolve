<script setup lang="ts">
import EmptyState from '@/components/marketing/EmptyState.vue';
import SearchInput from '@/components/marketing/SearchInput.vue';
import ToolCard from '@/components/marketing/ToolCard.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';

const props = defineProps<{
    filters: { q: string; category: string; access: string; sort: string };
    categories: Array<{ slug: string; name: string; description: string }>;
    sections: Array<{
        slug: string;
        name: string;
        description: string;
        tools: Array<{
            name: string;
            slug: string;
            description: string;
            access_type: string;
            category?: { name?: string | null };
        }>;
    }>;
}>();

const filters = reactive({ ...props.filters });

watch(
    () => props.filters,
    (value) => {
        filters.q = value.q;
        filters.category = value.category;
        filters.access = value.access;
        filters.sort = value.sort;
    },
);

const browsingAll = computed(() => props.filters.q === '' && props.filters.category === '' && props.filters.access === '');
const activeCategory = computed(() => props.categories.find((category) => category.slug === props.filters.category));

function apply() {
    router.get('/tools', { ...filters }, { preserveState: true, replace: true });
}

function categoryHref(slug: string): string {
    const params = new URLSearchParams();

    if (props.filters.q) {
        params.set('q', props.filters.q);
    }

    if (props.filters.access) {
        params.set('access', props.filters.access);
    }

    if (props.filters.sort && props.filters.sort !== 'popularity') {
        params.set('sort', props.filters.sort);
    }

    if (slug) {
        params.set('category', slug);
    }

    const query = params.toString();

    return query === '' ? '/tools' : `/tools?${query}`;
}
</script>

<template>
    <MarketingLayout>
        <div class="page">
            <nav class="crumbs" aria-label="Breadcrumb">
                <Link href="/">Home</Link>
                <span aria-hidden="true"> / </span>
                <span>Tools</span>
            </nav>
            <h1 class="title">Tools</h1>
            <p class="intro">
                <template v-if="activeCategory">{{ activeCategory.description }}</template>
                <template v-else>Pick a category, or search by name. Free tools stay open without an account.</template>
            </p>

            <nav class="chips" aria-label="Tool categories">
                <a v-if="browsingAll" href="#tools-catalog" class="chip on">All</a>
                <Link v-else :href="categoryHref('')" class="chip" :class="filters.category === '' ? 'on' : ''" :aria-current="filters.category === '' ? 'page' : undefined">All</Link>
                <template v-for="category in categories" :key="category.slug">
                    <a v-if="browsingAll" :href="`#cat-${category.slug}`" class="chip">{{ category.name }}</a>
                    <Link v-else :href="categoryHref(category.slug)" class="chip" :class="filters.category === category.slug ? 'on' : ''" :aria-current="filters.category === category.slug ? 'page' : undefined">
                        {{ category.name }}
                    </Link>
                </template>
            </nav>

            <form class="filters" @submit.prevent="apply">
                <SearchInput v-model="filters.q" label="Search" placeholder="Name or description" />
                <label class="field">
                    <span>Access</span>
                    <select v-model="filters.access">
                        <option value="">Free and Pro</option>
                        <option value="free">Free</option>
                        <option value="premium">Pro</option>
                    </select>
                </label>
                <label class="field">
                    <span>Sort</span>
                    <select v-model="filters.sort">
                        <option value="popularity">Popularity</option>
                        <option value="name">Name</option>
                    </select>
                </label>
                <Button class="apply" type="submit">Apply filters</Button>
            </form>

            <EmptyState v-if="sections.length === 0" class="gap" title="No tools match" message="Try another category or clear the search." />
            <div v-else id="tools-catalog" class="sections">
                <section v-for="section in sections" :id="`cat-${section.slug}`" :key="section.slug" class="section">
                    <div class="section-head">
                        <div>
                            <h2>{{ section.name }}</h2>
                            <p>{{ section.description }}</p>
                        </div>
                        <Link v-if="browsingAll" :href="categoryHref(section.slug)" class="only">Only this category</Link>
                    </div>
                    <div class="cards">
                        <ToolCard v-for="tool in section.tools" :key="tool.slug" :tool="tool" />
                    </div>
                </section>
            </div>
        </div>
    </MarketingLayout>
</template>

<style scoped>
.page {
    @apply mx-auto max-w-6xl px-4 py-12;
}

.crumbs {
    @apply text-sm text-slate-500;
}

.crumbs a {
    @apply hover:text-blue-700;
}

.title {
    @apply mt-3 text-3xl font-semibold text-slate-950 dark:text-white;
}

.intro {
    @apply mt-2 max-w-2xl text-slate-600 dark:text-slate-300;
}

.chips {
    @apply sticky top-20 z-20 -mx-4 mt-6 flex gap-2 overflow-x-auto border-y border-slate-200 bg-slate-50/95 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95;
}

.chip {
    @apply shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-700 hover:border-blue-300 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200;
}

.chip.on {
    @apply border-blue-700 bg-blue-700 text-white dark:border-blue-500 dark:bg-blue-600;
}

.filters {
    @apply mt-6 grid gap-4 md:grid-cols-4;
}

.field {
    @apply text-sm;
}

.field span {
    @apply mb-1 block font-medium text-slate-700 dark:text-slate-200;
}

.field select {
    @apply w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900 dark:text-white;
}

.apply {
    @apply md:col-span-4 md:w-fit;
}

.gap {
    @apply mt-8;
}

.sections {
    @apply mt-10 space-y-12;
}

.section {
    @apply scroll-mt-36;
}

.section-head {
    @apply flex flex-wrap items-end justify-between gap-3;
}

.section-head h2 {
    @apply text-2xl font-semibold text-slate-950 dark:text-white;
}

.section-head p {
    @apply mt-1 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300;
}

.only {
    @apply text-sm font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300;
}

.cards {
    @apply mt-4 grid gap-4 md:grid-cols-3;
}
</style>
