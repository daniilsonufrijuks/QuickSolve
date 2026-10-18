<script setup lang="ts">
import type { SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';

withDefaults(defineProps<{
    layout?: 'bar' | 'stack';
}>(), {
    layout: 'bar',
});

const page = usePage<SharedData>();
const open = ref(false);

const items = [
    { title: 'Templates', href: '/templates' },
    { title: 'Pricing', href: '/pricing' },
    { title: 'About', href: '/about' },
    { title: 'Contact', href: '/contact' },
];

const toolMenu = computed(() => page.props.toolMenu ?? []);
const path = computed(() => page.url.split('?')[0] ?? '');
const categoryQuery = computed(() => new URLSearchParams(page.url.split('?')[1] ?? '').get('category') ?? '');

function active(href: string) {
    return path.value === href || path.value.startsWith(`${href}/`);
}

function onKey(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        open.value = false;
    }
}

watch(open, (isOpen) => {
    if (isOpen) {
        window.addEventListener('keydown', onKey);
    } else {
        window.removeEventListener('keydown', onKey);
    }
});

const stopNavigate = router.on('navigate', () => {
    open.value = false;
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKey);

    if (typeof stopNavigate === 'function') {
        stopNavigate();
    }
});

function closeIfLink(event: MouseEvent) {
    const target = event.target;

    if (target instanceof Element && target.closest('a')) {
        open.value = false;
    }
}
</script>

<template>
    <nav :aria-label="layout === 'bar' ? 'Primary' : undefined">
        <ul :class="layout === 'bar' ? 'bar' : 'stack'">
            <li class="tools">
                <div class="tools-label">
                    <Link href="/tools" class="link" :class="active('/tools') ? 'text-blue-700 dark:text-blue-300' : ''" :aria-current="active('/tools') ? 'page' : undefined">
                        Tools
                    </Link>
                    <button
                        v-if="layout === 'bar' && toolMenu.length"
                        class="chevron"
                        type="button"
                        :aria-expanded="open"
                        aria-controls="tool-category-menu"
                        @click="open = !open"
                    >
                        <ChevronDown class="size-4 transition" :class="open ? 'rotate-180' : ''" />
                        <span class="sr-only">Tool categories</span>
                    </button>
                </div>

                <template v-if="layout === 'bar' && open">
                    <button class="backdrop" type="button" aria-label="Close tool menu" @click="open = false" />
                    <div id="tool-category-menu" class="panel" @click="closeIfLink">
                        <div class="panel-inner">
                            <div class="panel-head">
                                <p>Browse tools by category</p>
                                <Link href="/tools" class="all">All tools</Link>
                            </div>
                            <div class="columns">
                                <section v-for="category in toolMenu" :key="category.slug">
                                    <Link :href="`/tools?category=${category.slug}`" class="category" :class="categoryQuery === category.slug ? 'text-blue-700 dark:text-blue-300' : ''">
                                        {{ category.name }}
                                    </Link>
                                    <ul class="tool-list">
                                        <li v-for="tool in category.tools" :key="tool.slug">
                                            <Link :href="`/tools/${tool.slug}`" class="tool" :aria-current="path === `/tools/${tool.slug}` ? 'page' : undefined">
                                                <span>{{ tool.name }}</span>
                                                <span v-if="tool.access_type === 'premium'" class="pro">Pro</span>
                                            </Link>
                                        </li>
                                    </ul>
                                </section>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-if="layout === 'stack'" class="stack-tools">
                    <Link href="/tools" class="stack-link">All tools</Link>
                    <details v-for="category in toolMenu" :key="category.slug" class="stack-category">
                        <summary>{{ category.name }}</summary>
                        <Link :href="`/tools?category=${category.slug}`" class="stack-tool">Everything in {{ category.name }}</Link>
                        <Link v-for="tool in category.tools" :key="tool.slug" :href="`/tools/${tool.slug}`" class="stack-tool">
                            {{ tool.name }}
                        </Link>
                    </details>
                </div>
            </li>
            <li v-for="item in items" :key="item.href">
                <Link
                    :href="item.href"
                    class="link"
                    :class="active(item.href) ? 'text-blue-700 dark:text-blue-300' : ''"
                    :aria-current="active(item.href) ? 'page' : undefined"
                >
                    {{ item.title }}
                </Link>
            </li>
        </ul>
    </nav>
</template>

<style scoped>
.bar {
    @apply flex items-center gap-6;
}

.stack {
    @apply flex flex-col gap-1;
}

.tools {
    @apply static;
}

.tools-label {
    @apply relative z-50 flex items-center gap-0.5;
}

.link {
    @apply block rounded-md px-2 py-2 text-sm font-medium text-slate-700 hover:text-blue-700 md:px-0 md:py-0 dark:text-slate-200;
}

.chevron {
    @apply rounded-md p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white;
}

.backdrop {
    @apply absolute left-0 right-0 top-full z-40 h-screen w-full cursor-default bg-slate-950/20;
}

.panel {
    @apply absolute left-0 right-0 top-full z-50 border-b border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-950;
}

.panel-inner {
    @apply mx-auto max-w-6xl px-4 py-5;
}

.panel-head {
    @apply mb-4 flex items-center justify-between gap-4 text-sm text-slate-500;
}

.all {
    @apply font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300;
}

.columns {
    @apply grid max-h-[min(70vh,36rem)] gap-6 overflow-auto sm:grid-cols-2 lg:grid-cols-4;
}

.category {
    @apply text-sm font-semibold text-slate-950 hover:text-blue-700 dark:text-white;
}

.tool-list {
    @apply mt-2 space-y-0.5;
}

.tool {
    @apply flex items-start justify-between gap-2 rounded-md px-2 py-1 text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white;
}

.pro {
    @apply shrink-0 rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-blue-800;
}

.stack-tools {
    @apply mt-1 space-y-3 border-l border-slate-200 pl-3 dark:border-slate-800;
}

.stack-link,
.stack-category-link,
.stack-tool {
    @apply block py-1 text-sm text-slate-700 hover:text-blue-700 dark:text-slate-200;
}

.stack-category summary {
    @apply cursor-pointer py-1 text-sm font-semibold text-slate-950 dark:text-white;
}

.stack-tool {
    @apply pl-2 text-slate-600 dark:text-slate-300;
}

.sr-only {
    @apply absolute h-px w-px overflow-hidden whitespace-nowrap border-0 p-0;
    clip: rect(0, 0, 0, 0);
}
</style>
