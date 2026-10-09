<script setup lang="ts">
import ErrorState from '@/components/marketing/ErrorState.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { reactive, ref } from 'vue';

defineProps<{
    usage: { used: number; limit: number; remaining: number; window: string; plan: string } | null;
    demoMode: boolean;
}>();

const form = reactive({
    product_name: '',
    product_category: '',
    product_features: '',
    target_audience: '',
    tone: 'professional',
    length: 'short',
    format: 'listing',
    include_call_to_action: true,
});

const pending = ref(false);
const error = ref('');
const result = ref<null | {
    mode: string;
    notice: string | null;
    title: string;
    short_description: string;
    full_description: string;
    bullets: string[];
    call_to_action: string | null;
}>(null);

async function submit() {
    pending.value = true;
    error.value = '';
    result.value = null;

    const response = await postJson<typeof result.value>('/api/v1/tools/product-description-generator/generate', form);
    pending.value = false;

    if (!response.ok || !response.data) {
        error.value = response.message;
        return;
    }

    result.value = response.data;
}
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-2">
        <form class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="submit">
            <p v-if="demoMode" class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">Demo mode is on because no AI provider key is configured. Results are assembled from your inputs and labeled as a demo.</p>
            <p v-if="usage" class="text-sm text-slate-600">{{ usage.remaining }} of {{ usage.limit }} generations left this {{ usage.window }}.</p>
            <FormField label="Product name"><input v-model="form.product_name" required class="w-full rounded-lg border px-3 py-2" /></FormField>
            <FormField label="Product category"><input v-model="form.product_category" required class="w-full rounded-lg border px-3 py-2" /></FormField>
            <FormField label="Features" hint="One feature per line.">
                <textarea v-model="form.product_features" required rows="5" class="w-full rounded-lg border px-3 py-2" />
            </FormField>
            <FormField label="Target audience"><input v-model="form.target_audience" required class="w-full rounded-lg border px-3 py-2" /></FormField>
            <div class="grid gap-4 sm:grid-cols-3">
                <FormField label="Tone">
                    <select v-model="form.tone" class="w-full rounded-lg border px-3 py-2">
                        <option value="professional">Professional</option>
                        <option value="friendly">Friendly</option>
                        <option value="playful">Playful</option>
                        <option value="luxury">Luxury</option>
                        <option value="direct">Direct</option>
                    </select>
                </FormField>
                <FormField label="Length">
                    <select v-model="form.length" class="w-full rounded-lg border px-3 py-2">
                        <option value="short">Short</option>
                        <option value="medium">Medium</option>
                        <option value="long">Long</option>
                    </select>
                </FormField>
                <FormField label="Format">
                    <select v-model="form.format" class="w-full rounded-lg border px-3 py-2">
                        <option value="listing">Listing</option>
                        <option value="plain">Plain paragraph</option>
                        <option value="bullets">Bullets</option>
                    </select>
                </FormField>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.include_call_to_action" type="checkbox" />
                Include a call to action
            </label>
            <Button type="submit" :disabled="pending">{{ pending ? 'Working…' : 'Generate description' }}</Button>
        </form>
        <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" aria-live="polite">
            <h2 class="text-lg font-semibold">Draft</h2>
            <ErrorState v-if="error" class="mt-4" :message="error" />
            <p v-else-if="!result" class="mt-4 text-sm text-slate-600">Your draft will appear here. A failed provider request stays an error. Demo text is never presented as a live model response.</p>
            <div v-else class="mt-4 space-y-4 text-sm leading-6">
                <p v-if="result.mode === 'demo'" class="rounded-lg bg-amber-50 px-3 py-2 text-amber-900">{{ result.notice }}</p>
                <p v-else class="rounded-lg bg-emerald-50 px-3 py-2 text-emerald-900">Live provider response.</p>
                <h3 class="text-xl font-semibold">{{ result.title }}</h3>
                <p>{{ result.short_description }}</p>
                <p class="whitespace-pre-wrap">{{ result.full_description }}</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li v-for="bullet in result.bullets" :key="bullet">{{ bullet }}</li>
                </ul>
                <p v-if="result.call_to_action"><strong>Call to action:</strong> {{ result.call_to_action }}</p>
            </div>
        </section>
    </div>
</template>
