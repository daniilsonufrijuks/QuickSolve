<script setup lang="ts">
import Pagination from '@/components/marketing/Pagination.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps<{
    documents: { data: Array<{ id: number; title: string; created_at: string }>; meta: { current_page: number; last_page: number } };
}>();

function remove(id: number) {
    router.delete(`/invoices/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Documents" />
    <AppLayout :breadcrumbs="[{ title: 'Documents', href: '/dashboard/documents' }]">
        <div class="sx862vk">
            <h1 class="sixq2vr">Saved invoices</h1>
            <ul class="sm6llz1">
                <li v-for="document in documents.data" :key="document.id" class="s1dhwkrk">
                    <span>{{ document.title }}</span>
                    <span class="s1rwv5zo">
                        <Button as-child variant="outline"><a :href="`/invoices/${document.id}/file`">PDF</a></Button>
                        <Button variant="ghost" type="button" @click="remove(document.id)">Delete</Button>
                    </span>
                </li>
            </ul>
            <p v-if="documents.data.length === 0" class="syp5vk7">Save an invoice from the generator to see it here.</p>
            <Pagination :meta="documents.meta" path="/dashboard/documents" />
        </div>
    </AppLayout>
</template>

<style scoped>
.sx862vk {
    @apply space-y-4 p-4;
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
