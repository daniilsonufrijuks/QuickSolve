<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { calculateDateDifference } from '@/lib/dateDifference';
import { computed, ref } from 'vue';

const start = ref('2026-01-01');
const end = ref('2026-10-11');
const result = computed(() => calculateDateDifference(start.value, end.value));
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Start date"><input v-model="start" type="date" class="field" /></FormField>
            <FormField label="End date"><input v-model="end" type="date" class="field" /></FormField>
        </form>
        <section class="panel">
            <p v-if="'error' in result" class="error">{{ result.error }}</p>
            <dl v-else class="facts">
                <div><dt>Days</dt><dd>{{ result.days }}</dd></div>
                <div><dt>Weeks</dt><dd>{{ result.weeks }} weeks and {{ result.leftover_days }} days</dd></div>
                <div><dt>Whole months</dt><dd>{{ result.months }}</dd></div>
                <div><dt>Whole years</dt><dd>{{ result.years }}</dd></div>
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

.error {
    @apply text-sm text-red-700 dark:text-red-300;
}
</style>
