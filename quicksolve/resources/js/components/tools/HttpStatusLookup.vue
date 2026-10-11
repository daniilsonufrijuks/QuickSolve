<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { findHttpStatus } from '@/lib/httpStatus';
import { computed, ref } from 'vue';

const query = ref('');
const results = computed(() => findHttpStatus(query.value));
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Search" hint="Type a code, phrase, or a word such as redirect or timeout.">
                <input v-model="query" class="field" type="search" placeholder="404, unauthorized, gateway" />
            </FormField>
            <p class="meta">{{ results.length }} code{{ results.length === 1 ? '' : 's' }}</p>
        </form>
        <section class="panel">
            <article v-for="entry in results" :key="entry.code" class="card">
                <h3 class="title">{{ entry.code }} {{ entry.phrase }}</h3>
                <p>{{ entry.meaning }}</p>
                <p class="meta">Typical cause: {{ entry.cause }}</p>
                <p class="meta">What to check: {{ entry.next }}</p>
            </article>
            <p v-if="results.length === 0" class="meta">No codes match that search.</p>
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

.card {
    @apply space-y-2 border-b pb-4 last:border-b-0;
}

.title {
    @apply text-lg font-semibold;
}

.meta {
    @apply text-sm text-slate-600;
}
</style>
