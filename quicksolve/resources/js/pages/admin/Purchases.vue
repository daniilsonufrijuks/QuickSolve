<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    purchases: { data: Array<any> };
    users: Array<{ id: number; name: string; email: string }>;
    templates: Array<{ id: number; name: string }>;
}>();

const editingId = ref<number | null>(null);
const form = useForm({
    user_id: props.users[0]?.id ?? '',
    template_id: props.templates[0]?.id ?? '',
    amount: 1900,
    currency: 'EUR',
    status: 'pending',
    payment_reference: '',
});

function edit(purchase: any) {
    editingId.value = purchase.id;
    form.user_id = purchase.user_id;
    form.template_id = purchase.template_id;
    form.amount = purchase.amount;
    form.currency = purchase.currency;
    form.status = purchase.status;
    form.payment_reference = purchase.payment_reference ?? '';
}

function resetForm() {
    editingId.value = null;
    form.reset();
}

function save() {
    if (editingId.value) {
        form.put(`/admin/purchases/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/admin/purchases', { onSuccess: resetForm });
}
</script>

<template>
    <Head title="Purchase records" />
    <AppLayout :breadcrumbs="[{ title: 'Purchases', href: '/admin/purchases' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update purchase' : 'Add a purchase' }}</h1>
                    <FormField label="Buyer">
                        <select v-model="form.user_id" class="field">
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.email }}</option>
                        </select>
                    </FormField>
                    <FormField label="Template">
                        <select v-model="form.template_id" class="field">
                            <option v-for="template in templates" :key="template.id" :value="template.id">{{ template.name }}</option>
                        </select>
                    </FormField>
                    <FormField label="Amount (cents)" :error="form.errors.amount"><input v-model="form.amount" class="field" inputmode="numeric" /></FormField>
                    <FormField label="Currency">
                        <select v-model="form.currency" class="field">
                            <option>EUR</option>
                            <option>USD</option>
                            <option>GBP</option>
                        </select>
                    </FormField>
                    <FormField label="Status">
                        <select v-model="form.status" class="field">
                            <option value="pending">pending</option>
                            <option value="paid">paid</option>
                            <option value="failed">failed</option>
                            <option value="refunded">refunded</option>
                        </select>
                    </FormField>
                    <FormField label="Payment reference"><input v-model="form.payment_reference" class="field" /></FormField>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">Save purchase</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <table class="table">
                    <thead>
                        <tr class="row">
                            <th class="cell">Buyer</th>
                            <th>Template</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="purchase in purchases.data" :key="purchase.id" class="row">
                            <td class="cell">{{ purchase.user?.email }}</td>
                            <td>{{ purchase.template?.name }}</td>
                            <td>{{ purchase.amount }} {{ purchase.currency }}</td>
                            <td>{{ purchase.status }}</td>
                            <td class="actions">
                                <Button type="button" variant="outline" @click="edit(purchase)">Edit</Button>
                                <Button v-if="purchase.status === 'paid'" type="button" variant="outline" @click="router.post(`/admin/purchases/${purchase.id}/refund`)">Mark refunded</Button>
                                <Button type="button" variant="ghost" @click="router.delete(`/admin/purchases/${purchase.id}`)">Delete</Button>
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
