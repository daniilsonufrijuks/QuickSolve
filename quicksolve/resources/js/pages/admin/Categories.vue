<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{ categories: Array<any> }>();

const editingId = ref<number | null>(null);
const form = useForm({ name: '', slug: '', type: 'tool', description: '' });

function edit(category: any) {
    editingId.value = category.id;
    form.name = category.name;
    form.slug = category.slug;
    form.type = category.type;
    form.description = category.description ?? '';
}

function resetForm() {
    editingId.value = null;
    form.reset();
}

function save() {
    if (editingId.value) {
        form.put(`/admin/categories/${editingId.value}`);
        return;
    }

    form.post('/admin/categories');
}
</script>

<template>
    <Head title="Categories" />
    <AppLayout :breadcrumbs="[{ title: 'Categories', href: '/admin/categories' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update category' : 'New category' }}</h1>
                    <FormField label="Name"><input v-model="form.name" class="field" /></FormField>
                    <FormField label="Slug" :error="form.errors.slug"><input v-model="form.slug" class="field" /></FormField>
                    <FormField label="Type"><select v-model="form.type" class="field"><option value="tool">Tool</option><option value="template">Template</option></select></FormField>
                    <FormField label="Description"><textarea v-model="form.description" class="field" /></FormField>
                    <div class="actions">
                        <Button type="submit">Save category</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <ul class="list">
                    <li v-for="category in categories" :key="category.id" class="row">
                        <span>{{ category.name }} · {{ category.type }}</span>
                        <div class="actions">
                            <Button type="button" variant="outline" @click="edit(category)">Edit</Button>
                            <Button type="button" variant="ghost" @click="router.delete(`/admin/categories/${category.id}`)">Delete</Button>
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

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.row {
    @apply flex items-center justify-between gap-3 rounded-lg border p-3 text-sm;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
