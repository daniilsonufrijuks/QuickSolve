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
        <div class="sx862vk">
            <h1 class="sixq2vr">Purchases</h1>
            <p class="syp5vk7">These are one-time template downloads. A Pro or Business subscription appears under Subscription, not here.</p>
            <p v-if="status === 'processing'" class="s1kmzj3r">If you paid for a template, the download appears after Stripe confirms the payment.</p>
            <ul class="sm6llz1">
                <li v-for="purchase in purchases.data" :key="purchase.id" class="s5rs950">
                    <span>{{ purchase.template?.name }}</span>
                    <span>{{ purchase.formatted_amount }} · {{ purchase.status }}</span>
                </li>
            </ul>
            <p v-if="purchases.data.length === 0" class="syp5vk7">No purchases yet.</p>
            <Pagination :meta="purchases.meta" path="/dashboard/purchases" />
        </div>
    </AppLayout>
</template>

<style scoped>
.sx862vk {
    @apply space-y-4 p-4;
}

.sixq2vr {
    @apply text-2xl font-semibold;
}

.s1kmzj3r {
    @apply rounded-lg bg-amber-50 p-3 text-sm text-amber-900;
}

.sm6llz1 {
    @apply divide-y rounded-xl border;
}

.s5rs950 {
    @apply flex items-center justify-between p-3 text-sm;
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}
</style>
