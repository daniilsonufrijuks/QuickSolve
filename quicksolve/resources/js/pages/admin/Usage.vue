<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    rows: Array<{ usage_type: string; tool_slug: string; total: number }>;
    messages: Array<{ id: number; name: string; email: string; subject: string; message: string }>;
}>();
</script>

<template>
    <Head title="Usage" />
    <AppLayout :breadcrumbs="[{ title: 'Usage', href: '/admin/usage' }]">
        <div class="page">
            <AdminNav />
            <section>
                <h1 class="title">Usage, last 30 days</h1>
                <ul class="list">
                    <li v-for="row in rows" :key="`${row.usage_type}-${row.tool_slug}`">{{ row.tool_slug }} · {{ row.usage_type }} · {{ row.total }}</li>
                    <li v-if="rows.length === 0" class="muted">No events yet.</li>
                </ul>
            </section>
            <section>
                <h2 class="title">Recent contact messages</h2>
                <p class="copy"><Link href="/admin/contacts" class="link">Open the inbox</Link> to mark messages read or delete them.</p>
                <ul class="cards">
                    <li v-for="message in messages" :key="message.id" class="card">
                        <p class="name">{{ message.subject }}</p>
                        <p class="muted">{{ message.name }} · {{ message.email }}</p>
                        <p class="body">{{ message.message }}</p>
                    </li>
                </ul>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply space-y-8 p-4;
}

.title {
    @apply text-xl font-semibold;
}

.list {
    @apply mt-3 space-y-1 text-sm;
}

.muted,
.copy,
.body {
    @apply text-sm;
}

.link {
    @apply text-blue-800 underline dark:text-blue-200;
}

.cards {
    @apply mt-3 space-y-3;
}

.card {
    @apply rounded-lg border p-3;
}

.name {
    @apply font-medium;
}

.body {
    @apply mt-1;
}
</style>
