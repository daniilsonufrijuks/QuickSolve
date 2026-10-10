<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    billing: {
        configured: boolean;
        pro_price_id: string | null;
        business_price_id: string | null;
        currency: string;
        pro_amount: number;
        business_amount: number;
    };
}>();

const form = useForm({
    pro_price_id: props.billing.pro_price_id ?? '',
    business_price_id: props.billing.business_price_id ?? '',
});

const sync = useForm({});

function save() {
    form.put('/admin/billing');
}

function createPrices() {
    sync.post('/admin/billing/sync');
}
</script>

<template>
    <Head title="Billing" />
    <AppLayout :breadcrumbs="[{ title: 'Billing', href: '/admin/billing' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Stripe billing</h1>
            <p class="copy">
                Checkout needs a Stripe secret and a recurring price for each paid plan. If the secret is set, QuickSolve can create the missing prices in your Stripe account. Prices stay on the server.
            </p>
            <p class="status" :class="{ ready: billing.configured }">
                Stripe secret: {{ billing.configured ? 'configured' : 'missing. Add STRIPE_SECRET to .env first.' }}
            </p>
            <dl class="facts">
                <div><dt>Pro</dt><dd>€{{ (billing.pro_amount / 100).toFixed(2) }} / month · {{ billing.pro_price_id || 'no price yet' }}</dd></div>
                <div><dt>Business</dt><dd>€{{ (billing.business_amount / 100).toFixed(2) }} / month · {{ billing.business_price_id || 'no price yet' }}</dd></div>
                <div><dt>Currency</dt><dd>{{ billing.currency }}</dd></div>
            </dl>
            <div class="actions">
                <Button type="button" :disabled="!billing.configured || sync.processing" @click="createPrices">
                    {{ sync.processing ? 'Talking to Stripe…' : 'Create missing prices in Stripe' }}
                </Button>
            </div>
            <form class="form" @submit.prevent="save">
                <h2 class="subtitle">Or paste price IDs from the Stripe dashboard</h2>
                <FormField label="Pro price ID" :error="form.errors.pro_price_id">
                    <input v-model="form.pro_price_id" class="field" placeholder="price_" />
                </FormField>
                <FormField label="Business price ID" :error="form.errors.business_price_id">
                    <input v-model="form.business_price_id" class="field" placeholder="price_" />
                </FormField>
                <Button type="submit" :disabled="form.processing">Save price IDs</Button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply space-y-4 p-4;
}

.title {
    @apply text-2xl font-semibold;
}

.subtitle {
    @apply text-lg font-semibold;
}

.copy,
.status {
    @apply max-w-3xl text-sm leading-6;
}

.ready {
    @apply font-medium text-blue-800 dark:text-blue-200;
}

.facts {
    @apply grid gap-3 rounded-xl border p-4 text-sm sm:grid-cols-3;
}

.actions,
.form {
    @apply space-y-3;
}

.field {
    @apply w-full max-w-md rounded-lg border px-3 py-2;
}
</style>
