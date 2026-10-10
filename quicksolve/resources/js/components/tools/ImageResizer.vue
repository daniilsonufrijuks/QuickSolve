<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { ref, watch } from 'vue';

const error = ref('');
const fileName = ref('');
const source = ref<HTMLImageElement | null>(null);
const preview = ref('');
const width = ref('800');
const height = ref('600');
const lockRatio = ref(true);
const format = ref<'image/jpeg' | 'image/png' | 'image/webp'>('image/jpeg');
const quality = ref('0.85');
const originalSize = ref('');

function onFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) {
        error.value = 'Choose a JPEG, PNG, WebP, or GIF.';
        return;
    }

    if (file.size > 6 * 1024 * 1024) {
        error.value = 'Keep the image under 6 MB.';
        return;
    }

    error.value = '';
    fileName.value = file.name;
    originalSize.value = `${Math.round(file.size / 1024)} KB`;
    const url = URL.createObjectURL(file);
    const image = new Image();
    image.onload = () => {
        source.value = image;
        width.value = String(image.naturalWidth);
        height.value = String(image.naturalHeight);
        URL.revokeObjectURL(url);
        render();
    };
    image.onerror = () => {
        error.value = 'That image could not be read.';
        URL.revokeObjectURL(url);
    };
    image.src = url;
}

function syncHeight() {
    if (!lockRatio.value || !source.value) {
        return;
    }

    const nextWidth = Number(width.value);

    if (!Number.isFinite(nextWidth) || nextWidth < 1) {
        return;
    }

    height.value = String(Math.max(1, Math.round((nextWidth * source.value.naturalHeight) / source.value.naturalWidth)));
}

function syncWidth() {
    if (!lockRatio.value || !source.value) {
        return;
    }

    const nextHeight = Number(height.value);

    if (!Number.isFinite(nextHeight) || nextHeight < 1) {
        return;
    }

    width.value = String(Math.max(1, Math.round((nextHeight * source.value.naturalWidth) / source.value.naturalHeight)));
}

function render() {
    const image = source.value;
    const nextWidth = Math.round(Number(width.value));
    const nextHeight = Math.round(Number(height.value));
    const nextQuality = Number(quality.value);

    if (!image) {
        preview.value = '';
        return;
    }

    if (!Number.isFinite(nextWidth) || !Number.isFinite(nextHeight) || nextWidth < 1 || nextHeight < 1 || nextWidth > 4000 || nextHeight > 4000) {
        error.value = 'Width and height must be whole numbers from 1 to 4000.';
        return;
    }

    error.value = '';
    const canvas = document.createElement('canvas');
    canvas.width = nextWidth;
    canvas.height = nextHeight;
    const context = canvas.getContext('2d');

    if (!context) {
        error.value = 'Canvas is unavailable in this browser.';
        return;
    }

    context.drawImage(image, 0, 0, nextWidth, nextHeight);
    preview.value = canvas.toDataURL(format.value, format.value === 'image/png' ? undefined : nextQuality);
}

function download() {
    if (!preview.value) {
        return;
    }

    const extension = format.value === 'image/png' ? 'png' : format.value === 'image/webp' ? 'webp' : 'jpg';
    const link = document.createElement('a');
    link.href = preview.value;
    link.download = fileName.value.replace(/\.[^.]+$/, '') + `-resized.${extension}`;
    link.click();
}

watch([width, format, quality], () => {
    if (lockRatio.value) {
        syncHeight();
    }

    render();
});

watch(height, () => {
    render();
});
</script>

<template>
    <div class="studio">
        <form class="panel" @submit.prevent>
            <p class="notice">The image stays in this browser tab. It is not uploaded.</p>
            <FormField label="Image">
                <input type="file" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif" class="file" @change="onFile" />
            </FormField>
            <p v-if="fileName" class="meta">{{ fileName }} · {{ originalSize }}</p>
            <div class="fields">
                <FormField label="Width (px)">
                    <input v-model="width" inputmode="numeric" class="field" @change="syncHeight" />
                </FormField>
                <FormField label="Height (px)">
                    <input v-model="height" inputmode="numeric" class="field" @change="syncWidth" />
                </FormField>
            </div>
            <label class="check">
                <input v-model="lockRatio" type="checkbox" />
                Keep original proportions
            </label>
            <FormField label="Output type">
                <select v-model="format" class="field">
                    <option value="image/jpeg">JPEG</option>
                    <option value="image/png">PNG</option>
                    <option value="image/webp">WebP</option>
                </select>
            </FormField>
            <FormField v-if="format !== 'image/png'" label="Quality" hint="Used for JPEG and WebP. 1 is the highest.">
                <input v-model="quality" type="number" min="0.1" max="1" step="0.05" class="field" />
            </FormField>
            <Button type="button" :disabled="!preview" @click="download">Download image</Button>
        </form>
        <section class="panel" aria-live="polite">
            <h2 class="title">Preview</h2>
            <p v-if="error" class="error">{{ error }}</p>
            <img v-else-if="preview" :src="preview" class="preview" alt="Resized image preview" />
            <p v-else class="meta">Choose an image to resize or convert it here.</p>
        </section>
    </div>
</template>

<style scoped>
.studio {
    @apply grid gap-6 lg:grid-cols-[1.1fr_0.9fr];
}

.panel {
    @apply space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.notice,
.meta {
    @apply text-sm leading-6;
}

.title {
    @apply text-lg font-semibold;
}

.fields {
    @apply grid gap-4 sm:grid-cols-2;
}

.field,
.file {
    @apply w-full rounded-lg border px-3 py-2;
}

.check {
    @apply flex items-center gap-2 text-sm;
}

.preview {
    @apply mt-2 max-h-[28rem] w-full object-contain bg-slate-100 dark:bg-slate-800;
}

.error {
    @apply mt-2 text-sm text-red-700 dark:text-red-300;
}
</style>
