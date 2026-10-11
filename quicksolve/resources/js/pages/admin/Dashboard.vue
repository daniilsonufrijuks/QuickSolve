<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import BarChart from '@/components/admin/BarChart.vue';
import HBarChart from '@/components/admin/HBarChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: {
        totals: Record<string, number>;
        activity: Array<Record<string, string | number>>;
        purchase_status: Array<{ label: string; value: number }>;
        top_tools: Array<{ label: string; value: number }>;
        revenue: Array<Record<string, string | number>>;
    };
    billing: {
        configured: boolean;
        pro_price_id: string | null;
        business_price_id: string | null;
    };
}>();

function euros(cents: number) {
    return `€${(cents / 100).toFixed(2)}`;
}
</script>

<template>
    <Head title="Admin" />
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Administration</h1>
            <div class="cards">
                <div v-for="(value, key) in stats.totals" :key="key" class="card">
                    <p class="label">{{ String(key).replaceAll('_', ' ') }}</p>
                    <p class="figure">{{ key === 'revenue_paid' ? euros(value) : value }}</p>
                </div>
            </div>
            <div class="charts">
                <section class="card">
                    <h2 class="heading">Activity, 14 days</h2>
                    <BarChart
                        :series="stats.activity"
                        :keys="[
                            { name: 'signups', color: '#2563eb' },
                            { name: 'events', color: '#0f766e' },
                            { name: 'purchases', color: '#c2410c' },
                        ]"
                    />
                </section>
                <section class="card">
                    <h2 class="heading">Paid template revenue, 14 days</h2>
                    <BarChart :series="stats.revenue" :keys="[{ name: 'paid', color: '#7c3aed' }]" />
                    <p class="copy">Values are cents stored for each paid purchase.</p>
                </section>
                <section class="card">
                    <h2 class="heading">Purchases by status</h2>
                    <HBarChart :items="stats.purchase_status" color="#c2410c" />
                </section>
                <section class="card">
                    <h2 class="heading">Tool opens, 30 days</h2>
                    <HBarChart :items="stats.top_tools" />
                </section>
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
    @apply grid gap-3 sm:grid-cols-3 xl:grid-cols-4;
}

.charts {
    @apply grid gap-4 lg:grid-cols-2;
}

.card {
    @apply space-y-3 rounded-xl border p-4;
}

.label {
    @apply text-sm capitalize text-slate-600 dark:text-slate-300;
}

.figure,
.heading {
    @apply text-xl font-semibold;
}

.copy,
.link {
    @apply text-sm;
}

.link {
    @apply text-blue-800 underline dark:text-blue-200;
}
</style>
