<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    purchases: Array<{ id: number; template?: { name?: string }; can_download: boolean }>;
}>();
</script>

<template>
    <Head title="Downloads" />
    <AppLayout :breadcrumbs="[{ title: 'Downloads', href: '/dashboard/downloads' }]">
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Downloads</h1>
            <p class="text-sm text-slate-600">Files are streamed through an authorized request. A purchase that is still pending cannot be downloaded.</p>
            <ul class="space-y-3">
                <li v-for="purchase in purchases" :key="purchase.id" class="flex items-center justify-between rounded-xl border p-3">
                    <span>{{ purchase.template?.name }}</span>
                    <Button v-if="purchase.can_download" as-child><a :href="`/downloads/${purchase.id}`">Download</a></Button>
                    <span v-else class="text-sm text-slate-500">Unavailable</span>
                </li>
            </ul>
            <p v-if="purchases.length === 0" class="text-sm text-slate-500">Paid downloads will be listed here.</p>
        </div>
    </AppLayout>
</template>
