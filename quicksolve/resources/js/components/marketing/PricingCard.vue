<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';

defineProps<{
    plan: {
        key: string;
        name: string;
        formatted_price: string;
        description: string;
        features: string[];
        checkout?: boolean;
    };
}>();

const form = useForm({ plan: 'pro' });

function subscribe(plan: string) {
    form.plan = plan;
    form.post('/billing/checkout');
}
</script>

<template>
    <article class="sddfvcl">
        <h3 class="s1cmvr70">{{ plan.name }}</h3>
        <p class="s1s4e9ks">{{ plan.formatted_price }}<span v-if="plan.key !== 'free'" class="sjhynpn">/month</span></p>
        <p class="s92vpu7">{{ plan.description }}</p>
        <ul class="ss0oon9">
            <li v-for="feature in plan.features" :key="feature">{{ feature }}</li>
        </ul>
        <Button v-if="plan.checkout" class="s200pc" :disabled="form.processing" @click="subscribe(plan.key)">Continue to checkout</Button>
        <Button v-else as-child class="s200pc" variant="outline">
            <Link href="/register">Create a free account</Link>
        </Button>
    </article>
</template>

<style scoped>
.sddfvcl {
    @apply flex h-full flex-col rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900;
}

.s1cmvr70 {
    @apply text-lg font-semibold;
}

.s1s4e9ks {
    @apply mt-2 text-3xl font-semibold tracking-tight;
}

.sjhynpn {
    @apply text-base font-normal text-slate-500;
}

.s92vpu7 {
    @apply mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300;
}

.ss0oon9 {
    @apply mt-4 flex-1 space-y-2 text-sm text-slate-700 dark:text-slate-200;
}

.s200pc {
    @apply mt-6;
}
</style>
