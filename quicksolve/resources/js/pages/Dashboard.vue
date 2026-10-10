<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    plan: { name: string; key: string };
    usage: { used: number; limit: number; remaining: number; window: string };
    purchases: Array<{ id: number; formatted_amount: string; status: string; template?: { name?: string } }>;
    documents: Array<{ id: number; title: string }>;
    counts: { purchases: number; documents: number };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="sx99o2a">
            <div class="swmc7i2">
                <section class="s1rk4jvz">
                    <p class="syp5vk7">Plan</p>
                    <p class="si3k1n0">{{ plan.name }}</p>
                    <Button as-child class="s200p9" variant="outline"><Link href="/dashboard/subscription">Manage</Link></Button>
                </section>
                <section class="s1rk4jvz">
                    <p class="syp5vk7">Descriptions this {{ usage.window }}</p>
                    <p class="si3k1n0">{{ usage.used }} / {{ usage.limit }}</p>
                </section>
                <section class="s1rk4jvz">
                    <p class="syp5vk7">Template downloads</p>
                    <p class="si3k1n0">{{ counts.purchases }}</p>
                    <p class="swr3obg">{{ counts.documents }} saved invoices</p>
                </section>
            </div>
            <section>
                <h2 class="sreg0xd">Recent purchases</h2>
                <ul class="sa19kgt">
                    <li v-for="purchase in purchases" :key="purchase.id">{{ purchase.template?.name }} — {{ purchase.formatted_amount }} ({{ purchase.status }})</li>
                    <li v-if="purchases.length === 0" class="s1frj40x">No purchases yet.</li>
                </ul>
            </section>
            <section>
                <h2 class="sreg0xd">Recent invoices</h2>
                <ul class="sa19kgt">
                    <li v-for="document in documents" :key="document.id">{{ document.title }}</li>
                    <li v-if="documents.length === 0" class="s1frj40x">No saved invoices yet.</li>
                </ul>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.sx99o2a {
    @apply space-y-6 p-4;
}

.swmc7i2 {
    @apply grid gap-4 md:grid-cols-3;
}

.s1rk4jvz {
    @apply rounded-xl border p-4;
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}

.si3k1n0 {
    @apply mt-1 text-2xl font-semibold;
}

.s200p9 {
    @apply mt-3;
}

.swr3obg {
    @apply mt-2 text-sm text-slate-600;
}

.sreg0xd {
    @apply font-semibold;
}

.sa19kgt {
    @apply mt-2 space-y-2 text-sm;
}

.s1frj40x {
    @apply text-slate-500;
}
</style>
