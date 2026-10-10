<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    filters: { q: string };
    users: {
        data: Array<{ id: number; name: string; email: string; is_admin: boolean; created_at: string }>;
        current_page: number;
        last_page: number;
    };
}>();

const query = ref(props.filters.q ?? '');

function search() {
    router.get('/admin/users', { q: query.value }, { preserveState: true });
}

function setAdmin(user: { id: number }, isAdmin: boolean) {
    useForm({ is_admin: isAdmin }).put(`/admin/users/${user.id}`);
}
</script>

<template>
    <Head title="Users" />
    <AppLayout :breadcrumbs="[{ title: 'Users', href: '/admin/users' }]">
        <div class="page">
            <AdminNav />
            <h1 class="title">Users</h1>
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
                    <Button type="button" variant="outline" @click="setAdmin(user, !user.is_admin)">
                        {{ user.is_admin ? 'Remove admin' : 'Make admin' }}
                    </Button>
                </li>
            </ul>
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

.search {
    @apply flex max-w-xl gap-2;
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

.meta {
    @apply text-sm;
}
</style>
