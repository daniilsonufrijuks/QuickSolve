<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps<{
    messages: {
        data: Array<{ id: number; name: string; email: string; subject: string; message: string; is_read: boolean; created_at: string }>;
    };
}>();
</script>

<template>
    <Head title="Messages" />
    <AppLayout :breadcrumbs="[{ title: 'Messages', href: '/admin/contacts' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Contact messages</h1>
            <ul class="list">
                <li v-for="message in messages.data" :key="message.id" class="card">
                    <p class="name">{{ message.subject }} · {{ message.is_read ? 'Read' : 'Unread' }}</p>
                    <p class="meta">{{ message.name }} · {{ message.email }}</p>
                    <p class="body">{{ message.message }}</p>
                    <div class="actions">
                        <Button v-if="!message.is_read" type="button" variant="outline" @click="router.post(`/admin/contacts/${message.id}/read`)">Mark read</Button>
                        <Button type="button" variant="ghost" @click="router.delete(`/admin/contacts/${message.id}`)">Delete</Button>
                    </div>
                </li>
            </ul>
            <p v-if="messages.data.length === 0" class="meta">No messages yet.</p>
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

.list {
    @apply space-y-3;
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
    @apply flex gap-2;
}
</style>
