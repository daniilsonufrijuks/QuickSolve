<script setup lang="ts">
defineProps<{ title: string; message: string }>();
</script>

<template>
    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center dark:bg-slate-900">
        <h2 class="text-lg font-semibold">{{ title }}</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ message }}</p>
    </div>
</template>
