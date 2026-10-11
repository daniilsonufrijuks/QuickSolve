<script setup lang="ts">
import Pagination from '@/components/marketing/Pagination.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps<{
    documents: { data: Array<{ id: number; title: string; document_type?: string; created_at: string }>; meta: { current_page: number; last_page: number } };
    business?: boolean;
}>();

function remove(id: number) {
    router.delete(`/invoices/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Documents" />
    <AppLayout :breadcrumbs="[{ title: 'Documents', href: '/dashboard/documents' }]">
        <div class="sx862vk">
            <div class="head">
                <h1 class="sixq2vr">Saved documents</h1>
                <span v-if="business" class="exports">
                    <Button as-child variant="outline"><a href="/dashboard/documents/export">Export documents CSV</a></Button>
                    <Button as-child variant="outline"><a href="/dashboard/usage/export">Export usage CSV</a></Button>
                </span>
            </div>
            <ul class="sm6llz1">
                <li v-for="document in documents.data" :key="document.id" class="s1dhwkrk">
                    <span>{{ document.title }}{{ document.document_type ? ` (${document.document_type})` : '' }}</span>
                    <span class="s1rwv5zo">
                        <Button as-child variant="outline"><a :href="`/invoices/${document.id}/file`">PDF</a></Button>
                        <Button variant="ghost" type="button" @click="remove(document.id)">Delete</Button>
                    </span>
                </li>
            </ul>
            <p v-if="documents.data.length === 0" class="syp5vk7">Save an invoice or quote from the generators to see it here.</p>
            <p v-else-if="!business" class="syp5vk7">CSV exports of documents and usage are included with Business.</p>
            <Pagination :meta="documents.meta" path="/dashboard/documents" />
        </div>
    </AppLayout>
</template>

<style scoped>
.sx862vk {
    @apply space-y-4 p-4;
}

.head {
    @apply flex flex-wrap items-center justify-between gap-3;
}

.exports {
    @apply flex flex-wrap gap-2;
}

.sixq2vr {
    @apply text-2xl font-semibold;
}

.sm6llz1 {
    @apply divide-y rounded-xl border;
}

.s1dhwkrk {
    @apply flex items-center justify-between gap-3 p-3 text-sm;
}

.s1rwv5zo {
    @apply flex gap-2;
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}
</style>
