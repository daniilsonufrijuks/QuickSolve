<script setup lang="ts">
import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const page = usePage<SharedData>();
const message = ref('');
const tone = ref<'success' | 'error'>('success');

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    () => {
        if (page.props.flash?.success) {
            message.value = String(page.props.flash.success);
            tone.value = 'success';
        } else if (page.props.flash?.error) {
            message.value = String(page.props.flash.error);
            tone.value = 'error';
        }
    },
    { immediate: true },
);
</script>

<template>
    <div v-if="message" class="svr24hv" role="status">
        <div class="s1sthhai">
            <p class="s1bkxu62" :class="tone === 'error' ? 'text-red-700' : 'text-slate-800 dark:text-slate-100'">{{ message }}</p>
            <button type="button" class="s1frj40x" aria-label="Dismiss notification" @click="message = ''">
                <X class="s1k44y20" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.svr24hv {
    @apply fixed bottom-4 right-4 z-50 max-w-sm rounded-lg border bg-white p-4 shadow-sm dark:bg-slate-900;
}

.s1sthhai {
    @apply flex items-start gap-3;
}

.s1bkxu62 {
    @apply text-sm;
}

.s1frj40x {
    @apply text-slate-500;
}

.s1k44y20 {
    @apply size-4;
}
</style>
