<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { postJson, track } from '@/lib/api';
import { reactive, ref } from 'vue';

type Result = {
    issue_date: string;
    contractual_due: string;
    likely_pay_date: string;
    likely_land_date: string;
    gap_days: number;
    settlement_days: number;
    notice: string;
};

const form = reactive({
    issue_date: new Date().toISOString().slice(0, 10),
    terms: 'net_30',
    pay_run: 'weekly',
    weekday: 5,
    month_day: 28,
    skip_weekends: true,
    settlement_days: 2,
    holidays: '',
});

const result = ref<Result | null>(null);
const error = ref('');
const pending = ref(false);

async function calculate() {
    pending.value = true;
    error.value = '';
    result.value = null;

    const holidays = form.holidays
        .split(/[\n,]+/)
        .map((value) => value.trim())
        .filter((value) => /^\d{4}-\d{2}-\d{2}$/.test(value));

    const response = await postJson<Result>('/tools/payment-landing-date/calculate', {
        issue_date: form.issue_date,
        terms: form.terms,
        pay_run: form.pay_run,
        weekday: form.pay_run === 'weekly' ? form.weekday : null,
        month_day: form.pay_run === 'monthly_day' ? form.month_day : null,
        skip_weekends: form.skip_weekends,
        settlement_days: form.settlement_days,
        holiday_dates: holidays,
    });

    pending.value = false;

    if (!response.ok || !response.data) {
        error.value = response.message;
        return;
    }

    result.value = response.data;
    track('calculation_completed', 'payment-landing-date');
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent="calculate">
            <FormField label="Invoice date">
                <input v-model="form.issue_date" class="field" type="date" required />
            </FormField>
            <FormField label="Payment terms on the invoice">
                <select v-model="form.terms" class="field">
                    <option value="net_7">Net 7</option>
                    <option value="net_14">Net 14</option>
                    <option value="net_30">Net 30</option>
                    <option value="net_45">Net 45</option>
                    <option value="net_60">Net 60</option>
                    <option value="eom">End of invoice month</option>
                    <option value="eom_plus_15">End of month + 15 days</option>
                </select>
            </FormField>
            <FormField label="When this client actually pays">
                <select v-model="form.pay_run" class="field">
                    <option value="any">Any day (use the due date)</option>
                    <option value="weekly">A set weekday each week</option>
                    <option value="monthly_day">A set day each month (1–28)</option>
                    <option value="last_weekday">Last weekday of the month</option>
                </select>
            </FormField>
            <FormField v-if="form.pay_run === 'weekly'" label="Pay-run weekday">
                <select v-model.number="form.weekday" class="field">
                    <option :value="1">Monday</option>
                    <option :value="2">Tuesday</option>
                    <option :value="3">Wednesday</option>
                    <option :value="4">Thursday</option>
                    <option :value="5">Friday</option>
                </select>
            </FormField>
            <FormField v-if="form.pay_run === 'monthly_day'" label="Pay-run day of month">
                <input v-model.number="form.month_day" class="field" inputmode="numeric" min="1" max="28" />
            </FormField>
            <label class="check"><input v-model="form.skip_weekends" type="checkbox" /> Move weekend dates to the next weekday</label>
            <FormField label="Bank or card clearing days after they pay">
                <input v-model.number="form.settlement_days" class="field" inputmode="numeric" min="0" max="14" />
            </FormField>
            <FormField label="Public holidays this client skips (YYYY-MM-DD, one per line)">
                <textarea v-model="form.holidays" class="field" rows="3" placeholder="2026-12-25" />
            </FormField>
            <Button type="submit" :disabled="pending">{{ pending ? 'Calculating…' : 'Estimate cash landing' }}</Button>
        </form>
        <section class="panel">
            <p v-if="error" class="error">{{ error }}</p>
            <dl v-else-if="result" class="facts">
                <div><dt>On the invoice, due</dt><dd>{{ result.contractual_due }}</dd></div>
                <div><dt>Likely pay-run date</dt><dd>{{ result.likely_pay_date }}</dd></div>
                <div><dt>Likely in your account</dt><dd>{{ result.likely_land_date }}</dd></div>
                <div>
                    <dt>Later than the due date</dt>
                    <dd v-if="result.gap_days > 0">{{ result.gap_days }} day{{ result.gap_days === 1 ? '' : 's' }}</dd>
                    <dd v-else>Not later — landing is on or before the due date</dd>
                </div>
            </dl>
            <p v-else class="meta">Most due-date tools stop at Net 30. This one adds the client’s pay-run, weekends, holidays you type, and clearing days.</p>
            <p v-if="result" class="meta">{{ result.notice }} Do not spend the money until it is in the account.</p>
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

.facts {
    @apply space-y-3 text-sm;
}

.meta,
.error {
    @apply text-sm leading-6;
}
</style>
