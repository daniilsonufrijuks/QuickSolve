<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { track } from '@/lib/api';
import { calculateFreelanceRate, type FreelanceInput, type FreelanceResult } from '@/lib/freelance';
import { Link } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

defineProps<{ premium: boolean }>();

const form = reactive<FreelanceInput>({
    income_target: '60000.00',
    annual_expenses: '6000.00',
    hours_per_day: '5',
    days_per_week: '5',
    weeks_per_year: '48',
    unpaid_leave_days: '10',
    currency: 'EUR',
});

const copied = ref(false);
const result = computed(() => calculateFreelanceRate(form));
const figures = computed(() => ('error' in result.value ? null : (result.value as FreelanceResult)));

const rows = computed(() => {
    if (!figures.value) {
        return [];
    }

    return [
        ['Required revenue', figures.value.required_revenue],
        ['Available days', String(figures.value.available_days)],
        ['Billable hours', figures.value.billable_hours],
        ['Hourly rate', figures.value.hourly_rate],
        ['Daily rate', figures.value.daily_rate],
    ];
});

function reset() {
    Object.assign(form, {
        income_target: '',
        annual_expenses: '',
        hours_per_day: '',
        days_per_week: '5',
        weeks_per_year: '48',
        unpaid_leave_days: '0',
        currency: 'EUR',
    });
}

async function copyResults() {
    if (!figures.value) {
        return;
    }

    const text = rows.value.map(([label, value]) => `${label}: ${value}`).join('\n');
    await navigator.clipboard.writeText(text);
    copied.value = true;
    track('calculation_completed', 'freelance-rate-calculator');
}

function downloadReport() {
    if (!figures.value) {
        return;
    }

    const report = document.createElement('form');
    report.method = 'POST';
    report.action = '/tools/freelance-rate-calculator/report';
    const token = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';
    const fields: Record<string, string> = {
        _token: token,
        income_target: form.income_target,
        annual_expenses: form.annual_expenses,
        hours_per_day: form.hours_per_day,
        days_per_week: form.days_per_week,
        weeks_per_year: form.weeks_per_year,
        unpaid_leave_days: form.unpaid_leave_days === '' ? '0' : form.unpaid_leave_days,
        currency: form.currency,
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        report.appendChild(input);
    });

    document.body.appendChild(report);
    report.submit();
    report.remove();
}
</script>

<template>
    <div class="s16x0eqh">
        <form class="s1r0jak5" @submit.prevent>
            <div class="s9tb7ek">
                <FormField label="Income target" hint="What you want left after business expenses, for the year.">
                    <input v-model="form.income_target" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Annual expenses" hint="Software, insurance, equipment, and other business costs.">
                    <input v-model="form.annual_expenses" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Billable hours per day">
                    <input v-model="form.hours_per_day" inputmode="decimal" class="s1wl1yqe" />
                </FormField>
                <FormField label="Currency">
                    <select v-model="form.currency" class="s1wl1yqe">
                        <option>EUR</option>
                        <option>USD</option>
                        <option>GBP</option>
                    </select>
                </FormField>
                <FormField label="Working days per week">
                    <input v-model="form.days_per_week" inputmode="numeric" class="s1wl1yqe" />
                </FormField>
                <FormField label="Working weeks per year">
                    <input v-model="form.weeks_per_year" inputmode="numeric" class="s1wl1yqe" />
                </FormField>
                <FormField label="Unpaid leave days" hint="Days you do not bill. They reduce the available days.">
                    <input v-model="form.unpaid_leave_days" inputmode="numeric" class="s1wl1yqe" />
                </FormField>
            </div>
            <div class="s1sdudaq">
                <Button type="button" variant="outline" @click="reset">Reset</Button>
                <Button type="button" :disabled="!figures" @click="copyResults">{{ copied ? 'Copied' : 'Copy results' }}</Button>
                <Button v-if="premium" type="button" variant="outline" :disabled="!figures" @click="downloadReport">Download rate report</Button>
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
                Required revenue is the income target plus expenses. The hourly rate splits that revenue across billable hours. The daily rate splits it across available days.
            </p>
            <p v-if="!premium" class="s19ltth3">
                A downloadable rate report is included with Pro and Business.
                <Link href="/pricing" class="s1lufftk">View plans</Link>
            </p>
            <p class="s19ltth3">
                The
                <Link href="/templates/freelance-business-starter-kit" class="s1lufftk">Freelance Business Starter Kit</Link>
                is a separate one-time download for setting up an offer and a weekly review.
            </p>
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

.s1lufftk {
    @apply font-medium text-blue-800 underline dark:text-blue-300;
}
</style>
