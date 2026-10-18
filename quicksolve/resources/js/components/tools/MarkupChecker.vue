<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { computed, ref } from 'vue';

const props = defineProps<{
    kind: 'html' | 'css';
}>();

type Severity = 'error' | 'warning' | 'info';

type MarkupMessage = {
    severity: Severity;
    line: number | null;
    column: number | null;
    message: string;
    extract: string | null;
};

type MarkupResult = {
    valid: boolean;
    checked_by: string;
    messages: MarkupMessage[];
};

const samples = {
    html: `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Example</title>
</head>
<body>
<h1>Hello</h1>
<p>A short page.</p>
</body>
</html>`,
    css: `body {
  color: #111827;
  margin: 0;
}`,
};

const source = ref(samples[props.kind]);
const mode = ref<'document' | 'fragment'>('document');
const profile = ref('css3svg');
const result = ref<MarkupResult | null>(null);
const error = ref('');
const pending = ref(false);

const endpoint = computed(() => (props.kind === 'html' ? '/tools/html-validator/check' : '/tools/css-validator/check'));
const official = computed(() =>
    props.kind === 'html'
        ? { href: 'https://validator.w3.org/nu/#textarea', label: 'W3C Nu Html Checker' }
        : { href: 'https://jigsaw.w3.org/css-validator/validator', label: 'W3C CSS Validator' },
);

const errorCount = computed(() => result.value?.messages.filter((message) => message.severity === 'error').length ?? 0);
const warningCount = computed(() => result.value?.messages.filter((message) => message.severity === 'warning').length ?? 0);

async function check() {
    pending.value = true;
    error.value = '';
    result.value = null;

    const body = props.kind === 'html'
        ? { source: source.value, mode: mode.value }
        : { source: source.value, profile: profile.value };

    const response = await postJson<MarkupResult>(endpoint.value, body);

    pending.value = false;

    if (!response.ok || !response.data) {
        error.value = response.message;
        return;
    }

    result.value = response.data;
}

function location(message: MarkupMessage): string {
    if (message.line === null) {
        return 'Document';
    }

    if (message.column === null) {
        return `Line ${message.line}`;
    }

    return `Line ${message.line}, column ${message.column}`;
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent="check">
            <FormField v-if="kind === 'html'" label="What you are pasting" hint="A fragment is wrapped in a minimal document. Line numbers still match your paste.">
                <select v-model="mode" class="field">
                    <option value="document">Full HTML document</option>
                    <option value="fragment">HTML fragment</option>
                </select>
            </FormField>
            <FormField v-else label="Profile" hint="CSS Level 3 + SVG is the W3C default.">
                <select v-model="profile" class="field">
                    <option value="css3svg">CSS Level 3 + SVG</option>
                    <option value="css3">CSS Level 3</option>
                    <option value="css21">CSS 2.1</option>
                    <option value="svg">SVG</option>
                </select>
            </FormField>
            <FormField :label="kind === 'html' ? 'HTML' : 'CSS'">
                <textarea v-model="source" class="field code" rows="16" spellcheck="false" required />
            </FormField>
            <Button type="submit" :disabled="pending || source.trim() === ''">
                {{ pending ? 'Checking with W3C…' : 'Check' }}
            </Button>
            <p class="note">
                This sends your source to the
                <a :href="official.href" class="official" target="_blank" rel="noreferrer">{{ official.label }}</a>
                and does not store it.
            </p>
        </form>

        <section class="panel" aria-live="polite">
            <p v-if="error" class="banner error">{{ error }}</p>
            <p v-else-if="!result" class="meta">Paste source and check it. Messages show the line the W3C checker reported.</p>
            <template v-else>
                <p class="banner" :class="result.valid ? (warningCount ? 'warning' : 'ok') : 'error'">
                    <template v-if="result.valid && warningCount === 0">No errors or warnings.</template>
                    <template v-else-if="result.valid">No errors. {{ warningCount }} warning{{ warningCount === 1 ? '' : 's' }}.</template>
                    <template v-else>{{ errorCount }} error{{ errorCount === 1 ? '' : 's' }}<span v-if="warningCount">, {{ warningCount }} warning{{ warningCount === 1 ? '' : 's' }}</span>.</template>
                </p>
                <p class="meta">Results from the {{ result.checked_by }}.</p>
                <ul v-if="result.messages.length" class="messages">
                    <li v-for="(message, index) in result.messages" :key="`${message.line}-${index}`" class="message">
                        <p class="where">
                            <span class="badge" :class="message.severity">{{ message.severity }}</span>
                            {{ location(message) }}
                        </p>
                        <p>{{ message.message }}</p>
                        <pre v-if="message.extract" class="extract">{{ message.extract }}</pre>
                    </li>
                </ul>
            </template>
        </section>
    </div>
</template>

<style scoped>
.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.panel {
    @apply space-y-3 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.field {
    @apply w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100;
}

.code {
    @apply font-mono text-xs leading-5;
}

.note,
.meta {
    @apply text-sm leading-6 text-slate-600 dark:text-slate-300;
}

.official {
    @apply text-blue-700 underline dark:text-blue-300;
}

.banner {
    @apply rounded-lg px-3 py-2 text-sm font-medium;
}

.banner.ok {
    @apply bg-emerald-50 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-100;
}

.banner.warning {
    @apply bg-amber-50 text-amber-950 dark:bg-amber-950 dark:text-amber-100;
}

.banner.error {
    @apply bg-red-50 text-red-800 dark:bg-red-950 dark:text-red-100;
}

.messages {
    @apply space-y-4;
}

.message {
    @apply space-y-1 border-b border-slate-200 pb-4 text-sm leading-6 last:border-b-0 dark:border-slate-800;
}

.where {
    @apply flex flex-wrap items-center gap-2 text-xs font-medium uppercase tracking-wide text-slate-500;
}

.badge {
    @apply rounded-full px-2 py-0.5;
}

.badge.error {
    @apply bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200;
}

.badge.warning {
    @apply bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-100;
}

.badge.info {
    @apply bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200;
}

.extract {
    @apply overflow-auto rounded-lg bg-slate-50 p-3 font-mono text-xs leading-5 text-slate-800 dark:bg-slate-950 dark:text-slate-200;
}
</style>
