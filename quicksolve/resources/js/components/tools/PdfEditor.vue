<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { PDFDocument, StandardFonts, degrees, rgb } from 'pdf-lib';
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist';
import workerSrc from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { nextTick, ref } from 'vue';

GlobalWorkerOptions.workerSrc = workerSrc;

const error = ref('');
const fileName = ref('');
const pageCount = ref(0);
const currentPage = ref(1);
const bytes = ref<Uint8Array | null>(null);
const previewUrl = ref('');
const note = ref('');
const noteX = ref('72');
const noteY = ref('72');
const rendering = ref(false);

async function onFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        error.value = 'Choose a PDF file.';
        return;
    }

    if (file.size > 8 * 1024 * 1024) {
        error.value = 'Keep the PDF under 8 MB for this browser editor.';
        return;
    }

    error.value = '';
    fileName.value = file.name;
    bytes.value = new Uint8Array(await file.arrayBuffer());
    currentPage.value = 1;
    await refreshPreview();
}

async function pdf() {
    if (!bytes.value) {
        throw new Error('No PDF loaded.');
    }

    return PDFDocument.load(bytes.value, { ignoreEncryption: false });
}

async function refreshPreview() {
    if (!bytes.value) {
        previewUrl.value = '';
        pageCount.value = 0;
        return;
    }

    rendering.value = true;

    try {
        const loaded = await getDocument({ data: bytes.value.slice() }).promise;
        pageCount.value = loaded.numPages;
        currentPage.value = Math.min(Math.max(currentPage.value, 1), pageCount.value);
        const page = await loaded.getPage(currentPage.value);
        const viewport = page.getViewport({ scale: 1.25 });
        const canvas = window.document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const context = canvas.getContext('2d');

        if (!context) {
            throw new Error('Canvas is unavailable.');
        }

        await page.render({ canvas, canvasContext: context, viewport }).promise;
        previewUrl.value = canvas.toDataURL('image/png');
        await loaded.destroy();
    } catch {
        error.value = 'This PDF could not be opened. Password-protected files are not supported.';
        previewUrl.value = '';
    } finally {
        rendering.value = false;
        await nextTick();
    }
}

async function rotate() {
    try {
        const document = await pdf();
        const page = document.getPage(currentPage.value - 1);
        page.setRotation(degrees((page.getRotation().angle + 90) % 360));
        bytes.value = await document.save();
        await refreshPreview();
    } catch {
        error.value = 'The page could not be rotated.';
    }
}

async function addNote() {
    const text = note.value.trim();
    const x = Number(noteX.value);
    const y = Number(noteY.value);

    if (text === '' || Number.isNaN(x) || Number.isNaN(y)) {
        error.value = 'Enter the note text and a position in points from the lower left of the page.';
        return;
    }

    try {
        const document = await pdf();
        const font = await document.embedFont(StandardFonts.Helvetica);
        const page = document.getPage(currentPage.value - 1);
        page.drawText(text, {
            x,
            y,
            size: 12,
            font,
            color: rgb(0.13, 0.18, 0.28),
        });
        bytes.value = await document.save();
        note.value = '';
        await refreshPreview();
    } catch {
        error.value = 'The note could not be added. Try a shorter line of text.';
    }
}

async function download() {
    if (!bytes.value) {
        return;
    }

    const blob = new Blob([bytes.value], { type: 'application/pdf' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = fileName.value.replace(/\.pdf$/i, '') + '-edited.pdf';
    link.click();
    URL.revokeObjectURL(link.href);
}

async function go(step: number) {
    currentPage.value += step;
    await refreshPreview();
}
</script>

<template>
    <div class="studio">
        <form class="panel" @submit.prevent>
            <p class="notice">The PDF stays in this browser tab. QuickSolve does not store it.</p>
            <FormField label="PDF file">
                <input type="file" accept="application/pdf,.pdf" class="file" @change="onFile" />
            </FormField>
            <p v-if="fileName" class="meta">{{ fileName }} · {{ pageCount }} page{{ pageCount === 1 ? '' : 's' }}</p>
            <div v-if="bytes" class="actions">
                <Button type="button" variant="outline" :disabled="currentPage <= 1 || rendering" @click="go(-1)">Previous page</Button>
                <Button type="button" variant="outline" :disabled="currentPage >= pageCount || rendering" @click="go(1)">Next page</Button>
                <Button type="button" variant="outline" :disabled="rendering" @click="rotate">Rotate page 90°</Button>
            </div>
            <FormField v-if="bytes" label="Text note" hint="Drawn on the current page. Coordinates are PDF points from the lower left.">
                <input v-model="note" class="field" maxlength="120" />
            </FormField>
            <div v-if="bytes" class="fields">
                <FormField label="X">
                    <input v-model="noteX" inputmode="numeric" class="field" />
                </FormField>
                <FormField label="Y">
                    <input v-model="noteY" inputmode="numeric" class="field" />
                </FormField>
            </div>
            <div v-if="bytes" class="actions">
                <Button type="button" @click="addNote">Add note</Button>
                <Button type="button" variant="outline" @click="download">Download PDF</Button>
            </div>
        </form>
        <section class="panel" aria-live="polite">
            <h2 class="title">Preview</h2>
            <p v-if="error" class="error">{{ error }}</p>
            <p v-else-if="rendering" class="meta">Rendering page {{ currentPage }}…</p>
            <img v-else-if="previewUrl" :src="previewUrl" class="preview" :alt="`Page ${currentPage} of ${pageCount}`" />
            <p v-else class="meta">Choose a PDF to read and edit it here.</p>
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

.actions {
    @apply flex flex-wrap gap-2;
}

.fields {
    @apply grid gap-4 sm:grid-cols-2;
}

.field,
.file {
    @apply w-full rounded-lg border px-3 py-2;
}

.preview {
    @apply mt-2 w-full max-w-full bg-white;
}

.error {
    @apply mt-2 text-sm text-red-700 dark:text-red-300;
}
</style>
