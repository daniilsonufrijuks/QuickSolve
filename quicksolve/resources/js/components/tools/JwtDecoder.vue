<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { computed, ref } from 'vue';

const token = ref('');

function decodePart(part: string): unknown {
    const padded = part.replace(/-/g, '+').replace(/_/g, '/') + '='.repeat((4 - (part.length % 4)) % 4);
    const json = new TextDecoder().decode(Uint8Array.from(atob(padded), (character) => character.charCodeAt(0)));

    return JSON.parse(json);
}

const decoded = computed(() => {
    const trimmed = token.value.trim();

    if (trimmed === '') {
        return null;
    }

    const parts = trimmed.split('.');

    if (parts.length < 2 || parts.length > 3) {
        return { error: 'A JWT has two or three dot-separated parts.' };
    }

    try {
        return {
            header: decodePart(parts[0]),
            payload: decodePart(parts[1]),
            signed: parts.length === 3,
        };
    } catch {
        return { error: 'The token could not be decoded. Nothing was sent anywhere.' };
    }
});
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="JWT" hint="Decoding happens in this browser. Do not paste a signing secret. Treat live tokens as sensitive.">
                <textarea v-model="token" class="field" rows="8" placeholder="eyJ..." />
            </FormField>
        </form>
        <section class="panel">
            <p v-if="!decoded" class="meta">Paste a token to inspect the header and payload.</p>
            <p v-else-if="'error' in decoded" class="error">{{ decoded.error }}</p>
            <template v-else>
                <p class="meta">{{ decoded.signed ? 'A signature part is present. It is not verified here.' : 'This token has no signature part.' }}</p>
                <pre class="block">{{ JSON.stringify(decoded.header, null, 2) }}</pre>
                <pre class="block">{{ JSON.stringify(decoded.payload, null, 2) }}</pre>
            </template>
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

.field,
.block {
    @apply w-full overflow-auto rounded-lg border px-3 py-2 text-sm;
}

.meta,
.error {
    @apply text-sm leading-6;
}

.error {
    @apply text-red-700 dark:text-red-300;
}
</style>
