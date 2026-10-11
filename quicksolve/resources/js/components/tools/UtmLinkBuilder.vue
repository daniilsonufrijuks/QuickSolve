<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { computed, ref } from 'vue';

const base = ref('https://example.com/pricing');
const source = ref('newsletter');
const medium = ref('email');
const campaign = ref('april-launch');
const term = ref('');
const content = ref('');
const copied = ref(false);
const error = computed(() => {
    try {
        const parsed = new URL(base.value);

        return parsed.protocol === 'http:' || parsed.protocol === 'https:' ? '' : 'Use an http or https URL.';
    } catch {
        return 'Enter a full URL, including https://';
    }
});

const result = computed(() => {
    if (error.value) {
        return '';
    }

    const parsed = new URL(base.value);
    const pairs: Array<[string, string]> = [
        ['utm_source', source.value],
        ['utm_medium', medium.value],
        ['utm_campaign', campaign.value],
        ['utm_term', term.value],
        ['utm_content', content.value],
    ];

    for (const [key, value] of pairs) {
        if (value.trim() !== '') {
            parsed.searchParams.set(key, value.trim());
        }
    }

    return parsed.toString();
});

async function copyLink() {
    if (!result.value) {
        return;
    }

    await navigator.clipboard.writeText(result.value);
    copied.value = true;
}
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Page URL" :error="error"><input v-model="base" class="field" /></FormField>
            <FormField label="Source"><input v-model="source" class="field" placeholder="google, newsletter" /></FormField>
            <FormField label="Medium"><input v-model="medium" class="field" placeholder="cpc, email" /></FormField>
            <FormField label="Campaign"><input v-model="campaign" class="field" /></FormField>
            <FormField label="Term"><input v-model="term" class="field" /></FormField>
            <FormField label="Content"><input v-model="content" class="field" /></FormField>
        </form>
        <section class="panel">
            <p class="meta">Campaign parameters stay on the link you copy. QuickSolve does not register the click.</p>
            <p class="link">{{ result || 'Fix the URL to build a campaign link.' }}</p>
            <Button type="button" :disabled="!result" @click="copyLink">{{ copied ? 'Copied' : 'Copy link' }}</Button>
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

.meta {
    @apply text-sm leading-6;
}

.link {
    @apply break-all rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-950;
}
</style>
