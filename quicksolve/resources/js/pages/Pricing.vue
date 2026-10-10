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
        <div class="s1w0rxx4">
            <h1 class="s15bg7bs">Pricing</h1>
            <p class="s10e7xke">{{ billingNote }}</p>
            <div class="sy0lrrk">
                <PricingCard v-for="plan in plans" :key="plan.key" :plan="{ ...plan, checkout: plan.checkout }" />
            </div>
            <p class="s1imv3vj">Template purchases use the price stored for that product. A success page after checkout does not unlock Pro or a download by itself. Stripe webhooks update the account.</p>
        </div>
    </MarketingLayout>
</template>

<style scoped>
.s1w0rxx4 {
    @apply mx-auto max-w-6xl px-4 py-12;
}

.s15bg7bs {
    @apply text-3xl font-semibold;
}

.s10e7xke {
    @apply mt-3 max-w-2xl text-slate-600;
}

.sy0lrrk {
    @apply mt-8 grid gap-4 lg:grid-cols-3;
}

.s1imv3vj {
    @apply mt-8 max-w-3xl text-sm leading-6 text-slate-600;
}
</style>
