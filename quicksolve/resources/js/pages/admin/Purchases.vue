<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps<{ purchases: { data: Array<any> } }>();
</script>

<template>
    <Head title="Purchase records" />
    <AppLayout :breadcrumbs="[{ title: 'Purchases', href: '/admin/purchases' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Purchases</h1>
            <table class="table">
                <thead>
                    <tr class="row">
                        <th class="cell">Buyer</th>
                        <th>Template</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="purchase in purchases.data" :key="purchase.id" class="row">
                        <td class="cell">{{ purchase.user?.email }}</td>
                        <td>{{ purchase.template?.name }}</td>
                        <td>{{ purchase.amount }} {{ purchase.currency }}</td>
                        <td>{{ purchase.status }}</td>
                        <td>
                            <Button v-if="purchase.status === 'paid'" type="button" variant="outline" @click="router.post(`/admin/purchases/${purchase.id}/refund`)">
                                Mark refunded
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply p-4;
}

.title {
    @apply text-xl font-semibold;
}

.table {
    @apply mt-4 w-full text-left text-sm;
}

.row {
    @apply border-b;
}

.cell {
    @apply py-2;
}
</style>
