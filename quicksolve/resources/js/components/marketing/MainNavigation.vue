<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const items = [
    { title: 'Tools', href: '/tools' },
    { title: 'Templates', href: '/templates' },
    { title: 'Pricing', href: '/pricing' },
    { title: 'About', href: '/about' },
    { title: 'Contact', href: '/contact' },
];

function active(href: string) {
    return page.url === href || page.url.startsWith(`${href}/`);
}
</script>

<template>
    <nav aria-label="Primary">
        <ul class="flex flex-col gap-1 md:flex-row md:items-center md:gap-6">
            <li v-for="item in items" :key="item.href">
                <Link
                    :href="item.href"
                    class="block rounded-md px-2 py-2 text-sm font-medium text-slate-700 hover:text-blue-700 md:px-0 md:py-0 dark:text-slate-200"
                    :class="active(item.href) ? 'text-blue-700 dark:text-blue-300' : ''"
                    :aria-current="active(item.href) ? 'page' : undefined"
                >
                    {{ item.title }}
                </Link>
            </li>
        </ul>
    </nav>
</template>
