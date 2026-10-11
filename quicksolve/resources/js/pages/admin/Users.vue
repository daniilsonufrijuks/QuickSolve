<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    filters: { q: string };
    users: {
        data: Array<{ id: number; name: string; email: string; is_admin: boolean; email_verified_at: string | null; created_at: string }>;
        current_page: number;
        last_page: number;
    };
}>();

const query = ref(props.filters.q ?? '');
const editingId = ref<number | null>(null);
const form = useForm({
    name: '',
    email: '',
    password: '',
    is_admin: false,
    email_verified: true,
});

function search() {
    router.get('/admin/users', { q: query.value }, { preserveState: true });
}

function edit(user: (typeof props.users.data)[number]) {
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.is_admin = user.is_admin;
    form.email_verified = Boolean(user.email_verified_at);
    form.clearErrors();
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.email_verified = true;
}

function save() {
    if (editingId.value) {
        form.put(`/admin/users/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/admin/users', { onSuccess: resetForm });
}
</script>

<template>
    <Head title="Users" />
    <AppLayout :breadcrumbs="[{ title: 'Users', href: '/admin/users' }]">
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update user' : 'Create a user' }}</h1>
                    <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="field" /></FormField>
                    <FormField label="Email" :error="form.errors.email"><input v-model="form.email" type="email" class="field" /></FormField>
                    <FormField :label="editingId ? 'New password (optional)' : 'Password'" :error="form.errors.password">
                        <input v-model="form.password" type="password" class="field" autocomplete="new-password" />
                    </FormField>
                    <label class="check"><input v-model="form.is_admin" type="checkbox" /> Administrator</label>
                    <label class="check"><input v-model="form.email_verified" type="checkbox" /> Email verified</label>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">Save user</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <div>
                    <form class="search" @submit.prevent="search">
                        <input v-model="query" class="field" placeholder="Search name or email" />
                        <Button type="submit">Search</Button>
                    </form>
                    <ul class="list">
                        <li v-for="user in users.data" :key="user.id" class="row">
                            <div>
                                <p class="name">{{ user.name }}</p>
                                <p class="meta">{{ user.email }} · {{ user.is_admin ? 'Administrator' : 'Member' }}</p>
                            </div>
                            <div class="actions">
                                <Button type="button" variant="outline" @click="edit(user)">Edit</Button>
                                <Button type="button" variant="ghost" @click="router.delete(`/admin/users/${user.id}`)">Delete</Button>
                            </div>
                        </li>
                    </ul>
                </div>
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

.search {
    @apply mb-3 flex gap-2;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.check {
    @apply flex gap-2 text-sm;
}

.row {
    @apply flex items-center justify-between gap-3 rounded-lg border p-3 text-sm;
}

.name {
    @apply font-medium;
}

.meta {
    @apply text-sm text-slate-600;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
