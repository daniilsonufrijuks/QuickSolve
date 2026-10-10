<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { calculateProfit, type ProfitInput, type ProfitResult } from '@/lib/profit';
import { track } from '@/lib/api';
import { computed, reactive, ref } from 'vue';

const form = reactive<ProfitInput>({
    selling_price: '49.00',
    product_cost: '18.00',
    shipping_cost: '3.50',
    packaging_cost: '1.20',
    marketplace_fee_percent: '15',
    marketplace_fee_fixed: '0.30',
    processing_fee_percent: '2.9',
    processing_fee_fixed: '0.25',
    currency: 'EUR',
});

const copied = ref(false);

const result = computed(() => calculateProfit(form));
const figures = computed(() => ('error' in result.value ? null : (result.value as ProfitResult)));

const rows = computed(() => {
    if (!figures.value) {
        return [];
    }

    return [
        ['Marketplace fee', figures.value.marketplace_fee],
        ['Processing fee', figures.value.processing_fee],
        ['Total cost', figures.value.total_cost],
        ['Profit per sale', figures.value.profit],
        ['Profit margin', figures.value.profit_margin === null ? 'Not available' : `${figures.value.profit_margin}%`],
        ['Markup', figures.value.markup === null ? 'Not available' : `${figures.value.markup}%`],
        ['Break-even selling price', figures.value.break_even_price ?? 'Not possible at these fee rates'],
    ];
});

function reset() {
    Object.assign(form, {
        selling_price: '',
        product_cost: '',
        shipping_cost: '',
        packaging_cost: '',
        marketplace_fee_percent: '',
        marketplace_fee_fixed: '',
        processing_fee_percent: '',
        processing_fee_fixed: '',
        currency: 'EUR',
    });
}

async function copyResults() {
    if (!figures.value) {
        return;
    }

    const text = rows.value.map(([label, value]) => `${label}: ${value} ${figures.value?.currency}`).join('\n');
    await navigator.clipboard.writeText(text);
    copied.value = true;
    track('calculation_completed', 'profit-margin-calculator');
}

function loadExample() {
    Object.assign(form, {
        selling_price: '49.00',
        product_cost: '18.00',
        shipping_cost: '3.50',
        packaging_cost: '1.20',
        marketplace_fee_percent: '15',
        marketplace_fee_fixed: '0.30',
        processing_fee_percent: '2.9',
        processing_fee_fixed: '0.25',
        currency: 'EUR',
    });
}
</script>

<template>
    <div class="s16x0eqh">
        <form class="s1r0jak5" @submit.prevent>
            <div class="s9tb7ek">
                <FormField label="Selling price">
                    <input v-model="form.selling_price" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Currency">
                    <select v-model="form.currency" class="s1wl1yqe">
                        <option>EUR</option>
                        <option>USD</option>
                        <option>GBP</option>
                    </select>
                </FormField>
                <FormField label="Product cost">
                    <input v-model="form.product_cost" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Shipping cost">
                    <input v-model="form.shipping_cost" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Packaging cost">
                    <input v-model="form.packaging_cost" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Marketplace fee %" hint="Percentage of the selling price.">
                    <input v-model="form.marketplace_fee_percent" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Marketplace fixed fee" hint="Flat amount added on the same sale.">
                    <input v-model="form.marketplace_fee_fixed" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Processing fee %" hint="Percentage of the selling price.">
                    <input v-model="form.processing_fee_percent" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Processing fixed fee" hint="Flat amount, such as €0.25 per charge.">
                    <input v-model="form.processing_fee_fixed" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
            </div>
            <div class="s1sdudaq">
                <Button type="button" variant="outline" @click="reset">Reset</Button>
                <Button type="button" variant="outline" @click="loadExample">Load example</Button>
                <Button type="button" :disabled="!figures" @click="copyResults">{{ copied ? 'Copied' : 'Copy results' }}</Button>
            </div>
        </form>
        <section class="s2utn7g" aria-live="polite">
            <h2 class="s1cmvr70">Results</h2>
            <p v-if="'error' in result" class="s19m5poz">{{ result.error }}</p>
            <dl v-else class="sb0yfeu">
                <div v-for="[label, value] in rows" :key="label" class="s1ed58vv">
                    <dt class="syp5waw">{{ label }}</dt>
                    <dd class="s55z0kz">{{ value }}</dd>
                </div>
            </dl>
            <p class="smojh63">
                Margin is profit divided by the selling price. Markup is profit divided by total cost. Break-even is the selling price where profit is zero after the percentage fees.
            </p>
        </section>
    </div>
</template>

<style scoped>
.s16x0eqh {
    @apply grid gap-6 lg:grid-cols-[1.1fr_0.9fr];
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

.s1sdudaq {
    @apply flex flex-wrap gap-2;
}

.s2utn7g {
    @apply rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.s1cmvr70 {
    @apply text-lg font-semibold;
}

.s19m5poz {
    @apply mt-4 text-sm text-red-700;
}

.sb0yfeu {
    @apply mt-4 space-y-3;
}

.s1ed58vv {
    @apply flex items-baseline justify-between gap-4 border-b border-slate-100 pb-2;
}

.syp5waw {
    @apply text-sm text-slate-600;
}

.s55z0kz {
    @apply font-medium;
}

.smojh63 {
    @apply mt-4 text-sm leading-6 text-slate-600;
}
</style>
