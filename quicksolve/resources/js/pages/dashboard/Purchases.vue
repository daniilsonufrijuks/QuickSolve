<script setup lang="ts">
import Pagination from '@/components/marketing/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    purchases: { data: Array<{ id: number; formatted_amount: string; status: string; template?: { name?: string } }>; meta: { current_page: number; last_page: number } };
    status?: string;
}>();
</script>

<template>
    <Head title="Purchases" />
    <AppLayout :breadcrumbs="[{ title: 'Purchases', href: '/dashboard/purchases' }]">
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Purchases</h1>
            <p v-if="status === 'processing'" class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">If you paid, the download appears after the webhook marks the purchase as paid.</p>
            <ul class="divide-y rounded-xl border">
                <li v-for="purchase in purchases.data" :key="purchase.id" class="flex items-center justify-between p-3 text-sm">
                    <span>{{ purchase.template?.name }}</span>
                    <span>{{ purchase.formatted_amount }} · {{ purchase.status }}</span>
                </li>
            </ul>
            <p v-if="purchases.data.length === 0" class="text-sm text-slate-500">No purchases yet.</p>
            <Pagination :meta="purchases.meta" path="/dashboard/purchases" />
        </div>
    </AppLayout>
</template>
