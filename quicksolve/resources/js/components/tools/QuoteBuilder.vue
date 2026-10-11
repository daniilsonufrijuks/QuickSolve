<script setup lang="ts">
import ConfirmDialog from '@/components/marketing/ConfirmDialog.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { calculateInvoice } from '@/lib/invoice';
import type { SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    plan?: string;
    business?: boolean;
}>();

const page = usePage<SharedData>();
const isPro = computed(() => props.plan === 'pro' || props.plan === 'business');
const confirmReset = ref(false);

const form = useForm({
    business_name: '',
    business_email: '',
    business_address: '',
    customer_name: '',
    customer_email: '',
    customer_address: '',
    quote_number: 'Q-1001',
    issue_date: new Date().toISOString().slice(0, 10),
    valid_until: new Date().toISOString().slice(0, 10),
    currency: 'EUR',
    tax_rate: '0',
    notes: '',
    payment_instructions: '',
    accent_color: '#1d4ed8',
    custom_footer: '',
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
    pdfForm.action = '/tools/quote-builder/pdf';
    const token = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');
    const fields: Record<string, string> = {
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? token,
        business_name: form.business_name,
        business_email: form.business_email,
        business_address: form.business_address,
        customer_name: form.customer_name,
        customer_email: form.customer_email,
        customer_address: form.customer_address,
        quote_number: form.quote_number,
        issue_date: form.issue_date,
        valid_until: form.valid_until,
        currency: form.currency,
        tax_rate: form.tax_rate,
        notes: form.notes,
        payment_instructions: form.payment_instructions,
        accent_color: form.accent_color,
        custom_footer: form.custom_footer,
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
    form.post('/quotes');
}

function reset() {
    form.reset();
    form.items = [{ description: '', quantity: '1', unit_price: '0.00' }];
    confirmReset.value = false;
}
</script>

<template>
    <div class="wrap">
        <p class="note">This quote does not collect payment. Check the figures before you send them.</p>
        <div class="cols">
            <form class="panel" @submit.prevent="save">
                <div class="fields">
                    <FormField label="Business name" :error="form.errors.business_name"><input v-model="form.business_name" class="field" /></FormField>
                    <FormField label="Business email"><input v-model="form.business_email" type="email" class="field" /></FormField>
                    <FormField label="Client name" :error="form.errors.customer_name"><input v-model="form.customer_name" class="field" /></FormField>
                    <FormField label="Client email"><input v-model="form.customer_email" type="email" class="field" /></FormField>
                    <FormField label="Quote number"><input v-model="form.quote_number" class="field" /></FormField>
                    <FormField label="Currency">
                        <select v-model="form.currency" class="field"><option>EUR</option><option>USD</option><option>GBP</option></select>
                    </FormField>
                    <FormField label="Issue date"><input v-model="form.issue_date" type="date" class="field" /></FormField>
                    <FormField label="Valid until"><input v-model="form.valid_until" type="date" class="field" /></FormField>
                    <FormField label="Tax rate %"><input v-model="form.tax_rate" inputmode="decimal" class="field" /></FormField>
                </div>
                <FormField label="Business address"><textarea v-model="form.business_address" rows="2" class="field" /></FormField>
                <FormField label="Client address"><textarea v-model="form.customer_address" rows="2" class="field" /></FormField>
                <div class="lines">
                    <div class="row">
                        <h2>Line items</h2>
                        <Button type="button" variant="outline" @click="addItem">Add line</Button>
                    </div>
                    <div v-for="(item, index) in form.items" :key="index" class="line">
                        <input v-model="item.description" placeholder="Description" class="field" />
                        <input v-model="item.quantity" placeholder="Qty" class="field" />
                        <input v-model="item.unit_price" placeholder="Unit price" class="field" />
                        <Button type="button" variant="ghost" :disabled="form.items.length === 1" @click="removeItem(index)">Remove</Button>
                    </div>
                </div>
                <FormField v-if="isPro" label="Accent colour"><input v-model="form.accent_color" type="color" class="field" /></FormField>
                <FormField v-if="props.business" label="Custom footer"><textarea v-model="form.custom_footer" rows="2" class="field" /></FormField>
                <FormField label="Notes"><textarea v-model="form.notes" rows="2" class="field" /></FormField>
                <FormField label="How to accept"><textarea v-model="form.payment_instructions" rows="2" class="field" /></FormField>
                <div class="actions">
                    <Button type="button" variant="outline" @click="downloadPdf">Download PDF</Button>
                    <Button v-if="page.props.auth.user" type="submit" :disabled="form.processing">Save to account</Button>
                    <Button v-else as-child variant="outline"><a href="/login">Log in to save</a></Button>
                    <Button type="button" variant="ghost" @click="confirmReset = true">Reset</Button>
                </div>
            </form>
            <section class="panel">
                <p class="kicker">Preview</p>
                <h2>Quote {{ form.quote_number }}</h2>
                <p v-if="'error' in preview" class="error">{{ preview.error }}</p>
                <dl v-else>
                    <div><dt>Subtotal</dt><dd>{{ preview.subtotal }}</dd></div>
                    <div><dt>Tax {{ preview.tax_rate }}%</dt><dd>{{ preview.tax }}</dd></div>
                    <div><dt>Total</dt><dd>{{ preview.total }} {{ preview.currency }}</dd></div>
                </dl>
            </section>
        </div>
        <ConfirmDialog :open="confirmReset" title="Reset this quote?" message="The form will be cleared. A saved copy in your account is not deleted." @cancel="confirmReset = false" @confirm="reset" />
    </div>
</template>

<style scoped>
.wrap {
    @apply space-y-4;
}

.note {
    @apply rounded-lg px-3 py-2 text-sm text-slate-700;
}

.cols {
    @apply grid gap-6 xl:grid-cols-2;
}

.panel {
    @apply space-y-4 rounded-xl border p-5;
}

.fields {
    @apply grid gap-4 sm:grid-cols-2;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.lines {
    @apply space-y-3;
}

.row {
    @apply flex items-center justify-between;
}

.line {
    @apply grid gap-2 rounded-lg border p-3 sm:grid-cols-[1fr_90px_110px_auto];
}

.actions {
    @apply flex flex-wrap gap-2;
}

.kicker {
    @apply text-xs uppercase tracking-wide text-slate-500;
}

.error {
    @apply text-sm text-red-700;
}
</style>
