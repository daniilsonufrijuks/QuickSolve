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
    <article class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h3 class="text-lg font-semibold">{{ plan.name }}</h3>
        <p class="mt-2 text-3xl font-semibold tracking-tight">{{ plan.formatted_price }}<span v-if="plan.key !== 'free'" class="text-base font-normal text-slate-500">/month</span></p>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ plan.description }}</p>
        <ul class="mt-4 flex-1 space-y-2 text-sm text-slate-700 dark:text-slate-200">
            <li v-for="feature in plan.features" :key="feature">{{ feature }}</li>
        </ul>
        <Button v-if="plan.checkout" class="mt-6" :disabled="form.processing" @click="subscribe(plan.key)">Continue to checkout</Button>
        <Button v-else as-child class="mt-6" variant="outline">
            <Link href="/register">Create a free account</Link>
        </Button>
    </article>
</template>
