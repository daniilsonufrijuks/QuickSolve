<script setup lang="ts">
defineProps<{ access: string }>();
</script>

<template>
    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="access === 'premium' ? 'bg-blue-50 text-blue-800' : 'bg-slate-100 text-slate-700'">
        {{ access === 'premium' ? 'Pro' : 'Free' }}
    </span>
</template>
