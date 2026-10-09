<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    plan: { name: string; key: string };
    usage: { used: number; limit: number; window: string };
    subscription: { stripe_status: string; on_grace_period: boolean; ends_at: string | null; valid: boolean } | null;
    status?: string;
}>();

const cancel = useForm({});
const portal = useForm({});
</script>

<template>
    <Head title="Subscription" />
    <AppLayout :breadcrumbs="[{ title: 'Subscription', href: '/dashboard/subscription' }]">
        <div class="max-w-2xl space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Subscription</h1>
            <p v-if="status === 'processing'" class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Checkout returned to this page. The plan updates after Stripe sends a verified webhook. The return itself does not activate Pro.</p>
            <p>Current plan: <strong>{{ plan.name }}</strong></p>
            <p class="text-sm text-slate-600">{{ usage.used }} of {{ usage.limit }} descriptions used this {{ usage.window }}.</p>
            <p v-if="subscription" class="text-sm">Stripe status: {{ subscription.stripe_status }}<span v-if="subscription.on_grace_period"> · access until {{ subscription.ends_at }}</span></p>
            <div class="flex flex-wrap gap-2">
                <Button type="button" variant="outline" :disabled="portal.processing" @click="portal.post('/billing/portal')">Billing portal</Button>
                <Button v-if="subscription?.valid" type="button" variant="outline" :disabled="cancel.processing" @click="cancel.post('/billing/cancel')">Cancel at period end</Button>
            </div>
        </div>
    </AppLayout>
</template>
