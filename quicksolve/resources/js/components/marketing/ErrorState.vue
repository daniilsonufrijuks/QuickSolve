<script setup lang="ts">
defineProps<{ title?: string; message: string }>();
</script>

<template>
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <p class="font-medium">{{ title ?? 'Something went wrong' }}</p>
        <p class="mt-1">{{ message }}</p>
    </div>
</template>
