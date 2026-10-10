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
const sync = useForm({});
</script>

<template>
    <Head title="Subscription" />
    <AppLayout :breadcrumbs="[{ title: 'Subscription', href: '/dashboard/subscription' }]">
        <div class="page">
            <h1 class="title">Subscription</h1>
            <p v-if="status === 'processing' && !subscription?.valid" class="notice">
                Stripe is confirming the payment. This page also asks Stripe for the subscription directly, so you should not need a webhook for the first activation.
            </p>
            <p>Current plan: <strong>{{ plan.name }}</strong></p>
            <p class="copy">{{ usage.used }} of {{ usage.limit }} descriptions used this {{ usage.window }}.</p>
            <p v-if="subscription" class="copy">Stripe status: {{ subscription.stripe_status }}<span v-if="subscription.on_grace_period"> · access until {{ subscription.ends_at }}</span></p>
            <p v-else class="copy">No subscription is stored on this account yet.</p>
            <p class="copy">Purchases on the dashboard are one-time template downloads. A monthly plan is not listed there.</p>
            <div class="actions">
                <Button type="button" variant="outline" :disabled="sync.processing" @click="sync.post('/dashboard/subscription/sync')">
                    {{ sync.processing ? 'Checking Stripe…' : 'Refresh from Stripe' }}
                </Button>
                <Button type="button" variant="outline" :disabled="portal.processing" @click="portal.post('/billing/portal')">Billing portal</Button>
                <Button v-if="subscription?.valid" type="button" variant="outline" :disabled="cancel.processing" @click="cancel.post('/billing/cancel')">Cancel at period end</Button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply max-w-2xl space-y-4 p-4;
}

.title {
    @apply text-2xl font-semibold;
}

.notice {
    @apply rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-950 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-50;
}

.copy {
    @apply text-sm leading-6;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
