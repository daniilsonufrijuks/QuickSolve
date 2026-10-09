<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

function submit() {
    form.post('/contact', { preserveScroll: true, onSuccess: () => form.reset() });
}
</script>

<template>
    <Head title="Contact" />
    <MarketingLayout>
        <div class="mx-auto max-w-xl px-4 py-12">
            <h1 class="text-3xl font-semibold">Contact</h1>
            <p class="mt-2 text-sm text-slate-600">Messages are stored and a copy is queued to the configured mail inbox.</p>
            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Email" :error="form.errors.email"><input v-model="form.email" type="email" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Subject" :error="form.errors.subject"><input v-model="form.subject" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Message" :error="form.errors.message"><textarea v-model="form.message" rows="6" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <Button type="submit" :disabled="form.processing">Send</Button>
            </form>
        </div>
    </MarketingLayout>
</template>
