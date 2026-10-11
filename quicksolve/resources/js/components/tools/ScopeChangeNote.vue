<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    from_name: '',
    from_email: '',
    client_name: '',
    project_name: '',
    agreed_work: '',
    extra_request: '',
    extra_hours: '',
    extra_amount: '0.00',
    currency: 'EUR',
    new_deadline: '',
    hold_until_confirmed: true,
});

function downloadPdf() {
    const pdfForm = document.createElement('form');
    pdfForm.method = 'POST';
    pdfForm.action = '/tools/scope-change-note/pdf';
    const token = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');
    const fields: Record<string, string> = {
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? token,
        from_name: form.from_name,
        from_email: form.from_email,
        client_name: form.client_name,
        project_name: form.project_name,
        agreed_work: form.agreed_work,
        extra_request: form.extra_request,
        extra_hours: form.extra_hours,
        extra_amount: form.extra_amount,
        currency: form.currency,
        new_deadline: form.new_deadline,
        hold_until_confirmed: form.hold_until_confirmed ? '1' : '0',
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        pdfForm.appendChild(input);
    });

    document.body.appendChild(pdfForm);
    pdfForm.submit();
    pdfForm.remove();
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent="downloadPdf">
            <div class="fields">
                <FormField label="Your name" :error="form.errors.from_name"><input v-model="form.from_name" class="field" /></FormField>
                <FormField label="Your email"><input v-model="form.from_email" class="field" type="email" /></FormField>
                <FormField label="Client name" :error="form.errors.client_name"><input v-model="form.client_name" class="field" /></FormField>
                <FormField label="Job or project" :error="form.errors.project_name"><input v-model="form.project_name" class="field" /></FormField>
            </div>
            <FormField label="What was already agreed" :error="form.errors.agreed_work">
                <textarea v-model="form.agreed_work" class="field" rows="4" placeholder="Homepage redesign, three page templates, two revision rounds." />
            </FormField>
            <FormField label="The extra they asked for" :error="form.errors.extra_request">
                <textarea v-model="form.extra_request" class="field" rows="4" placeholder="Can you also add a shop and migrate the old products?" />
            </FormField>
            <div class="fields">
                <FormField label="Extra hours (optional)"><input v-model="form.extra_hours" class="field" inputmode="decimal" /></FormField>
                <FormField label="Extra cost" :error="form.errors.extra_amount"><input v-model="form.extra_amount" class="field" inputmode="decimal" /></FormField>
                <FormField label="Currency">
                    <select v-model="form.currency" class="field">
                        <option>EUR</option>
                        <option>USD</option>
                        <option>GBP</option>
                    </select>
                </FormField>
                <FormField label="New date if the extra slips the job"><input v-model="form.new_deadline" class="field" type="date" /></FormField>
            </div>
            <label class="check"><input v-model="form.hold_until_confirmed" type="checkbox" /> Do not start the extra work until they confirm</label>
            <Button type="submit">Download confirmation PDF</Button>
        </form>
        <section class="panel preview">
            <p class="kicker">Client-facing note</p>
            <h2>{{ form.project_name || 'Project' }}</h2>
            <p class="meta">{{ form.from_name || 'You' }} → {{ form.client_name || 'Client' }}</p>
            <p><strong>Agreed:</strong> {{ form.agreed_work || 'The original job.' }}</p>
            <p><strong>Extra:</strong> {{ form.extra_request || 'The “can you just…” request.' }}</p>
            <p><strong>Cost of extra:</strong> {{ form.extra_amount }} {{ form.currency }}</p>
            <p v-if="form.hold_until_confirmed" class="hold">Work on the extra does not start until they reply confirmed.</p>
            <p class="meta">This is not a lawyer’s contract. It is a written recap so the extra is not free by default.</p>
        </section>
    </div>
</template>

<style scoped>
.layout {
    @apply grid gap-6 xl:grid-cols-2;
}

.panel {
    @apply space-y-4 rounded-xl border p-5;
}

.fields {
    @apply grid gap-3 sm:grid-cols-2;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.check {
    @apply flex items-center gap-2 text-sm;
}

.preview h2 {
    @apply text-xl font-semibold;
}

.kicker,
.meta {
    @apply text-sm text-slate-500;
}

.hold {
    @apply font-semibold;
}
</style>
