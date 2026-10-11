<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { computed, ref } from 'vue';

const title = ref('QuickSolve — Small tools. Smarter business.');
const url = ref('https://example.com/tools/profit-margin-calculator');
const description = ref('Work out profit, margin, markup, and a break-even price from your costs and fees.');

const titleCount = computed(() => title.value.length);
const descriptionCount = computed(() => description.value.length);
const displayUrl = computed(() => url.value.replace(/^https?:\/\//, ''));
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField :label="`Title (${titleCount} characters)`" hint="Many result pages truncate around 60 characters.">
                <input v-model="title" class="field" maxlength="120" />
            </FormField>
            <FormField label="URL">
                <input v-model="url" class="field" />
            </FormField>
            <FormField :label="`Meta description (${descriptionCount} characters)`" hint="Many result pages truncate around 160 characters.">
                <textarea v-model="description" class="field" rows="4" maxlength="320" />
            </FormField>
        </form>
        <section class="panel">
            <p class="note">This is a layout preview, not a ranking guarantee. Google may rewrite titles and descriptions.</p>
            <article class="card">
                <p class="url">{{ displayUrl }}</p>
                <p class="title">{{ title }}</p>
                <p class="copy">{{ description }}</p>
            </article>
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

.note {
    @apply text-sm leading-6;
}

.card {
    @apply max-w-xl space-y-1 rounded-lg bg-white p-4 dark:bg-slate-950;
}

.url {
    @apply text-sm text-emerald-800 dark:text-emerald-300;
}

.title {
    @apply text-xl text-blue-800 dark:text-blue-300;
}

.copy {
    @apply text-sm leading-6 text-slate-700 dark:text-slate-200;
}
</style>
