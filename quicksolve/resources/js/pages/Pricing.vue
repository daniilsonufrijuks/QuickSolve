<script setup lang="ts">
import PricingCard from '@/components/marketing/PricingCard.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    plans: Array<any>;
    billingNote: string;
}>();
</script>

<template>
    <Head title="Pricing" />
    <MarketingLayout>
        <div class="mx-auto max-w-6xl px-4 py-12">
            <h1 class="text-3xl font-semibold">Pricing</h1>
            <p class="mt-3 max-w-2xl text-slate-600">{{ billingNote }}</p>
            <div class="mt-8 grid gap-4 lg:grid-cols-3">
                <PricingCard v-for="plan in plans" :key="plan.key" :plan="{ ...plan, checkout: plan.checkout }" />
            </div>
            <p class="mt-8 max-w-3xl text-sm leading-6 text-slate-600">Template purchases use the price stored for that product. A success page after checkout does not unlock Pro or a download by itself. Stripe webhooks update the account.</p>
        </div>
    </MarketingLayout>
</template>
