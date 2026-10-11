<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    documents: {
        data: Array<{ id: number; title: string; document_type: string; user?: { email?: string } }>;
    };
}>();

const editingId = ref<number | null>(null);
const form = useForm({ title: '' });

function edit(document: (typeof props.documents.data)[number]) {
    editingId.value = document.id;
    form.title = document.title;
}

function save() {
    if (!editingId.value) {
        return;
    }

    form.put(`/admin/documents/${editingId.value}`, {
        onSuccess: () => {
            editingId.value = null;
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Documents" />
    <AppLayout :breadcrumbs="[{ title: 'Documents', href: '/admin/documents' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Saved documents</h1>
            <p class="copy">Invoice files stay on the private disk. You can rename a record or delete it. New invoices are created from the invoice tool.</p>
            <form v-if="editingId" class="form" @submit.prevent="save">
                <FormField label="Title" :error="form.errors.title"><input v-model="form.title" class="field" /></FormField>
                <div class="actions">
                    <Button type="submit" :disabled="form.processing">Save title</Button>
                    <Button type="button" variant="ghost" @click="editingId = null">Cancel</Button>
                </div>
            </form>
            <ul class="list">
                <li v-for="document in documents.data" :key="document.id" class="row">
                    <div>
                        <p class="name">{{ document.title }}</p>
                        <p class="meta">{{ document.document_type }} · {{ document.user?.email }}</p>
                    </div>
                    <div class="actions">
                        <Button type="button" variant="outline" @click="edit(document)">Edit</Button>
                        <Button type="button" variant="ghost" @click="router.delete(`/admin/documents/${document.id}`)">Delete</Button>
                    </div>
                </li>
            </ul>
            <p v-if="documents.data.length === 0" class="meta">No saved documents.</p>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply space-y-4 p-4;
}

.title {
    @apply text-2xl font-semibold;
}

.copy,
.meta {
    @apply text-sm text-slate-600;
}

.form {
    @apply max-w-xl space-y-3;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.list {
    @apply space-y-2;
}

.row {
    @apply flex items-center justify-between gap-3 rounded-xl border p-3;
}

.name {
    @apply font-medium;
}

.actions {
    @apply flex gap-2;
}
</style>
