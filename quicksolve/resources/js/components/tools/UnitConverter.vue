<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { convertUnit, unitGroups } from '@/lib/units';
import { computed, ref, watch } from 'vue';

const group = ref('length');
const from = ref('km');
const to = ref('mi');
const amount = ref('5');
const units = computed(() => Object.keys(unitGroups[group.value] ?? {}));
const result = computed(() => convertUnit(group.value, from.value, to.value, amount.value));

watch(group, (value) => {
    const keys = Object.keys(unitGroups[value]);
    from.value = keys[0];
    to.value = keys[1] ?? keys[0];
});
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Type">
                <select v-model="group" class="field">
                    <option value="length">Length</option>
                    <option value="weight">Weight</option>
                    <option value="temperature">Temperature</option>
                    <option value="volume">Volume</option>
                    <option value="speed">Speed</option>
                </select>
            </FormField>
            <FormField label="Amount"><input v-model="amount" class="field" inputmode="decimal" /></FormField>
            <FormField label="From">
                <select v-model="from" class="field">
                    <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                </select>
            </FormField>
            <FormField label="To">
                <select v-model="to" class="field">
                    <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                </select>
            </FormField>
        </form>
        <section class="panel">
            <p v-if="'error' in result" class="error">{{ result.error }}</p>
            <p v-else class="value">{{ amount }} {{ from }} = {{ result.value }} {{ to }}</p>
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

.value {
    @apply text-2xl font-semibold;
}

.error {
    @apply text-sm text-red-700 dark:text-red-300;
}
</style>
