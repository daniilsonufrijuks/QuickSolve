<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const groups = [
    {
        title: 'Product',
        links: [
            { title: 'Tools', href: '/tools' },
            { title: 'Templates', href: '/templates' },
            { title: 'Pricing', href: '/pricing' },
        ],
    },
    {
        title: 'Support',
        links: [
            { title: 'About', href: '/about' },
            { title: 'Contact', href: '/contact' },
        ],
    },
    {
        title: 'Legal',
        links: [
            { title: 'Privacy', href: '/privacy' },
            { title: 'Terms', href: '/terms' },
        ],
    },
];
</script>

<template>
    <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-2 md:grid-cols-4">
            <div>
                <p class="font-semibold text-slate-950 dark:text-white">QuickSolve</p>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Small tools. Smarter business.</p>
            </div>
            <div v-for="group in groups" :key="group.title">
                <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ group.title }}</p>
                <ul class="mt-3 space-y-2">
                    <li v-for="link in group.links" :key="link.href">
                        <Link :href="link.href" class="text-sm text-slate-600 hover:text-blue-700 dark:text-slate-300">{{ link.title }}</Link>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
</template>
