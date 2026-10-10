<script setup lang="ts">
defineProps<{
    modelValue: string;
    categories: Array<{ slug: string; name: string }>;
    label?: string;
}>();

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <label class="seofrs7">
        <span class="skhmobw">{{ label ?? 'Category' }}</span>
        <select
            :value="modelValue"
            class="s1akvu0w"
            @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option value="">All categories</option>
            <option v-for="category in categories" :key="category.slug" :value="category.slug">{{ category.name }}</option>
        </select>
    </label>
</template>

<style scoped>
.seofrs7 {
    @apply block text-sm;
}

.skhmobw {
    @apply mb-1 block font-medium text-slate-700 dark:text-slate-200;
}

.s1akvu0w {
    @apply w-full rounded-lg border border-slate-300 bg-white px-3 py-2 dark:border-slate-700 dark:bg-slate-900;
}
</style>
