<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist';
import workerSrc from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { nextTick, ref } from 'vue';

GlobalWorkerOptions.workerSrc = workerSrc;

type Overlay = {
    id: string;
    page: number;
    x: number;
    y: number;
    text: string;
    size: number;
};

type Mode = 'page' | 'document';

const error = ref('');
const fileName = ref('document.pdf');
const pageCount = ref(0);
const currentPage = ref(1);
const bytes = ref<Uint8Array | null>(null);
const previewUrl = ref('');
const embedUrl = ref('');
const canvasWidth = ref(0);
const canvasHeight = ref(0);
const pageWidth = ref(595);
const pageHeight = ref(842);
const rendering = ref(false);
const mode = ref<Mode>('page');
const documentText = ref('');
const overlays = ref<Overlay[]>([]);
const selectedId = ref('');
const draft = ref('Type here');
const fontSize = ref(14);
const stage = ref<HTMLElement | null>(null);

function cloneBytes(source: Uint8Array): Uint8Array {
    const copy = new Uint8Array(source.byteLength);
    copy.set(source);

    return copy;
}

function hexToRgb(hex: string) {
    const value = hex.replace('#', '');

    return rgb(Number.parseInt(value.slice(0, 2), 16) / 255, Number.parseInt(value.slice(2, 4), 16) / 255, Number.parseInt(value.slice(4, 6), 16) / 255);
}

async function onFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        error.value = 'Choose a PDF file.';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        error.value = 'Keep the PDF under 10 MB for this browser editor.';
        return;
    }

    error.value = '';
    fileName.value = file.name;
    overlays.value = [];
    bytes.value = cloneBytes(new Uint8Array(await file.arrayBuffer()));
    currentPage.value = 1;
    await loadDocument();
}

async function startBlank() {
    const document = await PDFDocument.create();
    const page = document.addPage([595.28, 841.89]);
    const font = await document.embedFont(StandardFonts.Helvetica);
    page.drawText('Start writing in Document mode, or click the page to add text.', {
        x: 48,
        y: 780,
        size: 12,
        font,
        color: rgb(0.2, 0.24, 0.32),
    });
    bytes.value = await document.save();
    fileName.value = 'untitled.pdf';
    overlays.value = [];
    documentText.value = '';
    currentPage.value = 1;
    error.value = '';
    mode.value = 'document';
    await loadDocument();
}

async function loadDocument() {
    if (!bytes.value) {
        return;
    }

    if (embedUrl.value) {
        URL.revokeObjectURL(embedUrl.value);
    }

    embedUrl.value = URL.createObjectURL(new Blob([cloneBytes(bytes.value)], { type: 'application/pdf' }));

    try {
        const loaded = await PDFDocument.load(cloneBytes(bytes.value), { ignoreEncryption: true });
        pageCount.value = loaded.getPageCount();
        const size = loaded.getPage(currentPage.value - 1).getSize();
        pageWidth.value = size.width;
        pageHeight.value = size.height;
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'This PDF could not be opened.';
        return;
    }

    await extractText();
    await refreshPreview();
}

async function extractText() {
    if (!bytes.value) {
        return;
    }

    try {
        const loaded = await getDocument({ data: cloneBytes(bytes.value).buffer, isEvalSupported: false }).promise;
        const parts: string[] = [];

        for (let index = 1; index <= loaded.numPages; index += 1) {
            const page = await loaded.getPage(index);
            const content = await page.getTextContent();
            const lines: string[] = [];
            let lastY: number | null = null;
            let line = '';

            for (const item of content.items) {
                if (!('str' in item)) {
                    continue;
                }

                const y = Math.round(item.transform[5]);

                if (lastY !== null && Math.abs(lastY - y) > 4) {
                    lines.push(line.trimEnd());
                    line = '';
                }

                line += item.str + (item.hasEOL ? '\n' : ' ');
                lastY = y;
            }

            if (line.trim() !== '') {
                lines.push(line.trimEnd());
            }

            parts.push(lines.join('\n').trim());
        }

        documentText.value = parts.filter(Boolean).join('\n\n');
    } catch {
        if (documentText.value === '') {
            documentText.value = '';
        }
    }
}

async function refreshPreview() {
    if (!bytes.value) {
        previewUrl.value = '';
        return;
    }

    rendering.value = true;

    try {
        const loaded = await getDocument({ data: cloneBytes(bytes.value).buffer, isEvalSupported: false }).promise;
        pageCount.value = loaded.numPages;
        currentPage.value = Math.min(Math.max(currentPage.value, 1), pageCount.value);
        const page = await loaded.getPage(currentPage.value);
        const viewport = page.getViewport({ scale: 1.35 });
        const canvas = window.document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        canvasWidth.value = viewport.width;
        canvasHeight.value = viewport.height;
        await page.render({ canvas, viewport }).promise;
        previewUrl.value = canvas.toDataURL('image/png');
        error.value = '';
    } catch (caught) {
        previewUrl.value = '';
        const detail = caught instanceof Error ? caught.message : 'Unknown preview error';
        const lower = detail.toLowerCase();

        if (lower.includes('password') || lower.includes('encrypt')) {
            error.value = 'This PDF is password-protected. Unlock it elsewhere, then try again.';
        } else {
            error.value = `Page preview could not be drawn (${detail}). The built-in viewer below still opens the file.`;
        }
    } finally {
        rendering.value = false;
        await nextTick();
    }
}

function placeText(event: MouseEvent) {
    if (!stage.value || mode.value !== 'page') {
        return;
    }

    const target = event.target as HTMLElement;

    if (target.closest('textarea')) {
        return;
    }

    const box = stage.value.getBoundingClientRect();
    const x = ((event.clientX - box.left) / box.width) * pageWidth.value;
    const y = pageHeight.value - ((event.clientY - box.top) / box.height) * pageHeight.value;
    const overlay: Overlay = {
        id: crypto.randomUUID(),
        page: currentPage.value,
        x,
        y,
        text: draft.value.trim() === '' ? 'Type here' : draft.value.trim(),
        size: fontSize.value,
    };
    overlays.value = [...overlays.value, overlay];
    selectedId.value = overlay.id;
}

function overlayStyle(overlay: Overlay) {
    return {
        left: `${(overlay.x / pageWidth.value) * 100}%`,
        top: `${((pageHeight.value - overlay.y) / pageHeight.value) * 100}%`,
        fontSize: `${overlay.size}px`,
        width: '42%',
    };
}

function removeSelected() {
    overlays.value = overlays.value.filter((overlay) => overlay.id !== selectedId.value);
    selectedId.value = '';
}

async function applyOverlays() {
    if (!bytes.value) {
        throw new Error('No PDF loaded.');
    }

    const document = await PDFDocument.load(cloneBytes(bytes.value), { ignoreEncryption: true });
    const font = await document.embedFont(StandardFonts.Helvetica);

    for (const overlay of overlays.value) {
        const page = document.getPage(overlay.page - 1);
        const lines = overlay.text.split('\n');
        lines.forEach((line, index) => {
            page.drawText(line, {
                x: overlay.x,
                y: overlay.y - index * (overlay.size + 4),
                size: overlay.size,
                font,
                color: hexToRgb('1e293b'),
                maxWidth: page.getWidth() - overlay.x - 36,
            });
        });
    }

    return document;
}

async function downloadPagePdf() {
    const document = await applyOverlays();
    bytes.value = await document.save();
    overlays.value = [];
    await finishDownload(bytes.value, '-edited.pdf');
    await loadDocument();
}

async function downloadDocumentPdf() {
    const document = await PDFDocument.create();
    const font = await document.embedFont(StandardFonts.Helvetica);
    const bold = await document.embedFont(StandardFonts.HelveticaBold);
    const margin = 54;
    const size = 11;
    const leading = 16;
    let page = document.addPage([595.28, 841.89]);
    let y = page.getHeight() - margin;
    const maxWidth = page.getWidth() - margin * 2;
    const paragraphs = documentText.value.replace(/\r\n/g, '\n').split('\n');

    for (const paragraph of paragraphs) {
        const heading = paragraph.startsWith('# ');
        const text = heading ? paragraph.slice(2) : paragraph;
        const used = heading ? bold : font;
        const usedSize = heading ? 16 : size;
        const words = text === '' ? [''] : text.split(/\s+/);
        let line = '';

        const flush = async (current: string) => {
            if (y < margin + leading) {
                page = document.addPage([595.28, 841.89]);
                y = page.getHeight() - margin;
            }

            page.drawText(current, { x: margin, y, size: usedSize, font: used, color: rgb(0.12, 0.16, 0.24) });
            y -= heading ? 22 : leading;
        };

        for (const word of words) {
            const next = line === '' ? word : `${line} ${word}`;

            if (used.widthOfTextAtSize(next, usedSize) > maxWidth && line !== '') {
                await flush(line);
                line = word;
            } else {
                line = next;
            }
        }

        await flush(line);
        y -= 6;
    }

    await finishDownload(await document.save(), '-document.pdf');
}

async function finishDownload(data: Uint8Array, suffix: string) {
    const blob = new Blob([data], { type: 'application/pdf' });
    const link = window.document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = fileName.value.replace(/\.pdf$/i, '') + suffix;
    link.click();
    URL.revokeObjectURL(link.href);
}

async function go(step: number) {
    currentPage.value += step;
    await loadDocument();
}

const pageOverlays = () => overlays.value.filter((overlay) => overlay.page === currentPage.value);
</script>

<template>
    <div class="studio">
        <form class="panel" @submit.prevent>
            <p class="notice">The PDF stays in this browser tab. QuickSolve does not store it.</p>
            <div class="modes">
                <Button type="button" :variant="mode === 'page' ? 'default' : 'outline'" @click="mode = 'page'">Page editor</Button>
                <Button type="button" :variant="mode === 'document' ? 'default' : 'outline'" @click="mode = 'document'">Document editor</Button>
            </div>
            <FormField label="PDF file">
                <input type="file" accept="application/pdf,.pdf" class="file" @change="onFile" />
            </FormField>
            <Button type="button" variant="outline" @click="startBlank">Start a blank document</Button>
            <p v-if="bytes" class="meta">{{ fileName }} · {{ pageCount }} page{{ pageCount === 1 ? '' : 's' }}</p>

            <template v-if="mode === 'page' && bytes">
                <div class="actions">
                    <Button type="button" variant="outline" :disabled="currentPage <= 1 || rendering" @click="go(-1)">Previous</Button>
                    <Button type="button" variant="outline" :disabled="currentPage >= pageCount || rendering" @click="go(1)">Next</Button>
                </div>
                <FormField label="New text" hint="Click the page to drop this text. Click a box to edit it.">
                    <input v-model="draft" class="field" maxlength="400" />
                </FormField>
                <FormField label="Size">
                    <input v-model.number="fontSize" type="number" min="8" max="48" class="field" />
                </FormField>
                <div class="actions">
                    <Button type="button" variant="outline" :disabled="!selectedId" @click="removeSelected">Remove selected</Button>
                    <Button type="button" @click="downloadPagePdf">Download edited PDF</Button>
                </div>
            </template>

            <template v-if="mode === 'document'">
                <p class="meta">Edit the extracted wording like a simple document. Lines that start with # become headings. Layout of the original file is not kept.</p>
                <Button type="button" :disabled="documentText.trim() === ''" @click="downloadDocumentPdf">Download as PDF</Button>
            </template>
        </form>

        <section class="panel" aria-live="polite">
            <h2 class="title">{{ mode === 'document' ? 'Document' : `Page ${currentPage}` }}</h2>
            <p v-if="error" class="error">{{ error }}</p>
            <p v-if="rendering" class="meta">Rendering page {{ currentPage }}…</p>

            <textarea v-if="mode === 'document'" v-model="documentText" class="editor" rows="22" placeholder="Paste or type your document. Extracted PDF text appears here when the file can be read." />

            <div v-else-if="previewUrl" ref="stage" class="stage" @click="placeText">
                <img :src="previewUrl" class="preview" :alt="`Page ${currentPage} of ${pageCount}`" :width="canvasWidth" :height="canvasHeight" />
                <textarea
                    v-for="overlay in pageOverlays()"
                    :key="overlay.id"
                    v-model="overlay.text"
                    class="overlay"
                    :class="{ selected: overlay.id === selectedId }"
                    :style="overlayStyle(overlay)"
                    @click.stop="selectedId = overlay.id"
                />
            </div>
            <embed v-else-if="embedUrl && mode === 'page'" :src="embedUrl" type="application/pdf" class="embed" />
            <p v-else class="meta">Choose a PDF, or start a blank document.</p>
        </section>
    </div>
</template>

<style scoped>
.studio {
    @apply grid gap-6 lg:grid-cols-[0.9fr_1.1fr];
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

.modes,
.actions {
    @apply flex flex-wrap gap-2;
}

.field,
.file,
.editor {
    @apply w-full rounded-lg border px-3 py-2;
}

.editor {
    @apply min-h-[28rem] font-sans text-base leading-7;
}

.preview {
    @apply w-full max-w-full bg-white;
}

.stage {
    @apply relative cursor-text overflow-hidden rounded border bg-slate-100;
}

.overlay {
    @apply absolute -translate-y-1/2 resize-none rounded border border-blue-400 bg-white/90 px-1 py-0.5 leading-snug text-slate-900;
}

.selected {
    @apply ring-2 ring-blue-600;
}

.embed {
    @apply mt-2 h-[36rem] w-full rounded border;
}

.error {
    @apply mt-2 text-sm text-red-700 dark:text-red-300;
}
</style>
