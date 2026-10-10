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
    <div class="s16x0eqh">
        <form class="s1r0jak5" @submit.prevent>
            <div class="s9tb7ek">
                <FormField label="Original price">
                    <input v-model="form.original_price" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Currency">
                    <select v-model="form.currency" class="s1wl1yqe">
                        <option>EUR</option>
                        <option>USD</option>
                        <option>GBP</option>
                    </select>
                </FormField>
            </div>
            <fieldset>
                <legend class="s17n5pcd">First discount</legend>
                <div class="s19pcede">
                    <label class="s2ca09w"><input v-model="form.discount_type" type="radio" value="percent" /> Percentage</label>
                    <label class="s2ca09w"><input v-model="form.discount_type" type="radio" value="amount" /> Fixed amount</label>
                </div>
            </fieldset>
            <FormField v-if="form.discount_type === 'percent'" label="Discount %" hint="Taken from the original price.">
                <input v-model="form.discount_percent" inputmode="decimal" class="s1wl1yqe" />
            </FormField>
            <FormField v-else label="Discount amount" hint="Subtracted once from the original price.">
                <input v-model="form.discount_amount" inputmode="decimal" class="s1wl1yqe" />
            </FormField>
            <div class="s9tb7ek">
                <FormField label="Second discount %" hint="Applied to the price left after the first discount.">
                    <input v-model="form.successive_percents[0]" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Third discount %" hint="Optional. Also applied to the remaining price.">
                    <input v-model="form.successive_percents[1]" inputmode="decimal" class="s1wl1yqe" />
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
            <p v-if="'error' in result" class="sc4izda">{{ result.error }}</p>
            <dl v-else class="sb0yfeu">
                <div v-for="[label, value] in rows" :key="label" class="s1sa1hc2">
                    <dt class="s1bkxu62">{{ label }}</dt>
                    <dd class="s55z0kz">{{ value }}</dd>
                </div>
            </dl>
            <p class="s19ltth3">
                A 20% discount followed by 10% is not a 30% discount. Each percentage comes off the price that is left.
            </p>
            <AdSlot label="Discount calculator placement" />
            <p class="s19ltth3">Shopping affiliate links are not shown here. This page stays free of partner links until a real shopping relationship is added.</p>
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

.s17n5pcd {
    @apply text-sm font-medium;
}

.s19pcede {
    @apply mt-2 flex flex-wrap gap-4 text-sm;
}

.s2ca09w {
    @apply flex items-center gap-2;
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

.sc4izda {
    @apply mt-4 text-sm text-red-700 dark:text-red-300;
}

.sb0yfeu {
    @apply mt-4 space-y-3;
}

.s1sa1hc2 {
    @apply flex items-baseline justify-between gap-4 border-b border-slate-100 pb-2 dark:border-slate-800;
}

.s1bkxu62 {
    @apply text-sm;
}

.s55z0kz {
    @apply font-medium;
}

.s19ltth3 {
    @apply mt-4 text-sm leading-6;
}
</style>
