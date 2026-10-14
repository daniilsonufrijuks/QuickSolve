<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { computed, ref } from 'vue';

type OutputFormat = 'png' | 'jpeg' | 'jpg' | 'webp' | 'bmp' | 'ico' | 'svg' | 'pdf';
type SourceFormat = OutputFormat | 'gif' | 'jfif';

type Shortcut = { from: string; to: OutputFormat; label: string };

const error = ref('');
const notice = ref('The file stays in this tab. It is not uploaded.');
const fileName = ref('');
const sourceLabel = ref('');
const sourceCanvas = ref<HTMLCanvasElement | null>(null);
const preview = ref('');
const output = ref<OutputFormat>('jpeg');
const activeShortcut = ref('PNG to JPEG');
const quality = ref(0.85);
const busy = ref('');
const fileInput = ref<HTMLInputElement | null>(null);

const shortcuts: Shortcut[] = [
    { from: 'PNG', to: 'jpeg', label: 'PNG to JPEG' },
    { from: 'PNG', to: 'jpg', label: 'PNG to JPG' },
    { from: 'PNG', to: 'webp', label: 'PNG to WebP' },
    { from: 'PNG', to: 'ico', label: 'PNG to ICO' },
    { from: 'PNG', to: 'svg', label: 'PNG to SVG' },
    { from: 'PNG', to: 'pdf', label: 'PNG to PDF' },
    { from: 'PNG', to: 'bmp', label: 'PNG to BMP' },
    { from: 'JPEG', to: 'png', label: 'JPEG to PNG' },
    { from: 'JPEG', to: 'webp', label: 'JPEG to WebP' },
    { from: 'JPEG', to: 'pdf', label: 'JPEG to PDF' },
    { from: 'WebP', to: 'png', label: 'WebP to PNG' },
    { from: 'WebP', to: 'jpeg', label: 'WebP to JPEG' },
    { from: 'GIF', to: 'png', label: 'GIF to PNG' },
    { from: 'BMP', to: 'png', label: 'BMP to PNG' },
    { from: 'SVG', to: 'png', label: 'SVG to PNG' },
    { from: 'SVG', to: 'pdf', label: 'SVG to PDF' },
    { from: 'ICO', to: 'png', label: 'ICO to PNG' },
    { from: 'PDF', to: 'png', label: 'PDF to PNG' },
    { from: 'PDF', to: 'jpeg', label: 'PDF to JPEG' },
];

const groups = [
    { title: 'PNG', items: shortcuts.filter((item) => item.from === 'PNG') },
    { title: 'JPEG & WebP', items: shortcuts.filter((item) => item.from === 'JPEG' || item.from === 'WebP') },
    { title: 'GIF & BMP', items: shortcuts.filter((item) => item.from === 'GIF' || item.from === 'BMP') },
    { title: 'SVG, ICO & PDF', items: shortcuts.filter((item) => item.from === 'SVG' || item.from === 'ICO' || item.from === 'PDF') },
];

const usesQuality = computed(() => output.value === 'jpeg' || output.value === 'jpg' || output.value === 'webp');
const canDownload = computed(() => sourceCanvas.value !== null && !busy.value);

function extensionOf(format: OutputFormat): string {
    return format === 'jpeg' ? 'jpg' : format;
}

function mimeOf(format: OutputFormat): string {
    if (format === 'jpeg' || format === 'jpg') {
        return 'image/jpeg';
    }

    if (format === 'png' || format === 'webp' || format === 'bmp' || format === 'svg') {
        return `image/${format}`;
    }

    if (format === 'ico') {
        return 'image/x-icon';
    }

    return 'application/pdf';
}

function detectFormat(file: File): SourceFormat | null {
    const name = file.name.toLowerCase();
    const type = file.type.toLowerCase();

    if (type === 'image/png' || name.endsWith('.png')) {
        return 'png';
    }

    if (type === 'image/jpeg' || name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.jfif')) {
        return name.endsWith('.jpg') ? 'jpg' : name.endsWith('.jfif') ? 'jfif' : 'jpeg';
    }

    if (type === 'image/webp' || name.endsWith('.webp')) {
        return 'webp';
    }

    if (type === 'image/gif' || name.endsWith('.gif')) {
        return 'gif';
    }

    if (type === 'image/bmp' || type === 'image/x-ms-bmp' || name.endsWith('.bmp')) {
        return 'bmp';
    }

    if (type === 'image/svg+xml' || name.endsWith('.svg')) {
        return 'svg';
    }

    if (type === 'image/x-icon' || type === 'image/vnd.microsoft.icon' || name.endsWith('.ico')) {
        return 'ico';
    }

    if (type === 'application/pdf' || name.endsWith('.pdf')) {
        return 'pdf';
    }

    return null;
}

function cloneBytes(source: Uint8Array): Uint8Array {
    const copy = new Uint8Array(source.byteLength);
    copy.set(source);

    return copy;
}

function downloadBytes(data: Uint8Array, mime: string, name: string) {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([cloneBytes(data)], { type: mime }));
    link.download = name;
    link.click();
    URL.revokeObjectURL(link.href);
}

function canvasToBlob(canvas: HTMLCanvasElement, mime: string, qualityValue?: number): Promise<Blob> {
    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (blob) {
                    resolve(blob);
                } else {
                    reject(new Error('This browser could not encode that image type.'));
                }
            },
            mime,
            qualityValue,
        );
    });
}

async function blobToBytes(blob: Blob): Promise<Uint8Array> {
    return new Uint8Array(await blob.arrayBuffer());
}

function drawOnto(canvas: HTMLCanvasElement, image: CanvasImageSource, width: number, height: number, flattenWhite: boolean) {
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Canvas is unavailable in this browser.');
    }

    if (flattenWhite) {
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, width, height);
    } else {
        context.clearRect(0, 0, width, height);
    }

    context.drawImage(image, 0, 0, width, height);
}

function encodeBmp(canvas: HTMLCanvasElement): Uint8Array {
    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Canvas is unavailable in this browser.');
    }

    const width = canvas.width;
    const height = canvas.height;
    const pixels = context.getImageData(0, 0, width, height).data;
    const rowSize = width * 4;
    const pixelBytes = rowSize * height;
    const file = new ArrayBuffer(54 + pixelBytes);
    const view = new DataView(file);
    const out = new Uint8Array(file);

    view.setUint8(0, 0x42);
    view.setUint8(1, 0x4d);
    view.setUint32(2, file.byteLength, true);
    view.setUint32(10, 54, true);
    view.setUint32(14, 40, true);
    view.setInt32(18, width, true);
    view.setInt32(22, height, true);
    view.setUint16(26, 1, true);
    view.setUint16(28, 32, true);
    view.setUint32(34, pixelBytes, true);

    let offset = 54;

    for (let y = height - 1; y >= 0; y -= 1) {
        const row = y * width * 4;

        for (let x = 0; x < width; x += 1) {
            const i = row + x * 4;
            out[offset] = pixels[i + 2];
            out[offset + 1] = pixels[i + 1];
            out[offset + 2] = pixels[i];
            out[offset + 3] = pixels[i + 3];
            offset += 4;
        }
    }

    return out;
}

async function encodeIco(source: HTMLCanvasElement): Promise<Uint8Array> {
    const sizes = [16, 32, 48, 256];
    const frames: Uint8Array[] = [];

    for (const size of sizes) {
        const frame = document.createElement('canvas');
        drawOnto(frame, source, size, size, false);
        frames.push(await blobToBytes(await canvasToBlob(frame, 'image/png')));
    }

    const count = frames.length;
    const header = 6 + 16 * count;
    const total = frames.reduce((sum, frame) => sum + frame.byteLength, header);
    const out = new Uint8Array(total);
    const view = new DataView(out.buffer);
    view.setUint16(0, 0, true);
    view.setUint16(2, 1, true);
    view.setUint16(4, count, true);

    let offset = header;

    frames.forEach((frame, index) => {
        const entry = 6 + index * 16;
        const size = sizes[index];
        out[entry] = size >= 256 ? 0 : size;
        out[entry + 1] = size >= 256 ? 0 : size;
        view.setUint16(entry + 4, 1, true);
        view.setUint16(entry + 6, 32, true);
        view.setUint32(entry + 8, frame.byteLength, true);
        view.setUint32(entry + 12, offset, true);
        out.set(frame, offset);
        offset += frame.byteLength;
    });

    return out;
}

async function encodeSvg(source: HTMLCanvasElement): Promise<Uint8Array> {
    const png = await blobToBytes(await canvasToBlob(source, 'image/png'));
    let binary = '';

    png.forEach((byte) => {
        binary += String.fromCharCode(byte);
    });

    const markup = `<svg xmlns="http://www.w3.org/2000/svg" width="${source.width}" height="${source.height}" viewBox="0 0 ${source.width} ${source.height}"><image href="data:image/png;base64,${btoa(binary)}" width="${source.width}" height="${source.height}"/></svg>`;

    return new TextEncoder().encode(markup);
}

async function encodePdf(source: HTMLCanvasElement): Promise<Uint8Array> {
    const { PDFDocument } = await import('pdf-lib');
    const document = await PDFDocument.create();
    const png = await blobToBytes(await canvasToBlob(source, 'image/png'));
    const image = await document.embedPng(png);
    const width = image.width;
    const height = image.height;
    const page = document.addPage([width, height]);
    page.drawImage(image, { x: 0, y: 0, width, height });

    return document.save();
}

function extractPngFromIco(bytes: Uint8Array): Uint8Array | null {
    if (bytes.byteLength < 22) {
        return null;
    }

    const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);

    if (view.getUint16(0, true) !== 0 || view.getUint16(2, true) !== 1) {
        return null;
    }

    const count = view.getUint16(4, true);
    let best: Uint8Array | null = null;

    for (let index = 0; index < count; index += 1) {
        const entry = 6 + index * 16;

        if (entry + 16 > bytes.byteLength) {
            break;
        }

        const size = view.getUint32(entry + 8, true);
        const offset = view.getUint32(entry + 12, true);

        if (offset + size > bytes.byteLength || size < 8) {
            continue;
        }

        const slice = bytes.subarray(offset, offset + size);

        if (slice[0] === 0x89 && slice[1] === 0x50 && slice[2] === 0x4e && slice[3] === 0x47) {
            if (!best || slice.byteLength > best.byteLength) {
                best = slice;
            }
        }
    }

    return best;
}

function loadImage(url: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('That image could not be read in this browser.'));
        image.src = url;
    });
}

async function renderPdfPage(bytes: Uint8Array): Promise<HTMLCanvasElement> {
    const pdfjs = await import('pdfjs-dist');
    const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
    pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
    const loaded = await pdfjs.getDocument({ data: cloneBytes(bytes), isEvalSupported: false }).promise;
    const page = await loaded.getPage(1);
    const viewport = page.getViewport({ scale: 2 });
    const canvas = document.createElement('canvas');
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    await page.render({ canvas, viewport }).promise;

    try {
        await loaded.destroy();
    } catch {
        // PDF.js 6 can throw if the worker was already torn down.
    }

    return canvas;
}

function rasterize(image: CanvasImageSource, width: number, height: number): HTMLCanvasElement {
    const canvas = document.createElement('canvas');
    const max = 4096;
    const scale = Math.min(1, max / Math.max(width, height));
    drawOnto(canvas, image, Math.max(1, Math.round(width * scale)), Math.max(1, Math.round(height * scale)), false);

    return canvas;
}

async function decodeFile(file: File, format: SourceFormat): Promise<HTMLCanvasElement> {
    const buffer = new Uint8Array(await file.arrayBuffer());

    if (format === 'pdf') {
        return renderPdfPage(buffer);
    }

    let payload = buffer;
    let mime = file.type;

    if (format === 'ico') {
        const png = extractPngFromIco(buffer);

        if (!png) {
            throw new Error('This ICO uses bitmap frames this browser cannot decode. Export a PNG from another tool, then convert that.');
        }

        payload = png;
        mime = 'image/png';
    } else if (format === 'svg') {
        mime = 'image/svg+xml';
    } else if (!mime) {
        mime = format === 'jpg' || format === 'jfif' || format === 'jpeg' ? 'image/jpeg' : format === 'gif' ? 'image/gif' : format === 'bmp' ? 'image/bmp' : format === 'webp' ? 'image/webp' : 'image/png';
    }

    const blob = new Blob([cloneBytes(payload)], { type: mime });
    const url = URL.createObjectURL(blob);

    try {
        const image = await loadImage(url);

        return rasterize(image, image.naturalWidth || image.width, image.naturalHeight || image.height);
    } finally {
        URL.revokeObjectURL(url);
    }
}

function refreshPreview() {
    const canvas = sourceCanvas.value;

    if (!canvas) {
        preview.value = '';
        return;
    }

    preview.value = canvas.toDataURL('image/png');
}

async function loadSource(file: File) {
    const format = detectFormat(file);

    if (!format) {
        error.value = 'Choose a PNG, JPEG, JPG, JFIF, WebP, GIF, BMP, SVG, ICO, or PDF.';
        return;
    }

    if (file.size > 12 * 1024 * 1024) {
        error.value = 'Keep the file under 12 MB for this browser converter.';
        return;
    }

    error.value = '';
    busy.value = 'Reading file…';

    try {
        const canvas = await decodeFile(file, format);
        sourceCanvas.value = canvas;
        fileName.value = file.name;
        sourceLabel.value = `${format.toUpperCase()} · ${canvas.width}×${canvas.height}px · ${Math.round(file.size / 1024)} KB`;
        notice.value =
            format === 'gif'
                ? 'GIF animation is not kept. Only the first frame is converted.'
                : format === 'pdf'
                  ? 'Only the first PDF page is converted.'
                  : format === 'svg'
                    ? 'The SVG is drawn as a bitmap, then converted. Scripts and animation are not run.'
                    : 'The file stays in this tab. It is not uploaded.';
        refreshPreview();
    } catch (caught) {
        sourceCanvas.value = null;
        preview.value = '';
        error.value = caught instanceof Error ? caught.message : 'That file could not be converted.';
    } finally {
        busy.value = '';
    }
}

function onFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    (event.target as HTMLInputElement).value = '';

    if (file) {
        void loadSource(file);
    }
}

function onDrop(event: DragEvent) {
    event.preventDefault();
    const file = event.dataTransfer?.files?.[0];

    if (file) {
        void loadSource(file);
    }
}

function chooseShortcut(item: Shortcut) {
    output.value = item.to;
    activeShortcut.value = item.label;
    notice.value =
        item.to === 'svg'
            ? 'SVG download embeds the bitmap. It is not a traced vector drawing.'
            : item.to === 'ico'
              ? 'ICO download includes 16, 32, 48, and 256 pixel PNG frames.'
              : item.to === 'pdf'
                ? 'PDF download is one page at the image size. Text is not rebuilt.'
                : 'The file stays in this tab. It is not uploaded.';
}

async function loadSample() {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 256;
    const context = canvas.getContext('2d');

    if (!context) {
        error.value = 'Canvas is unavailable in this browser.';
        return;
    }

    context.fillStyle = '#1d4ed8';
    context.fillRect(0, 0, 256, 256);
    context.fillStyle = '#ffffff';
    context.font = '700 64px Helvetica, Arial, sans-serif';
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillText('PNG', 128, 128);
    const blob = await canvasToBlob(canvas, 'image/png');
    await loadSource(new File([blob], 'sample.png', { type: 'image/png' }));
    output.value = 'jpeg';
    activeShortcut.value = 'PNG to JPEG';
    notice.value = 'Sample PNG loaded. Pick an output such as JPEG, ICO, SVG, or PDF, then download.';
}

async function download() {
    const canvas = sourceCanvas.value;

    if (!canvas) {
        return;
    }

    busy.value = 'Preparing download…';
    error.value = '';

    try {
        const name = fileName.value.replace(/\.[^.]+$/, '') + '-converted.' + extensionOf(output.value);
        const flatten = output.value === 'jpeg' || output.value === 'jpg';
        const work = document.createElement('canvas');
        drawOnto(work, canvas, canvas.width, canvas.height, flatten);

        if (output.value === 'png' || output.value === 'jpeg' || output.value === 'jpg' || output.value === 'webp') {
            const blob = await canvasToBlob(work, mimeOf(output.value), usesQuality.value ? quality.value : undefined);
            downloadBytes(await blobToBytes(blob), blob.type, name);
        } else if (output.value === 'bmp') {
            downloadBytes(encodeBmp(work), 'image/bmp', name);
        } else if (output.value === 'ico') {
            downloadBytes(await encodeIco(work), 'image/x-icon', name);
        } else if (output.value === 'svg') {
            downloadBytes(await encodeSvg(work), 'image/svg+xml', name);
            notice.value = 'Downloaded an SVG that embeds the bitmap. Paths were not traced.';
        } else {
            downloadBytes(await encodePdf(work), 'application/pdf', name);
            notice.value = 'Downloaded a one-page PDF. The file was never sent to QuickSolve.';
        }

        if (output.value !== 'svg' && output.value !== 'pdf') {
            notice.value = `Downloaded ${output.value.toUpperCase()}. The file was never sent to QuickSolve.`;
        }
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'The file could not be converted.';
    } finally {
        busy.value = '';
    }
}
</script>

<template>
    <div class="studio">
        <form class="panel" @submit.prevent>
            <p class="notice">{{ notice }}</p>
            <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/webp,image/gif,image/bmp,image/svg+xml,image/x-icon,application/pdf,.png,.jpg,.jpeg,.jfif,.webp,.gif,.bmp,.svg,.ico,.pdf" class="hidden" @change="onFile" />
            <div class="drop" @dragover.prevent @drop="onDrop">
                <p>Drop a PNG, JPEG, WebP, GIF, BMP, SVG, ICO, or PDF here.</p>
                <div class="row">
                    <Button type="button" @click="fileInput?.click()">Open file</Button>
                    <Button type="button" variant="outline" @click="loadSample">Sample PNG</Button>
                </div>
            </div>
            <p v-if="fileName" class="meta">{{ fileName }} · {{ sourceLabel }}</p>
            <FormField label="Output">
                <select v-model="output" class="field" @change="chooseShortcut(shortcuts.find((item) => item.to === output) ?? shortcuts[0])">
                    <option value="png">PNG</option>
                    <option value="jpeg">JPEG</option>
                    <option value="jpg">JPG</option>
                    <option value="webp">WebP</option>
                    <option value="bmp">BMP</option>
                    <option value="ico">ICO</option>
                    <option value="svg">SVG (bitmap inside)</option>
                    <option value="pdf">PDF</option>
                </select>
            </FormField>
            <FormField v-if="usesQuality" label="Quality" hint="Used for JPEG, JPG, and WebP. 1 is the highest.">
                <input v-model.number="quality" type="number" min="0.1" max="1" step="0.05" class="field" />
            </FormField>
            <Button type="button" :disabled="!canDownload" @click="download">Download</Button>
            <p v-if="busy" class="meta">{{ busy }}</p>
            <p v-if="error" class="error">{{ error }}</p>
        </form>

        <section class="panel" aria-live="polite">
            <h2 class="title">Preview</h2>
            <img v-if="preview" :src="preview" class="preview" alt="Converted image preview" />
            <p v-else class="meta">Open a file or load the sample PNG, then pick an output format.</p>
        </section>

        <section class="conversions" aria-label="Conversion shortcuts">
            <div v-for="group in groups" :key="group.title" class="column">
                <h3 class="heading">{{ group.title }}</h3>
                <button
                    v-for="item in group.items"
                    :key="item.label"
                    type="button"
                    class="chip"
                    :class="{ on: activeShortcut === item.label }"
                    @click="chooseShortcut(item)"
                >
                    {{ item.label }}
                </button>
            </div>
        </section>
    </div>
</template>

<style scoped>
.studio {
    @apply grid gap-6;
}

.panel {
    @apply space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.notice,
.meta {
    @apply text-sm leading-6 text-slate-600 dark:text-slate-300;
}

.title {
    @apply text-lg font-semibold text-slate-900 dark:text-slate-100;
}

.drop {
    @apply flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm dark:border-slate-700 dark:bg-slate-950;
}

.row {
    @apply flex flex-wrap justify-center gap-2;
}

.field {
    @apply w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100;
}

.preview {
    @apply mt-2 max-h-[28rem] w-full bg-slate-100 object-contain dark:bg-slate-800;
}

.error {
    @apply text-sm text-red-700 dark:text-red-300;
}

.hidden {
    @apply sr-only;
}

.conversions {
    @apply grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900;
}

.column {
    @apply flex flex-col gap-1;
}

.heading {
    @apply mb-1 text-sm font-semibold text-slate-900 dark:text-slate-100;
}

.chip {
    @apply rounded-md px-2 py-1.5 text-left text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800;
}

.chip.on {
    @apply bg-slate-900 text-white dark:bg-blue-600;
}
</style>
