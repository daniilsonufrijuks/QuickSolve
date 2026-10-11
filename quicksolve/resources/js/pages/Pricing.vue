<script setup lang="ts">
import PricingCard from '@/components/marketing/PricingCard.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
defineProps<{
    plans: Array<any>;
    billingNote: string;
}>();
</script>

<template>
    <MarketingLayout>
        <div class="s1w0rxx4">
            <h1 class="s15bg7bs">Pricing</h1>
            <p class="s10e7xke">{{ billingNote }}</p>
            <div class="sy0lrrk">
                <PricingCard v-for="plan in plans" :key="plan.key" :plan="{ ...plan, checkout: plan.checkout }" />
            </div>
            <p class="s1imv3vj">Template purchases use the price stored for that product. After Stripe checkout the account asks Stripe for the session, so a paid template should move from pending to paid even when a local webhook listener is not running. Free accounts also get 5 trial uses of Pro tools.</p>
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
