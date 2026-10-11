<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { calculateBreakEven } from '@/lib/breakeven';
import { track } from '@/lib/api';
import { computed, reactive, ref } from 'vue';

const form = reactive({
    fixed_costs: '2400.00',
    variable_cost: '8.50',
    selling_price: '24.00',
    currency: 'EUR',
});

const copied = ref(false);
const result = computed(() => calculateBreakEven(form));
const figures = computed(() => ('error' in result.value ? null : result.value));

async function copyResults() {
    if (!figures.value) {
        return;
    }

    await navigator.clipboard.writeText(
        `Contribution: ${figures.value.contribution} ${figures.value.currency}\nUnits to break even: ${figures.value.units}\nRevenue at break-even: ${figures.value.revenue} ${figures.value.currency}`,
    );
    copied.value = true;
    track('calculation_completed', 'break-even-calculator');
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Fixed costs"><input v-model="form.fixed_costs" class="field" inputmode="decimal" /></FormField>
            <FormField label="Variable cost per unit"><input v-model="form.variable_cost" class="field" inputmode="decimal" /></FormField>
            <FormField label="Selling price"><input v-model="form.selling_price" class="field" inputmode="decimal" /></FormField>
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
                <div><dt>Contribution per unit</dt><dd>{{ figures?.contribution }} {{ figures?.currency }}</dd></div>
                <div><dt>Units to cover costs</dt><dd>{{ figures?.units }}</dd></div>
                <div><dt>Revenue at that volume</dt><dd>{{ figures?.revenue }} {{ figures?.currency }}</dd></div>
            </dl>
            <p class="meta">This ignores tax, unsold stock, and changing prices. Pair it with the profit margin calculator when fees also apply.</p>
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
