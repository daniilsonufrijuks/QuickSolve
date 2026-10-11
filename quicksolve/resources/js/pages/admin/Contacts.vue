<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    messages: {
        data: Array<{ id: number; name: string; email: string; subject: string; message: string; is_read: boolean; created_at: string }>;
    };
}>();

const editingId = ref<number | null>(null);
const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    is_read: false,
});

function edit(message: (typeof props.messages.data)[number]) {
    editingId.value = message.id;
    form.name = message.name;
    form.email = message.email;
    form.subject = message.subject;
    form.message = message.message;
    form.is_read = message.is_read;
}

function resetForm() {
    editingId.value = null;
    form.reset();
}

function save() {
    if (editingId.value) {
        form.put(`/admin/contacts/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/admin/contacts', { onSuccess: resetForm });
}
</script>

<template>
    <Head title="Messages" />
    <AppLayout :breadcrumbs="[{ title: 'Messages', href: '/admin/contacts' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update message' : 'Add a message' }}</h1>
                    <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="field" /></FormField>
                    <FormField label="Email" :error="form.errors.email"><input v-model="form.email" type="email" class="field" /></FormField>
                    <FormField label="Subject" :error="form.errors.subject"><input v-model="form.subject" class="field" /></FormField>
                    <FormField label="Message" :error="form.errors.message"><textarea v-model="form.message" rows="5" class="field" /></FormField>
                    <label class="check"><input v-model="form.is_read" type="checkbox" /> Read</label>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">Save message</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <ul class="list">
                    <li v-for="message in messages.data" :key="message.id" class="card">
                        <p class="name">{{ message.subject }} · {{ message.is_read ? 'Read' : 'Unread' }}</p>
                        <p class="meta">{{ message.name }} · {{ message.email }}</p>
                        <p class="body">{{ message.message }}</p>
                        <div class="actions">
                            <Button type="button" variant="outline" @click="edit(message)">Edit</Button>
                            <Button v-if="!message.is_read" type="button" variant="outline" @click="router.post(`/admin/contacts/${message.id}/read`)">Mark read</Button>
                            <Button type="button" variant="ghost" @click="router.delete(`/admin/contacts/${message.id}`)">Delete</Button>
                        </div>
                    </li>
                </ul>
            </div>
            <p v-if="messages.data.length === 0" class="meta">No messages yet.</p>
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
    @apply text-2xl font-semibold;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.check {
    @apply flex gap-2 text-sm;
}

.card {
    @apply space-y-2 rounded-xl border p-4;
}

.name {
    @apply font-medium;
}

.meta,
.body {
    @apply text-sm leading-6;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
