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
        <div class="sx862vk">
            <h1 class="sixq2vr">Downloads</h1>
            <p class="syp5waw">Files are streamed through an authorized request. A purchase that is still pending cannot be downloaded.</p>
            <ul class="s1j8i8bc">
                <li v-for="purchase in purchases" :key="purchase.id" class="s1yz91c2">
                    <span>{{ purchase.template?.name }}</span>
                    <Button v-if="purchase.can_download" as-child><a :href="`/downloads/${purchase.id}`">Download</a></Button>
                    <span v-else class="syp5vk7">Unavailable</span>
                </li>
            </ul>
            <p v-if="purchases.length === 0" class="syp5vk7">Paid downloads will be listed here.</p>
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

.syp5waw {
    @apply text-sm text-slate-600;
}

.s1j8i8bc {
    @apply space-y-3;
}

.s1yz91c2 {
    @apply flex items-center justify-between rounded-xl border p-3;
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}
</style>
