<script setup lang="ts">
withDefaults(
    defineProps<{
        src?: string;
        initials: string;
        size?: 'sm' | 'md' | 'lg' | 'xl';
        shape?: 'circle' | 'rounded';
    }>(),
    { src: '', size: 'md', shape: 'circle' },
);
</script>

<template>
    <img v-if="src" :src="src" alt="" class="pic" :class="[size, shape]" />
    <span v-else class="pic mark" :class="[size, shape]">{{ initials }}</span>
</template>

<style scoped>
.pic {
    @apply shrink-0 object-cover;
}

.circle {
    @apply rounded-full;
}

.rounded {
    @apply rounded-xl;
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

.xl {
    @apply h-32 w-32;
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

.mark.xl {
    @apply text-2xl;
}
</style>
