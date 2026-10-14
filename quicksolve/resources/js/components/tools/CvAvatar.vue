<script setup lang="ts">
withDefaults(
    defineProps<{
        src?: string;
        initials: string;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { src: '', size: 'md' },
);
</script>

<template>
    <img v-if="src" :src="src" alt="" class="pic" :class="size" />
    <span v-else class="pic mark" :class="size">{{ initials }}</span>
</template>

<style scoped>
.pic {
    @apply shrink-0 rounded-full object-cover;
}

.sm {
    @apply h-14 w-14;
}

.md {
    @apply h-20 w-20;
}

.lg {
    @apply h-24 w-24;
}

.mark {
    @apply inline-flex items-center justify-center bg-slate-400 text-sm font-semibold tracking-wide text-white;
}

.mark.md {
    @apply text-lg;
}

.mark.lg {
    @apply text-xl;
}
</style>
