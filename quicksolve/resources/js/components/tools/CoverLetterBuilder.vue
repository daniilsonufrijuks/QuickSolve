<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    full_name: '',
    headline: '',
    email: '',
    phone: '',
    location: '',
    recipient_name: '',
    recipient_title: '',
    company: '',
    role: '',
    body: '',
});

function downloadPdf() {
    const pdfForm = document.createElement('form');
    pdfForm.method = 'POST';
    pdfForm.action = '/tools/cover-letter/pdf';
    const token = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');
    const fields: Record<string, string> = {
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? token,
        ...form.data(),
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
                <FormField label="Your name" :error="form.errors.full_name"><input v-model="form.full_name" class="field" /></FormField>
                <FormField label="Headline"><input v-model="form.headline" class="field" /></FormField>
                <FormField label="Email"><input v-model="form.email" type="email" class="field" /></FormField>
                <FormField label="Phone"><input v-model="form.phone" class="field" /></FormField>
                <FormField label="Location"><input v-model="form.location" class="field" /></FormField>
                <FormField label="Role you are applying for" :error="form.errors.role"><input v-model="form.role" class="field" /></FormField>
                <FormField label="Hiring manager" :error="form.errors.recipient_name"><input v-model="form.recipient_name" class="field" /></FormField>
                <FormField label="Their title"><input v-model="form.recipient_title" class="field" /></FormField>
                <FormField label="Company" :error="form.errors.company"><input v-model="form.company" class="field" /></FormField>
            </div>
            <FormField label="Letter body" :error="form.errors.body">
                <textarea v-model="form.body" rows="10" class="field" placeholder="Why this role, what you shipped, and what you want next." />
            </FormField>
            <Button type="submit">Download PDF</Button>
        </form>
        <section class="panel preview">
            <p class="kicker">Preview</p>
            <h2>{{ form.full_name || 'Your name' }}</h2>
            <p class="meta">{{ form.headline }}</p>
            <p>Dear {{ form.recipient_name || 'hiring manager' }},</p>
            <p class="body">{{ form.body || 'Write two or three short paragraphs. Keep claims specific.' }}</p>
            <p>Yours sincerely,<br />{{ form.full_name || 'Your name' }}</p>
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
    @apply grid gap-4 sm:grid-cols-2;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.kicker {
    @apply text-xs uppercase tracking-wide text-slate-500;
}

.preview h2 {
    @apply mt-1 text-2xl font-semibold;
}

.meta,
.body {
    @apply whitespace-pre-wrap text-sm leading-6;
}
</style>
