<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{ subscriptions: { data: Array<any> } }>();
</script>

<template>
    <Head title="Subscriptions" />
    <AppLayout :breadcrumbs="[{ title: 'Subscriptions', href: '/admin/subscriptions' }]">
        <div class="p-4">
            <h1 class="text-xl font-semibold">Subscriptions</h1>
            <table class="mt-4 w-full text-left text-sm">
                <thead><tr class="border-b"><th class="py-2">Account</th><th>Status</th><th>Price</th><th>Ends</th></tr></thead>
                <tbody>
                    <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="border-b">
                        <td class="py-2">{{ subscription.user?.email }}</td>
                        <td>{{ subscription.stripe_status }}</td>
                        <td>{{ subscription.stripe_price }}</td>
                        <td>{{ subscription.ends_at ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
