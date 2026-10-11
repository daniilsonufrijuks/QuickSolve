<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { track } from '@/lib/api';
import { calculateProfit } from '@/lib/profit';
import { computed, reactive, ref } from 'vue';

const presets: Record<string, { marketplace_fee_percent: string; marketplace_fee_fixed: string; processing_fee_percent: string; processing_fee_fixed: string }> = {
    etsy: { marketplace_fee_percent: '6.5', marketplace_fee_fixed: '0.20', processing_fee_percent: '3', processing_fee_fixed: '0.25' },
    amazon: { marketplace_fee_percent: '15', marketplace_fee_fixed: '0.00', processing_fee_percent: '0', processing_fee_fixed: '0.00' },
    ebay: { marketplace_fee_percent: '13.6', marketplace_fee_fixed: '0.30', processing_fee_percent: '0', processing_fee_fixed: '0.00' },
    shopify: { marketplace_fee_percent: '0', marketplace_fee_fixed: '0.00', processing_fee_percent: '2.9', processing_fee_fixed: '0.30' },
};

const platform = ref('etsy');
const form = reactive({
    selling_price: '48.00',
    product_cost: '14.00',
    shipping_cost: '4.50',
    packaging_cost: '1.00',
    marketplace_fee_percent: presets.etsy.marketplace_fee_percent,
    marketplace_fee_fixed: presets.etsy.marketplace_fee_fixed,
    processing_fee_percent: presets.etsy.processing_fee_percent,
    processing_fee_fixed: presets.etsy.processing_fee_fixed,
    currency: 'EUR',
});

function applyPreset() {
    Object.assign(form, presets[platform.value]);
}

applyPreset();

const result = computed(() => calculateProfit(form));
const figures = computed(() => ('error' in result.value ? null : result.value));
const copied = ref(false);

async function copyResults() {
    if (!figures.value) {
        return;
    }

    await navigator.clipboard.writeText(`Profit ${figures.value.profit} ${figures.value.currency} · margin ${figures.value.profit_margin}%`);
    copied.value = true;
    track('calculation_completed', 'marketplace-fee-calculator');
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Fee schedule">
                <select v-model="platform" class="field" @change="applyPreset">
                    <option value="etsy">Etsy-style example</option>
                    <option value="amazon">Amazon-style example</option>
                    <option value="ebay">eBay-style example</option>
                    <option value="shopify">Shopify payments example</option>
                </select>
            </FormField>
            <p class="meta">These are example schedules, not live marketplace rates. Change any fee before you rely on the number.</p>
            <FormField label="Selling price"><input v-model="form.selling_price" class="field" inputmode="decimal" /></FormField>
            <FormField label="Product cost"><input v-model="form.product_cost" class="field" inputmode="decimal" /></FormField>
            <FormField label="Shipping"><input v-model="form.shipping_cost" class="field" inputmode="decimal" /></FormField>
            <FormField label="Marketplace fee %"><input v-model="form.marketplace_fee_percent" class="field" inputmode="decimal" /></FormField>
            <FormField label="Marketplace fee fixed"><input v-model="form.marketplace_fee_fixed" class="field" inputmode="decimal" /></FormField>
            <FormField label="Processing fee %"><input v-model="form.processing_fee_percent" class="field" inputmode="decimal" /></FormField>
            <FormField label="Processing fee fixed"><input v-model="form.processing_fee_fixed" class="field" inputmode="decimal" /></FormField>
            <Button type="button" :disabled="!figures" @click="copyResults">{{ copied ? 'Copied' : 'Copy result' }}</Button>
        </form>
        <section class="panel">
            <p v-if="'error' in result" class="error">{{ result.error }}</p>
            <dl v-else class="facts">
                <div><dt>Marketplace fee</dt><dd>{{ figures?.marketplace_fee }} {{ figures?.currency }}</dd></div>
                <div><dt>Processing fee</dt><dd>{{ figures?.processing_fee }} {{ figures?.currency }}</dd></div>
                <div><dt>Profit</dt><dd>{{ figures?.profit }} {{ figures?.currency }}</dd></div>
                <div><dt>Margin</dt><dd>{{ figures?.profit_margin }}%</dd></div>
            </dl>
        </section>
    </div>
</template>

<style scoped>
.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.panel {
    @apply space-y-3 rounded-xl border p-5;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.facts {
    @apply space-y-3 text-sm;
}

.meta,
.error {
    @apply text-sm leading-6;
}

.error {
    @apply text-red-700 dark:text-red-300;
}
</style>
