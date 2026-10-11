<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { computed, ref } from 'vue';

const pattern = ref('\\b[A-Z][a-z]+\\b');
const flags = ref('g');
const sample = ref('Ada met Bob in Lisbon.');

const result = computed(() => {
    try {
        const expression = new RegExp(pattern.value, flags.value);
        const matches = [...sample.value.matchAll(expression)].map((match) => ({
            text: match[0],
            index: match.index ?? 0,
        }));

        return { matches, error: '' };
    } catch (caught) {
        return { matches: [], error: caught instanceof Error ? caught.message : 'The pattern is not valid.' };
    }
});

const highlighted = computed(() => {
    if (result.value.error) {
        return sample.value;
    }

    const pieces: string[] = [];
    let cursor = 0;

    for (const match of result.value.matches) {
        pieces.push(escapeHtml(sample.value.slice(cursor, match.index)));
        pieces.push(`<mark>${escapeHtml(match.text)}</mark>`);
        cursor = match.index + match.text.length;
    }

    pieces.push(escapeHtml(sample.value.slice(cursor)));

    return pieces.join('');
});

function escapeHtml(value: string): string {
    return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Pattern"><input v-model="pattern" class="field" /></FormField>
            <FormField label="Flags"><input v-model="flags" class="field" maxlength="8" /></FormField>
            <FormField label="Sample text"><textarea v-model="sample" class="field" rows="8" /></FormField>
        </form>
        <section class="panel">
            <p v-if="result.error" class="error">{{ result.error }}</p>
            <p v-else class="meta">{{ result.matches.length }} match{{ result.matches.length === 1 ? '' : 'es' }}</p>
            <p class="sample" v-html="highlighted" />
        </section>
    </div>
</template>

<style scoped>
.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.panel {
    @apply space-y-3 rounded-xl border p-5;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.meta,
.error {
    @apply text-sm leading-6;
}

.error {
    @apply text-red-700 dark:text-red-300;
}

.sample {
    @apply whitespace-pre-wrap break-words rounded-lg bg-slate-50 p-3 text-sm leading-7 dark:bg-slate-950;
}

.sample :deep(mark) {
    @apply rounded bg-amber-200 px-0.5 text-slate-900;
}
</style>
