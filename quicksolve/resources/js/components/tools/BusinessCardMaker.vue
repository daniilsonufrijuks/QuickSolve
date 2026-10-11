<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { nextTick, onMounted, reactive, ref, watch } from 'vue';

const form = reactive({
    name: 'Ada North',
    title: 'Studio lead',
    company: 'North Studio',
    email: 'ada@example.com',
    phone: '+372 5555 0100',
    website: 'north.example',
    tagline: 'Small tools. Smarter business.',
    layout: 'left' as 'left' | 'centered',
    background: '#0f172a',
    foreground: '#f8fafc',
    accent: '#38bdf8',
});

const canvas = ref<HTMLCanvasElement | null>(null);

function draw() {
    const node = canvas.value;

    if (!node) {
        return;
    }

    const width = 1050;
    const height = 600;
    node.width = width;
    node.height = height;
    const context = node.getContext('2d');

    if (!context) {
        return;
    }

    context.fillStyle = form.background;
    context.fillRect(0, 0, width, height);
    context.fillStyle = form.accent;
    context.fillRect(form.layout === 'left' ? 0 : 0, 0, form.layout === 'left' ? 18 : width, form.layout === 'left' ? height : 12);

    const align = form.layout === 'centered' ? 'center' : 'left';
    const x = form.layout === 'centered' ? width / 2 : 72;
    context.textAlign = align;
    context.fillStyle = form.foreground;
    context.font = '600 54px Georgia, serif';
    context.fillText(form.name, x, 180, 900);
    context.fillStyle = form.accent;
    context.font = '500 28px system-ui, sans-serif';
    context.fillText(form.title, x, 230, 900);
    context.fillStyle = form.foreground;
    context.font = '500 26px system-ui, sans-serif';
    context.fillText(form.company, x, 280, 900);
    context.font = '400 22px system-ui, sans-serif';
    context.fillText([form.email, form.phone].filter(Boolean).join('  ·  '), x, 400, 900);
    context.fillText(form.website, x, 440, 900);
    context.fillStyle = form.accent;
    context.font = 'italic 20px Georgia, serif';
    context.fillText(form.tagline, x, 520, 900);
}

function download() {
    const node = canvas.value;

    if (!node) {
        return;
    }

    const link = document.createElement('a');
    link.href = node.toDataURL('image/png');
    link.download = 'business-card.png';
    link.click();
}

watch(form, () => nextTick(draw), { deep: true });
onMounted(draw);
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent>
            <FormField label="Name"><input v-model="form.name" class="field" /></FormField>
            <FormField label="Title"><input v-model="form.title" class="field" /></FormField>
            <FormField label="Company"><input v-model="form.company" class="field" /></FormField>
            <FormField label="Email"><input v-model="form.email" class="field" type="email" /></FormField>
            <FormField label="Phone"><input v-model="form.phone" class="field" /></FormField>
            <FormField label="Website"><input v-model="form.website" class="field" /></FormField>
            <FormField label="Tagline"><input v-model="form.tagline" class="field" /></FormField>
            <FormField label="Layout">
                <select v-model="form.layout" class="field">
                    <option value="left">Left aligned</option>
                    <option value="centered">Centered</option>
                </select>
            </FormField>
            <div class="colors">
                <FormField label="Background"><input v-model="form.background" class="swatch" type="color" /></FormField>
                <FormField label="Text"><input v-model="form.foreground" class="swatch" type="color" /></FormField>
                <FormField label="Accent"><input v-model="form.accent" class="swatch" type="color" /></FormField>
            </div>
            <Button type="button" @click="download">Download PNG</Button>
        </form>
        <section class="panel">
            <p class="meta">Preview at 3.5 × 2 in (1050 × 600 px). Printing and paper stock are not included.</p>
            <canvas ref="canvas" class="card" aria-label="Business card preview" />
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

.colors {
    @apply grid grid-cols-3 gap-3;
}

.swatch {
    @apply h-10 w-full rounded-lg border;
}

.meta {
    @apply text-sm text-slate-600;
}

.card {
    @apply mt-3 w-full rounded-lg border;
}
</style>
