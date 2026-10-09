<script setup lang="ts">
import AdSlot from '@/components/marketing/AdSlot.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { track } from '@/lib/api';
import { calculateDiscount, type DiscountInput, type DiscountResult } from '@/lib/discount';
import { computed, reactive, ref } from 'vue';

const form = reactive<DiscountInput>({
    original_price: '100.00',
    discount_type: 'percent',
    discount_percent: '20',
    discount_amount: '',
    successive_percents: ['10', ''],
    currency: 'EUR',
});

const copied = ref(false);
const result = computed(() => calculateDiscount(form));
const figures = computed(() => ('error' in result.value ? null : (result.value as DiscountResult)));

const rows = computed(() => {
    if (!figures.value) {
        return [];
    }

    return [
        ['Original price', figures.value.original_price],
        ['Sale price', figures.value.sale_price],
        ['You save', figures.value.savings],
        ['Effective discount', `${figures.value.effective_discount}%`],
    ];
});

function reset() {
    Object.assign(form, {
        original_price: '',
        discount_type: 'percent',
        discount_percent: '',
        discount_amount: '',
        successive_percents: ['', ''],
        currency: 'EUR',
    });
}

function loadExample() {
    Object.assign(form, {
        original_price: '100.00',
        discount_type: 'percent',
        discount_percent: '20',
        discount_amount: '',
        successive_percents: ['10', ''],
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
    track('calculation_completed', 'discount-calculator');
}
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <form class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent>
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Original price">
                    <input v-model="form.original_price" inputmode="decimal" class="w-full rounded-lg border px-3 py-2" />
                </FormField>
                <FormField label="Currency">
                    <select v-model="form.currency" class="w-full rounded-lg border px-3 py-2">
                        <option>EUR</option>
                        <option>USD</option>
                        <option>GBP</option>
                    </select>
                </FormField>
            </div>
            <fieldset>
                <legend class="text-sm font-medium">First discount</legend>
                <div class="mt-2 flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2"><input v-model="form.discount_type" type="radio" value="percent" /> Percentage</label>
                    <label class="flex items-center gap-2"><input v-model="form.discount_type" type="radio" value="amount" /> Fixed amount</label>
                </div>
            </fieldset>
            <FormField v-if="form.discount_type === 'percent'" label="Discount %" hint="Taken from the original price.">
                <input v-model="form.discount_percent" inputmode="decimal" class="w-full rounded-lg border px-3 py-2" />
            </FormField>
            <FormField v-else label="Discount amount" hint="Subtracted once from the original price.">
                <input v-model="form.discount_amount" inputmode="decimal" class="w-full rounded-lg border px-3 py-2" />
            </FormField>
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Second discount %" hint="Applied to the price left after the first discount.">
                    <input v-model="form.successive_percents[0]" inputmode="decimal" class="w-full rounded-lg border px-3 py-2" />
                </FormField>
                <FormField label="Third discount %" hint="Optional. Also applied to the remaining price.">
                    <input v-model="form.successive_percents[1]" inputmode="decimal" class="w-full rounded-lg border px-3 py-2" />
                </FormField>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button type="button" variant="outline" @click="reset">Reset</Button>
                <Button type="button" variant="outline" @click="loadExample">Load example</Button>
                <Button type="button" :disabled="!figures" @click="copyResults">{{ copied ? 'Copied' : 'Copy results' }}</Button>
            </div>
        </form>
        <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" aria-live="polite">
            <h2 class="text-lg font-semibold">Results</h2>
            <p v-if="'error' in result" class="mt-4 text-sm text-red-700 dark:text-red-300">{{ result.error }}</p>
            <dl v-else class="mt-4 space-y-3">
                <div v-for="[label, value] in rows" :key="label" class="flex items-baseline justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-800">
                    <dt class="text-sm">{{ label }}</dt>
                    <dd class="font-medium">{{ value }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-sm leading-6">
                A 20% discount followed by 10% is not a 30% discount. Each percentage comes off the price that is left.
            </p>
            <AdSlot label="Discount calculator placement" />
            <p class="mt-4 text-sm leading-6">Shopping affiliate links are not shown here. This page stays free of partner links until a real shopping relationship is added.</p>
        </section>
    </div>
</template>
