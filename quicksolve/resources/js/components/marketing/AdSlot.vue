<script setup lang="ts">
defineProps<{ label: string }>();
</script>

<template>
    <section class="hidden" :aria-label="label" />
</template>
