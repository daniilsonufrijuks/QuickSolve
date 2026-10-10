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
    <footer class="s37sb22">
        <div class="sjwrw36">
            <div>
                <p class="s1pefx18">QuickSolve</p>
                <p class="sgwv9i7">Small tools. Smarter business.</p>
            </div>
            <div v-for="group in groups" :key="group.title">
                <p class="s15j7uqq">{{ group.title }}</p>
                <ul class="s14qtbyc">
                    <li v-for="link in group.links" :key="link.href">
                        <Link :href="link.href" class="srddxch">{{ link.title }}</Link>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.s37sb22 {
    @apply border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950;
}

.sjwrw36 {
    @apply mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-2 md:grid-cols-4;
}

.s1pefx18 {
    @apply font-semibold text-slate-950 dark:text-white;
}

.sgwv9i7 {
    @apply mt-2 text-sm text-slate-600 dark:text-slate-300;
}

.s15j7uqq {
    @apply text-sm font-semibold text-slate-950 dark:text-white;
}

.s14qtbyc {
    @apply mt-3 space-y-2;
}

.srddxch {
    @apply text-sm text-slate-600 hover:text-blue-700 dark:text-slate-300;
}
</style>
