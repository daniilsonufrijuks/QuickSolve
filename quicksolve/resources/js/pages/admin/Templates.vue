<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    templates: Array<any>;
    categories: Array<{ id: number; name: string }>;
}>();

const editingId = ref<number | null>(null);

const form = useForm({
    category_id: props.categories[0]?.id ?? '',
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

function edit(template: any) {
    editingId.value = template.id;
    form.category_id = template.category_id;
    form.name = template.name;
    form.slug = template.slug;
    form.description = template.description;
    form.whats_included = template.whats_included ?? '';
    form.price = template.price;
    form.currency = template.currency;
    form.is_featured = template.is_featured;
    form.is_published = template.is_published;
    form.file = null;
    form.preview = null;
    form.clearErrors();
}

function resetForm() {
    editingId.value = null;
    form.reset();
}

function save() {
    if (editingId.value) {
        form.put(`/admin/templates/${editingId.value}`, { forceFormData: true });
        return;
    }

    form.post('/admin/templates', { forceFormData: true });
}
</script>

<template>
    <Head title="Manage templates" />
    <AppLayout :breadcrumbs="[{ title: 'Templates', href: '/admin/templates' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update template' : 'New template' }}</h1>
                    <p class="copy">Downloads stay on the private disk. Publishing without a file is blocked.</p>
                    <FormField label="Category"><select v-model="form.category_id" class="field"><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></FormField>
                    <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="field" /></FormField>
                    <FormField label="Slug" :error="form.errors.slug"><input v-model="form.slug" class="field" /></FormField>
                    <FormField label="Description"><textarea v-model="form.description" rows="4" class="field" /></FormField>
                    <FormField label="What's included" hint="One item per line."><textarea v-model="form.whats_included" rows="3" class="field" /></FormField>
                    <FormField label="Price in cents" :error="form.errors.price"><input v-model.number="form.price" type="number" class="field" /></FormField>
                    <FormField label="File" :error="form.errors.file"><input type="file" @change="form.file = ($event.target as HTMLInputElement).files?.[0] ?? null" /></FormField>
                    <label class="check"><input v-model="form.is_published" type="checkbox" /> Published</label>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">{{ editingId ? 'Save changes' : 'Upload template' }}</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <ul class="list">
                    <li v-for="template in templates" :key="template.id" class="row">
                        <p>{{ template.name }} · {{ template.price }} {{ template.currency }} · {{ template.has_file ? 'File ready' : 'Missing file' }} · {{ template.is_published ? 'Published' : 'Draft' }}</p>
                        <div class="actions">
                            <Button type="button" variant="outline" @click="edit(template)">Edit</Button>
                            <Button type="button" variant="outline" @click="router.post(`/admin/templates/${template.id}/toggle`)">{{ template.is_published ? 'Unpublish' : 'Publish' }}</Button>
                            <Button type="button" variant="ghost" @click="router.delete(`/admin/templates/${template.id}`)">Delete</Button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply space-y-4 p-4;
}

.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.form,
.list {
    @apply space-y-3;
}

.title {
    @apply text-xl font-semibold;
}

.copy {
    @apply text-sm;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.check {
    @apply flex gap-2 text-sm;
}

.row {
    @apply space-y-2 rounded-lg border p-3 text-sm;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
