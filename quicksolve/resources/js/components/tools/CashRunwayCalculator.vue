<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { calculateRunway } from '@/lib/runway';
import { track } from '@/lib/api';
import { computed, reactive, ref } from 'vue';

const form = reactive({
    cash: '24000.00',
    monthly_burn: '6200.00',
    monthly_inflow: '4100.00',
    currency: 'EUR',
});

const copied = ref(false);
const result = computed(() => calculateRunway(form));
const figures = computed(() => ('error' in result.value ? null : result.value));

async function copyResults() {
    if (!figures.value) {
        return;
    }

    const line = figures.value.profitable
        ? `Inflow covers costs. Cash on hand: ${figures.value.cash} ${figures.value.currency}.`
        : `Net burn ${figures.value.net_burn} ${figures.value.currency}/month. Runway about ${figures.value.months} months.`;

    await navigator.clipboard.writeText(line);
    copied.value = true;
    track('calculation_completed', 'cash-runway-calculator');
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Cash on hand"><input v-model="form.cash" class="field" inputmode="decimal" /></FormField>
            <FormField label="Monthly costs"><input v-model="form.monthly_burn" class="field" inputmode="decimal" /></FormField>
            <FormField label="Monthly inflow"><input v-model="form.monthly_inflow" class="field" inputmode="decimal" /></FormField>
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
                <div><dt>Cash</dt><dd>{{ figures?.cash }} {{ figures?.currency }}</dd></div>
                <div><dt>Net monthly burn</dt><dd>{{ figures?.net_burn }} {{ figures?.currency }}</dd></div>
                <div>
                    <dt>Runway</dt>
                    <dd v-if="figures?.profitable">Inflow covers costs — no runway countdown</dd>
                    <dd v-else>{{ figures?.months }} months</dd>
                </div>
            </dl>
            <p class="meta">This is cash divided by net burn, rounded down to one decimal. It ignores tax, delayed invoices, and one-off costs. It is not a forecast.</p>
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
</style>
