<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { computed, ref } from 'vue';

const min = ref('1');
const max = ref('100');
const count = ref('1');
const unique = ref(false);
const copied = ref(false);
const numbers = ref<number[]>([]);
const error = ref('');

function randomInt(low: number, high: number): number {
    const span = high - low + 1;
    const values = new Uint32Array(1);

    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
        crypto.getRandomValues(values);

        return low + (values[0] % span);
    }

    return low + Math.floor(Math.random() * span);
}

function generate() {
    copied.value = false;
    const low = Number.parseInt(min.value, 10);
    const high = Number.parseInt(max.value, 10);
    const total = Number.parseInt(count.value, 10);

    if (!Number.isInteger(low) || !Number.isInteger(high) || low > high) {
        error.value = 'Minimum must be an integer no larger than maximum.';
        numbers.value = [];

        return;
    }

    if (!Number.isInteger(total) || total < 1 || total > 100) {
        error.value = 'Count must be a whole number from 1 to 100.';
        numbers.value = [];

        return;
    }

    const span = high - low + 1;

    if (unique.value && total > span) {
        error.value = 'Unique values cannot exceed the size of the range.';
        numbers.value = [];

        return;
    }

    error.value = '';
    const drawn: number[] = [];

    while (drawn.length < total) {
        const next = randomInt(low, high);

        if (unique.value && drawn.includes(next)) {
            continue;
        }

        drawn.push(next);
    }

    numbers.value = drawn;
}

const summary = computed(() => numbers.value.join(', '));

async function copy() {
    if (numbers.value.length === 0) {
        return;
    }

    await navigator.clipboard.writeText(summary.value);
    copied.value = true;
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent="generate">
            <FormField label="Minimum"><input v-model="min" class="field" inputmode="numeric" /></FormField>
            <FormField label="Maximum"><input v-model="max" class="field" inputmode="numeric" /></FormField>
            <FormField label="How many"><input v-model="count" class="field" inputmode="numeric" /></FormField>
            <label class="check"><input v-model="unique" type="checkbox" /> Unique values only</label>
            <div class="actions">
                <Button type="submit">Generate</Button>
                <Button type="button" variant="outline" :disabled="numbers.length === 0" @click="copy">{{ copied ? 'Copied' : 'Copy' }}</Button>
            </div>
        </form>
        <section class="panel">
            <p v-if="error" class="error">{{ error }}</p>
            <p v-else-if="numbers.length === 0" class="meta">Choose a range and generate numbers in this browser.</p>
            <p v-else class="result">{{ summary }}</p>
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

.check {
    @apply flex items-center gap-2 text-sm;
}

.actions {
    @apply flex flex-wrap gap-2;
}

.result {
    @apply text-2xl font-semibold;
}

.meta,
.error {
    @apply text-sm;
}

.error {
    @apply text-red-700;
}
</style>
