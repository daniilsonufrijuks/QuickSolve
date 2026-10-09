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
        <div class="space-y-6 p-4">
            <div class="grid gap-4 md:grid-cols-3">
                <section class="rounded-xl border p-4">
                    <p class="text-sm text-slate-500">Plan</p>
                    <p class="mt-1 text-2xl font-semibold">{{ plan.name }}</p>
                    <Button as-child class="mt-3" variant="outline"><Link href="/dashboard/subscription">Manage</Link></Button>
                </section>
                <section class="rounded-xl border p-4">
                    <p class="text-sm text-slate-500">Descriptions this {{ usage.window }}</p>
                    <p class="mt-1 text-2xl font-semibold">{{ usage.used }} / {{ usage.limit }}</p>
                </section>
                <section class="rounded-xl border p-4">
                    <p class="text-sm text-slate-500">Paid downloads</p>
                    <p class="mt-1 text-2xl font-semibold">{{ counts.purchases }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ counts.documents }} saved invoices</p>
                </section>
            </div>
            <section>
                <h2 class="font-semibold">Recent purchases</h2>
                <ul class="mt-2 space-y-2 text-sm">
                    <li v-for="purchase in purchases" :key="purchase.id">{{ purchase.template?.name }} — {{ purchase.formatted_amount }} ({{ purchase.status }})</li>
                    <li v-if="purchases.length === 0" class="text-slate-500">No purchases yet.</li>
                </ul>
            </section>
            <section>
                <h2 class="font-semibold">Recent invoices</h2>
                <ul class="mt-2 space-y-2 text-sm">
                    <li v-for="document in documents" :key="document.id">{{ document.title }}</li>
                    <li v-if="documents.length === 0" class="text-slate-500">No saved invoices yet.</li>
                </ul>
            </section>
        </div>
    </AppLayout>
</template>
