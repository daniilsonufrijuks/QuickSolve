<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    series: Array<Record<string, string | number>>;
    keys: Array<{ name: string; color: string }>;
    labelKey?: string;
}>();

const labelKey = computed(() => props.labelKey ?? 'label');

const max = computed(() =>
    Math.max(1, ...props.series.flatMap((point) => props.keys.map((key) => Number(point[key.name] ?? 0)))),
);
</script>

<template>
    <div>
    <div class="chart" role="img" aria-label="Bar chart">
        <div v-for="(point, index) in series" :key="index" class="group">
            <div class="bars">
                <div
                    v-for="key in keys"
                    :key="key.name"
                    class="bar"
                    :style="{ height: `${(Number(point[key.name] ?? 0) / max) * 100}%`, background: key.color }"
                    :title="`${key.name}: ${point[key.name] ?? 0}`"
                />
            </div>
            <span class="tick">{{ point[labelKey] }}</span>
        </div>
    </div>
    <ul class="legend">
        <li v-for="key in keys" :key="key.name" class="item">
            <span class="swatch" :style="{ background: key.color }" />
            {{ key.name }}
        </li>
    </ul>
    </div>
</template>

<style scoped>
.chart {
    @apply flex h-44 items-end gap-1 overflow-x-auto;
}

.group {
    @apply flex min-w-[1.75rem] flex-1 flex-col items-center gap-1;
}

.bars {
    @apply flex h-36 items-end gap-0.5;
}

.bar {
    @apply w-2 rounded-t;
}

.tick {
    @apply text-[10px] text-slate-500;
}

.legend {
    @apply mt-3 flex flex-wrap gap-3 text-xs text-slate-600 dark:text-slate-300;
}

.item {
    @apply flex items-center gap-1;
}

.swatch {
    @apply h-2 w-2 rounded-full;
}
</style>
