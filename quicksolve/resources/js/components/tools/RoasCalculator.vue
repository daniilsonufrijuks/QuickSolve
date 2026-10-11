<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { track } from '@/lib/api';
import { calculateRoas } from '@/lib/roas';
import { computed, reactive, ref } from 'vue';

const form = reactive({
    ad_spend: '250.00',
    revenue: '900.00',
    product_cost: '320.00',
    currency: 'EUR',
});

const result = computed(() => calculateRoas(form));
const figures = computed(() => ('error' in result.value ? null : result.value));
const copied = ref(false);

async function copyResults() {
    if (!figures.value) {
        return;
    }

    await navigator.clipboard.writeText(`ROAS ${figures.value.roas} · profit ${figures.value.profit} ${figures.value.currency}`);
    copied.value = true;
    track('calculation_completed', 'roas-calculator');
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Ad spend"><input v-model="form.ad_spend" class="field" inputmode="decimal" /></FormField>
            <FormField label="Revenue from those ads"><input v-model="form.revenue" class="field" inputmode="decimal" /></FormField>
            <FormField label="Product cost of those sales"><input v-model="form.product_cost" class="field" inputmode="decimal" /></FormField>
            <FormField label="Currency">
                <select v-model="form.currency" class="field">
                    <option>EUR</option>
                    <option>USD</option>
                    <option>GBP</option>
                </select>
            </FormField>
            <Button type="button" :disabled="!figures" @click="copyResults">{{ copied ? 'Copied' : 'Copy result' }}</Button>
        </form>
        <section class="panel">
            <p v-if="'error' in result" class="error">{{ result.error }}</p>
            <dl v-else class="facts">
                <div><dt>ROAS</dt><dd>{{ figures?.roas }}</dd></div>
                <div><dt>Profit after ads and product cost</dt><dd>{{ figures?.profit }} {{ figures?.currency }}</dd></div>
                <div><dt>Break-even ROAS</dt><dd>{{ figures?.break_even_roas ?? 'Not available' }}</dd></div>
            </dl>
            <p class="meta">ROAS is revenue divided by ad spend. Break-even ROAS uses revenue divided by revenue minus product cost. It does not include VAT, wages, or returns.</p>
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
