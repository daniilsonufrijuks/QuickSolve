<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { track } from '@/lib/api';
import { calculateTimesheet, type TimesheetDay } from '@/lib/timesheet';
import { computed, reactive, ref } from 'vue';

const days = reactive<TimesheetDay[]>(
    ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((label) => ({
        label,
        start: label === 'Sat' || label === 'Sun' ? '' : '09:00',
        end: label === 'Sat' || label === 'Sun' ? '' : '17:30',
        break_minutes: label === 'Sat' || label === 'Sun' ? '' : '30',
    })),
);

const overtimeAfter = ref('08:00');
const copied = ref(false);

const overtimeMinutes = computed(() => {
    const [hours, minutes] = overtimeAfter.value.split(':').map(Number);

    return hours * 60 + minutes;
});

const result = computed(() => calculateTimesheet(days, overtimeMinutes.value || 480));
const figures = computed(() => ('error' in result.value ? null : result.value));

async function copyResults() {
    if (!figures.value) {
        return;
    }

    const lines = figures.value.rows.map((row) => `${row.label}: ${row.worked} (overtime ${row.overtime})`);
    await navigator.clipboard.writeText([...lines, `Total: ${figures.value.total}`, `Overtime: ${figures.value.overtime}`].join('\n'));
    copied.value = true;
    track('calculation_completed', 'timesheet-calculator');
}

function downloadCsv() {
    if (!figures.value) {
        return;
    }

    const body = ['Day,Worked,Overtime', ...figures.value.rows.map((row) => `${row.label},${row.worked},${row.overtime}`), `Total,${figures.value.total},${figures.value.overtime}`].join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([body], { type: 'text/csv' }));
    link.download = 'timesheet.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Count overtime after">
                <input v-model="overtimeAfter" type="time" class="field" />
            </FormField>
            <div v-for="day in days" :key="day.label" class="day">
                <p class="label">{{ day.label }}</p>
                <input v-model="day.start" type="time" class="field" aria-label="Start" />
                <input v-model="day.end" type="time" class="field" aria-label="End" />
                <input v-model="day.break_minutes" class="field" inputmode="numeric" aria-label="Break minutes" placeholder="Break min" />
            </div>
            <div class="actions">
                <Button type="button" :disabled="!figures" @click="copyResults">{{ copied ? 'Copied' : 'Copy' }}</Button>
                <Button type="button" variant="outline" :disabled="!figures" @click="downloadCsv">Download CSV</Button>
            </div>
        </form>
        <section class="panel">
            <p v-if="'error' in result" class="error">{{ result.error }}</p>
            <ul v-else class="list">
                <li v-for="row in figures?.rows" :key="row.label">{{ row.label }} · {{ row.worked }} · overtime {{ row.overtime }}</li>
                <li class="total">Total {{ figures?.total }} · overtime {{ figures?.overtime }}</li>
            </ul>
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

.day {
    @apply grid grid-cols-[3rem_1fr_1fr_1fr] items-center gap-2;
}

.label {
    @apply text-sm font-medium;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.actions {
    @apply flex flex-wrap gap-2;
}

.list {
    @apply space-y-2 text-sm;
}

.total {
    @apply font-medium;
}

.error {
    @apply text-sm text-red-700 dark:text-red-300;
}
</style>
