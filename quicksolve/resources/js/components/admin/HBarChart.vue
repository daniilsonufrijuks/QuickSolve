<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    items: Array<{ label: string; value: number }>;
    color?: string;
}>();

const max = computed(() => Math.max(1, ...props.items.map((item) => item.value)));
</script>

<template>
    <ul v-if="items.length" class="list">
        <li v-for="item in items" :key="item.label" class="row">
            <div class="label">{{ item.label }}</div>
            <div class="track">
                <div class="fill" :style="{ width: `${(item.value / max) * 100}%`, background: color ?? '#2563eb' }" />
            </div>
            <div class="value">{{ item.value }}</div>
        </li>
    </ul>
    <p v-else class="empty">No data yet.</p>
</template>

<style scoped>
.list {
    @apply space-y-2;
}

.row {
    @apply grid grid-cols-[8rem_1fr_3rem] items-center gap-2 text-sm;
}

.label {
    @apply truncate text-slate-700 dark:text-slate-200;
}

.track {
    @apply h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800;
}

.fill {
    @apply h-full rounded-full;
}

.value {
    @apply text-right text-slate-600 dark:text-slate-300;
}

.empty {
    @apply text-sm text-slate-500;
}
</style>
