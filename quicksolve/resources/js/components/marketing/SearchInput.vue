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
    <label class="s1jv5hp">
        <span v-if="label" class="sj1azyq">{{ label }}</span>
        <input
            :value="modelValue"
            type="search"
            class="s15fkjq9"
            v-bind="$attrs"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
    </label>
</template>

<style scoped>
.s1jv5hp {
    @apply block;
}

.sj1azyq {
    @apply mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200;
}

.s15fkjq9 {
    @apply w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none ring-blue-600 focus:ring-2 dark:border-slate-700 dark:bg-slate-900 dark:text-white;
}
</style>
