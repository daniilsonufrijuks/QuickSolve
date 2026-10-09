<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: Record<string, number>;
}>();

const links = [
    ['Tools', '/admin/tools'],
    ['Categories', '/admin/categories'],
    ['Templates', '/admin/templates'],
    ['Purchases', '/admin/purchases'],
    ['Subscriptions', '/admin/subscriptions'],
    ['Usage', '/admin/usage'],
];
</script>

<template>
    <Head title="Admin" />
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }]">
        <div class="space-y-6 p-4">
            <h1 class="text-2xl font-semibold">Administration</h1>
            <div class="grid gap-3 sm:grid-cols-3">
                <div v-for="(value, key) in stats" :key="key" class="rounded-xl border p-4">
                    <p class="text-sm capitalize text-slate-500">{{ String(key).replaceAll('_', ' ') }}</p>
                    <p class="text-2xl font-semibold">{{ value }}</p>
                </div>
            </div>
            <ul class="flex flex-wrap gap-3 text-sm">
                <li v-for="[label, href] in links" :key="href"><Link :href="href" class="text-blue-700">{{ label }}</Link></li>
            </ul>
        </div>
    </AppLayout>
</template>
