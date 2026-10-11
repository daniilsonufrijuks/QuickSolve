<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps<{
    rows: Array<{ usage_type: string; tool_slug: string; total: number }>;
    events: {
        data: Array<{ id: number; usage_type: string; tool_slug: string; created_at: string; user?: { email?: string } | null }>;
    };
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
                <h2 class="title">Recent events</h2>
                <ul class="cards">
                    <li v-for="event in events.data" :key="event.id" class="card">
                        <p class="name">{{ event.tool_slug }} · {{ event.usage_type }}</p>
                        <p class="muted">{{ event.user?.email ?? 'Guest' }} · {{ event.created_at }}</p>
                        <Button type="button" variant="ghost" @click="router.delete(`/admin/usage/${event.id}`)">Delete</Button>
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

.muted {
    @apply text-sm;
}

.cards {
    @apply mt-3 space-y-3;
}

.card {
    @apply flex items-center justify-between gap-3 rounded-lg border p-3;
}

.name {
    @apply font-medium;
}
</style>
