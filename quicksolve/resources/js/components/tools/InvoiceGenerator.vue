<script setup lang="ts">
import ConfirmDialog from '@/components/marketing/ConfirmDialog.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { calculateInvoice } from '@/lib/invoice';
import type { SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage<SharedData>();

function printInvoice() {
    window.print();
}
const confirmReset = ref(false);

const form = useForm({
    business_name: '',
    business_email: '',
    business_address: '',
    customer_name: '',
    customer_email: '',
    customer_address: '',
    invoice_number: 'INV-1001',
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: new Date().toISOString().slice(0, 10),
    currency: 'EUR',
    tax_rate: '0',
    notes: '',
    payment_instructions: '',
    items: [{ description: '', quantity: '1', unit_price: '0.00' }],
});

const preview = computed(() =>
    calculateInvoice({
        currency: form.currency,
        tax_rate: form.tax_rate,
        items: form.items,
    }),
);

function addItem() {
    form.items.push({ description: '', quantity: '1', unit_price: '0.00' });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function downloadPdf() {
    const pdfForm = document.createElement('form');
    pdfForm.method = 'POST';
    pdfForm.action = '/tools/invoice-generator/pdf';
    const token = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');
    const fields: Record<string, string> = {
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? token,
        business_name: form.business_name,
        business_email: form.business_email,
        business_address: form.business_address,
        customer_name: form.customer_name,
        customer_email: form.customer_email,
        customer_address: form.customer_address,
        invoice_number: form.invoice_number,
        issue_date: form.issue_date,
        due_date: form.due_date,
        currency: form.currency,
        tax_rate: form.tax_rate,
        notes: form.notes,
        payment_instructions: form.payment_instructions,
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        pdfForm.appendChild(input);
    });

    form.items.forEach((item, index) => {
        (['description', 'quantity', 'unit_price'] as const).forEach((key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${index}][${key}]`;
            input.value = item[key];
            pdfForm.appendChild(input);
        });
    });

    document.body.appendChild(pdfForm);
    pdfForm.submit();
    pdfForm.remove();
}

function save() {
    form.post('/invoices');
}

function reset() {
    form.reset();
    form.items = [{ description: '', quantity: '1', unit_price: '0.00' }];
    confirmReset.value = false;
}
</script>

<template>
    <div class="s1j8i8bd">
        <p class="s1an3db">This tool creates a document. It does not collect payment, and it does not confirm tax treatment for your country.</p>
        <div class="sr07hjo">
            <form class="s1r0jak5" @submit.prevent="save">
                <div class="s9tb7ek">
                    <FormField label="Business name" :error="form.errors.business_name"><input v-model="form.business_name" class="s1wl1yqe" /></FormField>
                    <FormField label="Business email"><input v-model="form.business_email" type="email" class="s1wl1yqe" /></FormField>
                    <FormField label="Customer name" :error="form.errors.customer_name"><input v-model="form.customer_name" class="s1wl1yqe" /></FormField>
                    <FormField label="Customer email"><input v-model="form.customer_email" type="email" class="s1wl1yqe" /></FormField>
                    <FormField label="Invoice number"><input v-model="form.invoice_number" class="s1wl1yqe" /></FormField>
                    <FormField label="Currency">
                        <select v-model="form.currency" class="s1wl1yqe"><option>EUR</option><option>USD</option><option>GBP</option></select>
                    </FormField>
                    <FormField label="Issue date"><input v-model="form.issue_date" type="date" class="s1wl1yqe" /></FormField>
                    <FormField label="Due date"><input v-model="form.due_date" type="date" class="s1wl1yqe" /></FormField>
                    <FormField label="Tax rate %" hint="Applied once to the subtotal."><input v-model="form.tax_rate" inputmode="decimal" class="s1wl1yqe" /></FormField>
                </div>
                <FormField label="Business address"><textarea v-model="form.business_address" rows="2" class="s1wl1yqe" /></FormField>
                <FormField label="Customer address"><textarea v-model="form.customer_address" rows="2" class="s1wl1yqe" /></FormField>
                <div class="s1j8i8bc">
                    <div class="sxc8ak4">
                        <h2 class="s55z0kz">Line items</h2>
                        <Button type="button" variant="outline" @click="addItem">Add line</Button>
                    </div>
                    <div v-for="(item, index) in form.items" :key="index" class="sbd7xm1">
                        <input v-model="item.description" placeholder="Description" class="s1knqkr5" />
                        <input v-model="item.quantity" placeholder="Qty" class="s1knqkr5" />
                        <input v-model="item.unit_price" placeholder="Unit price" class="s1knqkr5" />
                        <Button type="button" variant="ghost" :disabled="form.items.length === 1" @click="removeItem(index)">Remove</Button>
                    </div>
                </div>
                <FormField label="Notes"><textarea v-model="form.notes" rows="2" class="s1wl1yqe" /></FormField>
                <FormField label="Payment instructions"><textarea v-model="form.payment_instructions" rows="2" class="s1wl1yqe" /></FormField>
                <div class="s1sdudaq">
                    <Button type="button" variant="outline" @click="downloadPdf">Download PDF</Button>
                    <Button type="button" variant="outline" @click="printInvoice">Print</Button>
                    <Button v-if="page.props.auth.user" type="submit" :disabled="form.processing">Save to account</Button>
                    <Button v-else as-child variant="outline"><a href="/login">Log in to save</a></Button>
                    <Button type="button" variant="ghost" @click="confirmReset = true">Reset</Button>
                </div>
                <p v-if="form.errors.items" class="sg4mcch">{{ form.errors.items }}</p>
            </form>
            <section class="invoice-preview">
                <p class="s7dsyaz">Preview</p>
                <h2 class="sgznuwb">Invoice {{ form.invoice_number }}</h2>
                <div class="sktqgb4">
                    <p><strong>{{ form.business_name || 'Your business' }}</strong><br />{{ form.business_email }}</p>
                    <p><strong>Bill to</strong><br />{{ form.customer_name || 'Customer' }}</p>
                </div>
                <p v-if="'error' in preview" class="s19m5poz">{{ preview.error }}</p>
                <table v-else class="sbscwr5">
                    <thead>
                        <tr class="s1fzh5qg">
                            <th class="s221da">Description</th>
                            <th class="skenhpq">Qty</th>
                            <th class="skenhpq">Price</th>
                            <th class="skenhpq">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in preview.items" :key="item.description" class="syh3pxd">
                            <td class="s221da">{{ item.description }}</td>
                            <td class="skenhpq">{{ item.quantity }}</td>
                            <td class="skenhpq">{{ item.unit_price }}</td>
                            <td class="skenhpq">{{ item.line_total }}</td>
                        </tr>
                    </tbody>
                </table>
                <dl v-if="!('error' in preview)" class="s1caj91u">
                    <div class="sqh6ahw"><dt>Subtotal</dt><dd>{{ preview.subtotal }}</dd></div>
                    <div class="sqh6ahw"><dt>Tax {{ preview.tax_rate }}%</dt><dd>{{ preview.tax }}</dd></div>
                    <div class="s1xdl1dh"><dt>Total</dt><dd>{{ preview.total }} {{ preview.currency }}</dd></div>
                </dl>
            </section>
        </div>
        <ConfirmDialog :open="confirmReset" title="Reset this invoice?" message="The form will be cleared. A saved copy in your account is not deleted." @cancel="confirmReset = false" @confirm="reset" />
    </div>
</template>

<style scoped>
.s1j8i8bd {
    @apply space-y-4;
}

.s1an3db {
    @apply rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700;
}

.sr07hjo {
    @apply grid gap-6 xl:grid-cols-2;
}

.s1r0jak5 {
    @apply space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.s9tb7ek {
    @apply grid gap-4 sm:grid-cols-2;
}

.s1wl1yqe {
    @apply w-full rounded-lg border px-3 py-2;
}

.s1j8i8bc {
    @apply space-y-3;
}

.sxc8ak4 {
    @apply flex items-center justify-between;
}

.s55z0kz {
    @apply font-medium;
}

.sbd7xm1 {
    @apply grid gap-2 rounded-lg border p-3 sm:grid-cols-[1fr_90px_110px_auto];
}

.s1knqkr5 {
    @apply rounded-lg border px-3 py-2;
}

.s1sdudaq {
    @apply flex flex-wrap gap-2;
}

.sg4mcch {
    @apply text-sm text-red-700;
}

.invoice-preview {
    @apply rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900;
}

.s7dsyaz {
    @apply text-xs uppercase tracking-wide text-slate-500;
}

.sgznuwb {
    @apply mt-2 text-2xl font-semibold;
}

.sktqgb4 {
    @apply mt-4 grid gap-4 text-sm sm:grid-cols-2;
}

.s19m5poz {
    @apply mt-4 text-sm text-red-700;
}

.sbscwr5 {
    @apply mt-4 w-full text-sm;
}

.s1fzh5qg {
    @apply border-b text-left;
}

.s221da {
    @apply py-2;
}

.skenhpq {
    @apply py-2 text-right;
}

.syh3pxd {
    @apply border-b;
}

.s1caj91u {
    @apply ml-auto mt-4 w-52 space-y-1 text-sm;
}

.sqh6ahw {
    @apply flex justify-between;
}

.s1xdl1dh {
    @apply flex justify-between font-semibold;
}
</style>
