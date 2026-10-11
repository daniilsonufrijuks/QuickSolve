<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    settings: Array<{ id: number; key: string; value: string | null }>;
}>();

const editingId = ref<number | null>(null);
const form = useForm({ key: '', value: '' });

function edit(setting: (typeof props.settings)[number]) {
    editingId.value = setting.id;
    form.key = setting.key;
    form.value = setting.value ?? '';
}

function resetForm() {
    editingId.value = null;
    form.reset();
}

function save() {
    if (editingId.value) {
        form.put(`/admin/settings/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/admin/settings', { onSuccess: resetForm });
}
</script>

<template>
    <Head title="Settings" />
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: '/admin/settings' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update setting' : 'Add a setting' }}</h1>
                    <FormField label="Key" :error="form.errors.key"><input v-model="form.key" class="field" /></FormField>
                    <FormField label="Value" :error="form.errors.value"><textarea v-model="form.value" rows="3" class="field" /></FormField>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">Save setting</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <ul class="list">
                    <li v-for="setting in settings" :key="setting.id" class="row">
                        <div>
                            <p class="name">{{ setting.key }}</p>
                            <p class="meta">{{ setting.value }}</p>
                        </div>
                        <div class="actions">
                            <Button type="button" variant="outline" @click="edit(setting)">Edit</Button>
                            <Button type="button" variant="ghost" @click="router.delete(`/admin/settings/${setting.id}`)">Delete</Button>
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
    @apply flex items-center justify-between gap-3 rounded-xl border p-3;
}

.name {
    @apply font-medium;
}

.meta {
    @apply break-all text-sm text-slate-600;
}

.actions {
    @apply flex gap-2;
}
</style>
