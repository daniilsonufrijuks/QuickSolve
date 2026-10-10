<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: Record<string, number>;
    billing: {
        configured: boolean;
        pro_price_id: string | null;
        business_price_id: string | null;
    };
}>();
</script>

<template>
    <Head title="Admin" />
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Administration</h1>
            <div class="cards">
                <div v-for="(value, key) in stats" :key="key" class="card">
                    <p class="label">{{ String(key).replaceAll('_', ' ') }}</p>
                    <p class="title">{{ value }}</p>
                </div>
            </div>
            <section class="card">
                <p class="label">Stripe prices</p>
                <p class="copy">
                    Secret {{ billing.configured ? 'is set' : 'is missing' }}.
                    Pro {{ billing.pro_price_id || 'needs a price' }}.
                    Business {{ billing.business_price_id || 'needs a price' }}.
                </p>
                <Link href="/admin/billing" class="link">Open billing</Link>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply space-y-6 p-4;
}

.title {
    @apply text-2xl font-semibold;
}

.cards {
    @apply grid gap-3 sm:grid-cols-3;
}

.card {
    @apply space-y-2 rounded-xl border p-4;
}

.label {
    @apply text-sm capitalize text-slate-600 dark:text-slate-300;
}

.copy,
.link {
    @apply text-sm;
}

.link {
    @apply text-blue-800 underline dark:text-blue-200;
}
</style>
