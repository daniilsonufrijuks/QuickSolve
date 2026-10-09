<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    templates: Array<any>;
    categories: Array<{ id: number; name: string }>;
}>();

const form = useForm({
    category_id: '',
    name: '',
    slug: '',
    description: '',
    whats_included: '',
    price: 1900,
    currency: 'EUR',
    is_featured: false,
    is_published: false,
    file: null as File | null,
    preview: null as File | null,
});

function save() {
    form.post('/admin/templates', { forceFormData: true });
}
</script>

<template>
    <Head title="Manage templates" />
    <AppLayout :breadcrumbs="[{ title: 'Templates', href: '/admin/templates' }]">
        <div class="grid gap-6 p-4 lg:grid-cols-2">
            <form class="space-y-3" @submit.prevent="save">
                <h1 class="text-xl font-semibold">New template</h1>
                <p class="text-sm text-slate-600">The download is stored on the private disk. Publishing without a file is blocked on create.</p>
                <FormField label="Category"><select v-model="form.category_id" class="w-full rounded-lg border px-3 py-2"><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></FormField>
                <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Slug" :error="form.errors.slug"><input v-model="form.slug" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Description"><textarea v-model="form.description" rows="4" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="What's included" hint="One item per line."><textarea v-model="form.whats_included" rows="3" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Price in cents" :error="form.errors.price"><input v-model.number="form.price" type="number" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="File" :error="form.errors.file"><input type="file" @change="form.file = ($event.target as HTMLInputElement).files?.[0] ?? null" /></FormField>
                <label class="flex gap-2 text-sm"><input v-model="form.is_published" type="checkbox" /> Published</label>
                <Button type="submit" :disabled="form.processing">Upload template</Button>
            </form>
            <ul class="space-y-2 text-sm">
                <li v-for="template in templates" :key="template.id" class="rounded-lg border p-3">
                    {{ template.name }} · {{ template.price }} {{ template.currency }} · {{ template.has_file ? 'File ready' : 'Missing file' }} · {{ template.is_published ? 'Published' : 'Draft' }}
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
