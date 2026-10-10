<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const links = [
    ['Overview', '/admin'],
    ['Billing', '/admin/billing'],
    ['Users', '/admin/users'],
    ['Tools', '/admin/tools'],
    ['Categories', '/admin/categories'],
    ['Templates', '/admin/templates'],
    ['Purchases', '/admin/purchases'],
    ['Subscriptions', '/admin/subscriptions'],
    ['Messages', '/admin/contacts'],
    ['Usage', '/admin/usage'],
];

const page = usePage();
</script>

<template>
    <nav class="nav" aria-label="Administration">
        <Link v-for="[label, href] in links" :key="href" :href="href" class="link" :class="{ active: page.url === href || (href !== '/admin' && page.url.startsWith(href)) }">
            {{ label }}
        </Link>
    </nav>
</template>

<style scoped>
.nav {
    @apply mb-6 flex flex-wrap gap-2;
}

.link {
    @apply rounded-full border border-slate-200 px-3 py-1 text-sm text-slate-800 hover:border-blue-300 dark:border-slate-700 dark:text-slate-100;
}

.active {
    @apply border-blue-600 bg-blue-50 text-blue-900 dark:bg-blue-950 dark:text-blue-100;
}
</style>
