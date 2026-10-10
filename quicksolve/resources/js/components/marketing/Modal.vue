<script setup lang="ts">
defineProps<{ open: boolean; title: string }>();
defineEmits<{ close: [] }>();
</script>

<template>
    <div v-if="open" class="s1qp6srd" @click.self="$emit('close')">
        <div class="sk6pzno" role="dialog" aria-modal="true" :aria-label="title">
            <h2 class="s1cmvr70">{{ title }}</h2>
            <div class="s200pa">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.s1qp6srd {
    @apply fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4;
}

.sk6pzno {
    @apply w-full max-w-lg rounded-xl bg-white p-6 shadow-lg dark:bg-slate-900;
}

.s1cmvr70 {
    @apply text-lg font-semibold;
}

.s200pa {
    @apply mt-4;
}
</style>
