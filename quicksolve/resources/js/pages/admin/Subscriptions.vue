<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    subscriptions: { data: Array<any> };
    users: Array<{ id: number; name: string; email: string }>;
    prices: { pro: string | null; business: string | null };
}>();

const editingId = ref<number | null>(null);
const form = useForm({
    user_id: props.users[0]?.id ?? '',
    type: 'default',
    stripe_id: '',
    stripe_status: 'active',
    stripe_price: props.prices.pro ?? '',
    quantity: 1,
    ends_at: '',
});

function applyPlan(plan: 'pro' | 'business') {
    const price = props.prices[plan];

    if (price) {
        form.stripe_price = price;
    }
}

function edit(subscription: any) {
    editingId.value = subscription.id;
    form.user_id = subscription.user_id;
    form.type = subscription.type ?? 'default';
    form.stripe_id = subscription.stripe_id;
    form.stripe_status = subscription.stripe_status;
    form.stripe_price = subscription.stripe_price ?? '';
    form.quantity = subscription.quantity ?? 1;
    form.ends_at = subscription.ends_at ? String(subscription.ends_at).slice(0, 10) : '';
    form.clearErrors();
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.stripe_price = props.prices.pro ?? '';
}

function save() {
    if (editingId.value) {
        form.put(`/admin/subscriptions/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/admin/subscriptions', { onSuccess: resetForm });
}
</script>

<template>
    <Head title="Subscriptions" />
    <AppLayout :breadcrumbs="[{ title: 'Subscriptions', href: '/admin/subscriptions' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update subscription' : 'Add a subscription' }}</h1>
                    <p class="copy">Saving here changes QuickSolve access. It does not charge Stripe. Cancel calls Stripe only when the id looks like a live Stripe subscription.</p>
                    <FormField label="Account">
                        <select v-model="form.user_id" class="field">
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.email }}</option>
                        </select>
                    </FormField>
                    <div class="actions">
                        <Button type="button" variant="outline" :disabled="!prices.pro" @click="applyPlan('pro')">Use Pro price</Button>
                        <Button type="button" variant="outline" :disabled="!prices.business" @click="applyPlan('business')">Use Business price</Button>
                    </div>
                    <FormField label="Stripe price id" :error="form.errors.stripe_price"><input v-model="form.stripe_price" class="field" /></FormField>
                    <FormField label="Stripe subscription id" :error="form.errors.stripe_id"><input v-model="form.stripe_id" class="field" placeholder="sub_admin_local or a live sub_ id" /></FormField>
                    <FormField label="Type" :error="form.errors.type"><input v-model="form.type" class="field" /></FormField>
                    <FormField label="Status">
                        <select v-model="form.stripe_status" class="field">
                            <option value="active">active</option>
                            <option value="trialing">trialing</option>
                            <option value="canceled">canceled</option>
                            <option value="incomplete">incomplete</option>
                            <option value="incomplete_expired">incomplete_expired</option>
                            <option value="past_due">past_due</option>
                            <option value="unpaid">unpaid</option>
                            <option value="paused">paused</option>
                        </select>
                    </FormField>
                    <FormField label="Quantity" :error="form.errors.quantity"><input v-model.number="form.quantity" class="field" inputmode="numeric" /></FormField>
                    <FormField label="Ends at (optional)" :error="form.errors.ends_at"><input v-model="form.ends_at" class="field" type="date" /></FormField>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">Save subscription</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <table class="table">
                    <thead>
                        <tr class="row">
                            <th class="cell">Account</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Stripe id</th>
                            <th>Ends</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="row">
                            <td class="cell">{{ subscription.user?.email }}</td>
                            <td>{{ subscription.stripe_status }}</td>
                            <td>{{ subscription.stripe_price }}</td>
                            <td>{{ subscription.stripe_id }}</td>
                            <td>{{ subscription.ends_at ?? '—' }}</td>
                            <td class="actions">
                                <Button type="button" variant="outline" @click="edit(subscription)">Edit</Button>
                                <Button v-if="subscription.stripe_status !== 'canceled'" type="button" variant="outline" @click="router.post(`/admin/subscriptions/${subscription.id}/cancel`)">Cancel</Button>
                                <Button type="button" variant="ghost" @click="router.delete(`/admin/subscriptions/${subscription.id}`)">Delete</Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply p-4;
}

.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.form {
    @apply space-y-3;
}

.title {
    @apply text-xl font-semibold;
}

.copy {
    @apply text-sm text-muted-foreground;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.table {
    @apply w-full text-left text-sm;
}

.row {
    @apply border-b;
}

.cell {
    @apply py-2;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
