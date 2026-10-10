<script setup lang="ts">
defineProps<{
    label: string;
    error?: string;
    hint?: string;
}>();
</script>

<template>
    <label class="seofrs7">
        <span class="s152qa0c">{{ label }}</span>
        <slot />
        <span v-if="hint" class="szx0bse">{{ hint }}</span>
        <span v-if="error" class="sdmk961">{{ error }}</span>
    </label>
</template>

<style scoped>
.seofrs7 {
    @apply block text-sm;
}

.s152qa0c {
    @apply mb-1 block font-medium text-slate-800 dark:text-slate-100;
}

.szx0bse {
    @apply mt-1 block text-xs text-slate-500;
}

.sdmk961 {
    @apply mt-1 block text-sm text-red-700;
}
</style>
