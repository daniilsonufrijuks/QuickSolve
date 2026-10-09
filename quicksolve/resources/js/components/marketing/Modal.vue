<script setup lang="ts">
defineProps<{ open: boolean; title: string }>();
defineEmits<{ close: [] }>();
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" @click.self="$emit('close')">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-lg dark:bg-slate-900" role="dialog" aria-modal="true" :aria-label="title">
            <h2 class="text-lg font-semibold">{{ title }}</h2>
            <div class="mt-4">
                <slot />
            </div>
        </div>
    </div>
</template>
