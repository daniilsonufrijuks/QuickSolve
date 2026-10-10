<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{ subscriptions: { data: Array<any> } }>();
</script>

<template>
    <Head title="Subscriptions" />
    <AppLayout :breadcrumbs="[{ title: 'Subscriptions', href: '/admin/subscriptions' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Subscriptions</h1>
            <table class="table">
                <thead>
                    <tr class="row">
                        <th class="cell">Account</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Ends</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="row">
                        <td class="cell">{{ subscription.user?.email }}</td>
                        <td>{{ subscription.stripe_status }}</td>
                        <td>{{ subscription.stripe_price }}</td>
                        <td>{{ subscription.ends_at ?? '—' }}</td>
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
