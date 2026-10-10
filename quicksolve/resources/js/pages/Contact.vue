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
        <div class="s128wfte">
            <h1 class="s15bg7bs">Contact</h1>
            <p class="swr3obg">Messages are stored and a copy is queued to the configured mail inbox.</p>
            <form class="smmcoax" @submit.prevent="submit">
                <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="s1wl1yqe" /></FormField>
                <FormField label="Email" :error="form.errors.email"><input v-model="form.email" type="email" class="s1wl1yqe" /></FormField>
                <FormField label="Subject" :error="form.errors.subject"><input v-model="form.subject" class="s1wl1yqe" /></FormField>
                <FormField label="Message" :error="form.errors.message"><textarea v-model="form.message" rows="6" class="s1wl1yqe" /></FormField>
                <Button type="submit" :disabled="form.processing">Send</Button>
            </form>
        </div>
    </MarketingLayout>
</template>

<style scoped>
.s128wfte {
    @apply mx-auto max-w-xl px-4 py-12;
}

.s15bg7bs {
    @apply text-3xl font-semibold;
}

.swr3obg {
    @apply mt-2 text-sm text-slate-600;
}

.smmcoax {
    @apply mt-6 space-y-4;
}

.s1wl1yqe {
    @apply w-full rounded-lg border px-3 py-2;
}
</style>
