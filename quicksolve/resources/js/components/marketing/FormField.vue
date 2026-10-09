<script setup lang="ts">
defineProps<{
    label: string;
    error?: string;
    hint?: string;
}>();
</script>

<template>
    <label class="block text-sm">
        <span class="mb-1 block font-medium text-slate-800 dark:text-slate-100">{{ label }}</span>
        <slot />
        <span v-if="hint" class="mt-1 block text-xs text-slate-500">{{ hint }}</span>
        <span v-if="error" class="mt-1 block text-sm text-red-700">{{ error }}</span>
    </label>
</template>
