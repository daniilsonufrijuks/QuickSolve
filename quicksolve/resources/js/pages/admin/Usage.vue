<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    rows: Array<{ usage_type: string; tool_slug: string; total: number }>;
    messages: Array<{ id: number; name: string; email: string; subject: string; message: string }>;
}>();
</script>

<template>
    <Head title="Usage" />
    <AppLayout :breadcrumbs="[{ title: 'Usage', href: '/admin/usage' }]">
        <div class="space-y-8 p-4">
            <section>
                <h1 class="text-xl font-semibold">Usage, last 30 days</h1>
                <ul class="mt-3 space-y-1 text-sm">
                    <li v-for="row in rows" :key="`${row.usage_type}-${row.tool_slug}`">{{ row.tool_slug }} · {{ row.usage_type }} · {{ row.total }}</li>
                    <li v-if="rows.length === 0" class="text-slate-500">No events yet.</li>
                </ul>
            </section>
            <section>
                <h2 class="text-xl font-semibold">Contact messages</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    <li v-for="message in messages" :key="message.id" class="rounded-lg border p-3">
                        <p class="font-medium">{{ message.subject }}</p>
                        <p class="text-slate-500">{{ message.name }} · {{ message.email }}</p>
                        <p class="mt-1">{{ message.message }}</p>
                    </li>
                </ul>
            </section>
        </div>
    </AppLayout>
</template>
