<script setup lang="ts">
defineProps<{
    modelValue: string;
    label?: string;
}>();

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <label class="block">
        <span v-if="label" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">{{ label }}</span>
        <input
            :value="modelValue"
            type="search"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none ring-blue-600 focus:ring-2 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            v-bind="$attrs"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
    </label>
</template>
