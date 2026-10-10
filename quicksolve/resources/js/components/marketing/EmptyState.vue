<script setup lang="ts">
defineProps<{ title: string; message: string }>();
</script>

<template>
    <div class="s1aggm2m">
        <h2 class="s1cmvr70">{{ title }}</h2>
        <p class="sgwv9i7">{{ message }}</p>
    </div>
</template>

<style scoped>
.s1aggm2m {
    @apply rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center dark:bg-slate-900;
}

.s1cmvr70 {
    @apply text-lg font-semibold;
}

.sgwv9i7 {
    @apply mt-2 text-sm text-slate-600 dark:text-slate-300;
}
</style>
