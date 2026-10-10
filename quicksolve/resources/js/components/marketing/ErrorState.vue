<script setup lang="ts">
defineProps<{ title?: string; message: string }>();
</script>

<template>
    <div class="se7qo0f" role="alert">
        <p class="s55z0kz">{{ title ?? 'Something went wrong' }}</p>
        <p class="s200p7">{{ message }}</p>
    </div>
</template>

<style scoped>
.se7qo0f {
    @apply rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800;
}

.s55z0kz {
    @apply font-medium;
}

.s200p7 {
    @apply mt-1;
}
</style>
