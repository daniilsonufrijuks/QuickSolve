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
    <div class="s9jd40r">
        <form class="s1r0jak5" @submit.prevent="submit">
            <p v-if="demoMode" class="s1mx57ig">Demo mode is on because no DeepSeek API key is configured. Results are assembled from your inputs and labeled as a demo.</p>
            <p v-else class="s1ehttdy">Requests are sent to DeepSeek on the server. A failed request stays an error and is not replaced with demo text.</p>
            <p v-if="usage" class="syp5waw">{{ usage.remaining }} of {{ usage.limit }} generations left this {{ usage.window }}.</p>
            <FormField label="Product name"><input v-model="form.product_name" required class="s1wl1yqe" /></FormField>
            <FormField label="Product category"><input v-model="form.product_category" required class="s1wl1yqe" /></FormField>
            <FormField label="Features" hint="One feature per line.">
                <textarea v-model="form.product_features" required rows="5" class="s1wl1yqe" />
            </FormField>
            <FormField label="Target audience"><input v-model="form.target_audience" required class="s1wl1yqe" /></FormField>
            <div class="s9tb7el">
                <FormField label="Tone">
                    <select v-model="form.tone" class="s1wl1yqe">
                        <option value="professional">Professional</option>
                        <option value="friendly">Friendly</option>
                        <option value="playful">Playful</option>
                        <option value="luxury">Luxury</option>
                        <option value="direct">Direct</option>
                    </select>
                </FormField>
                <FormField label="Length">
                    <select v-model="form.length" class="s1wl1yqe">
                        <option value="short">Short</option>
                        <option value="medium">Medium</option>
                        <option value="long">Long</option>
                    </select>
                </FormField>
                <FormField label="Format">
                    <select v-model="form.format" class="s1wl1yqe">
                        <option value="listing">Listing</option>
                        <option value="plain">Plain paragraph</option>
                        <option value="bullets">Bullets</option>
                    </select>
                </FormField>
            </div>
            <label class="s1mng9fi">
                <input v-model="form.include_call_to_action" type="checkbox" />
                Include a call to action
            </label>
            <Button type="submit" :disabled="pending">{{ pending ? 'Working…' : 'Generate description' }}</Button>
        </form>
        <section class="s2utn7g" aria-live="polite">
            <h2 class="s1cmvr70">Draft</h2>
            <ErrorState v-if="error" class="s200pa" :message="error" />
            <p v-else-if="!result" class="sujbau2">Your draft will appear here. A failed provider request stays an error. Demo text is never presented as a live model response.</p>
            <div v-else class="s1du7jg0">
                <p v-if="result.mode === 'demo'" class="s18v4p8n">{{ result.notice }}</p>
                <p v-else class="s1dqhb61">Live provider response.</p>
                <h3 class="sy1t0l">{{ result.title }}</h3>
                <p>{{ result.short_description }}</p>
                <p class="s19o3qtg">{{ result.full_description }}</p>
                <ul class="s1gvggfe">
                    <li v-for="bullet in result.bullets" :key="bullet">{{ bullet }}</li>
                </ul>
                <p v-if="result.call_to_action"><strong>Call to action:</strong> {{ result.call_to_action }}</p>
            </div>
        </section>
    </div>
</template>

<style scoped>
.s9jd40r {
    @apply grid gap-6 lg:grid-cols-2;
}

.s1r0jak5 {
    @apply space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.s1mx57ig {
    @apply rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-950;
}

.s1ehttdy {
    @apply rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-950;
}

.syp5waw {
    @apply text-sm text-slate-600;
}

.s1wl1yqe {
    @apply w-full rounded-lg border px-3 py-2;
}

.s9tb7el {
    @apply grid gap-4 sm:grid-cols-3;
}

.s1mng9fi {
    @apply flex items-center gap-2 text-sm;
}

.s2utn7g {
    @apply rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.s1cmvr70 {
    @apply text-lg font-semibold;
}

.s200pa {
    @apply mt-4;
}

.sujbau2 {
    @apply mt-4 text-sm text-slate-600;
}

.s1du7jg0 {
    @apply mt-4 space-y-4 text-sm leading-6;
}

.s18v4p8n {
    @apply rounded-lg bg-amber-50 px-3 py-2 text-amber-900;
}

.s1dqhb61 {
    @apply rounded-lg bg-emerald-50 px-3 py-2 text-emerald-900;
}

.sy1t0l {
    @apply text-xl font-semibold;
}

.s19o3qtg {
    @apply whitespace-pre-wrap;
}

.s1gvggfe {
    @apply list-disc space-y-1 pl-5;
}
</style>
