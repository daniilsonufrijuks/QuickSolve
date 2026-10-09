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
    <div v-if="message" class="fixed bottom-4 right-4 z-50 max-w-sm rounded-lg border bg-white p-4 shadow-sm dark:bg-slate-900" role="status">
        <div class="flex items-start gap-3">
            <p class="text-sm" :class="tone === 'error' ? 'text-red-700' : 'text-slate-800 dark:text-slate-100'">{{ message }}</p>
            <button type="button" class="text-slate-500" aria-label="Dismiss notification" @click="message = ''">
                <X class="size-4" />
            </button>
        </div>
    </div>
</template>
