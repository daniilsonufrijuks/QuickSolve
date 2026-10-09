<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{ purchases: { data: Array<any> } }>();
</script>

<template>
    <Head title="Purchase records" />
    <AppLayout :breadcrumbs="[{ title: 'Purchases', href: '/admin/purchases' }]">
        <div class="p-4">
            <h1 class="text-xl font-semibold">Purchases</h1>
            <table class="mt-4 w-full text-left text-sm">
                <thead><tr class="border-b"><th class="py-2">Buyer</th><th>Template</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    <tr v-for="purchase in purchases.data" :key="purchase.id" class="border-b">
                        <td class="py-2">{{ purchase.user?.email }}</td>
                        <td>{{ purchase.template?.name }}</td>
                        <td>{{ purchase.amount }} {{ purchase.currency }}</td>
                        <td>{{ purchase.status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
