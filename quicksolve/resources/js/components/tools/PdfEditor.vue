<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    ChevronLeft,
    ChevronRight,
    Download,
    FilePlus,
    FileUp,
    Hand,
    Highlighter,
    ImagePlus,
    Layers,
    Maximize2,
    Minimize2,
    MousePointer2,
    Pencil,
    RotateCw,
    Scissors,
    Trash2,
    Type,
    ZoomIn,
    ZoomOut,
} from 'lucide-vue-next';
import { PDFDocument, StandardFonts, degrees, rgb } from 'pdf-lib';
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist';
import workerSrc from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { computed, nextTick, ref } from 'vue';

GlobalWorkerOptions.workerSrc = workerSrc;

type Point = { x: number; y: number };
type Tool = 'select' | 'hand' | 'text' | 'highlight' | 'draw' | 'image';
type Overlay =
    | { kind: 'text'; id: string; page: number; x: number; y: number; text: string; size: number; color: string }
    | { kind: 'highlight'; id: string; page: number; x: number; y: number; w: number; h: number; color: string }
    | { kind: 'ink'; id: string; page: number; points: Point[]; color: string; width: number }
    | { kind: 'image'; id: string; page: number; x: number; y: number; w: number; h: number; dataUrl: string; mime: string };

const error = ref('');
const notice = ref('The PDF stays in this browser tab. QuickSolve does not store it.');
const fileName = ref('document.pdf');
const pageCount = ref(0);
const currentPage = ref(1);
const bytes = ref<Uint8Array | null>(null);
const previewUrl = ref('');
const thumbs = ref<string[]>([]);
const canvasWidth = ref(0);
const canvasHeight = ref(0);
const pageWidth = ref(595);
const pageHeight = ref(842);
const rendering = ref(false);
const busy = ref('');
const tool = ref<Tool>('select');
const overlays = ref<Overlay[]>([]);
const selectedId = ref('');
const draft = ref('Type here');
const fontSize = ref(16);
const inkColor = ref('#1d4ed8');
const highlightColor = ref('#facc15');
const zoom = ref(110);
const imagePayload = ref<{ dataUrl: string; mime: string; w: number; h: number } | null>(null);
const convertOpen = ref(false);
const stage = ref<HTMLElement | null>(null);
const drawing = ref<Point[] | null>(null);
const drag = ref<{ id: string; dx: number; dy: number } | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const mergeInput = ref<HTMLInputElement | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
const photosInput = ref<HTMLInputElement | null>(null);

const pageOverlays = computed(() => overlays.value.filter((item) => item.page === currentPage.value));
const textMarks = computed(() => pageOverlays.value.filter((item): item is Extract<Overlay, { kind: 'text' }> => item.kind === 'text'));
const highlightMarks = computed(() => pageOverlays.value.filter((item): item is Extract<Overlay, { kind: 'highlight' }> => item.kind === 'highlight'));
const imageMarks = computed(() => pageOverlays.value.filter((item): item is Extract<Overlay, { kind: 'image' }> => item.kind === 'image'));
const inkMarks = computed(() => pageOverlays.value.filter((item): item is Extract<Overlay, { kind: 'ink' }> => item.kind === 'ink'));
const zoomLabel = computed(() => `${Math.round(zoom.value)}%`);

function cloneBytes(source: Uint8Array): Uint8Array {
    const copy = new Uint8Array(source.byteLength);
    copy.set(source);

    return copy;
}

function hexToRgb(hex: string) {
    const value = hex.replace('#', '');

    return rgb(Number.parseInt(value.slice(0, 2), 16) / 255, Number.parseInt(value.slice(2, 4), 16) / 255, Number.parseInt(value.slice(4, 6), 16) / 255);
}

function dataUrlToBytes(dataUrl: string): Uint8Array {
    const binary = atob(dataUrl.split(',')[1] ?? '');
    const out = new Uint8Array(binary.length);

    for (let index = 0; index < binary.length; index += 1) {
        out[index] = binary.charCodeAt(index);
    }

    return out;
}

function pdfFromEvent(event: MouseEvent): Point | null {
    if (!stage.value) {
        return null;
    }

    const box = stage.value.getBoundingClientRect();
    const x = ((event.clientX - box.left) / box.width) * pageWidth.value;
    const y = pageHeight.value - ((event.clientY - box.top) / box.height) * pageHeight.value;

    return { x, y };
}

async function loadPdfBytes(data: Uint8Array, name: string) {
    error.value = '';
    overlays.value = [];
    selectedId.value = '';
    bytes.value = cloneBytes(data);
    fileName.value = name;
    currentPage.value = 1;
    await reload();
}

async function onOpen(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    (event.target as HTMLInputElement).value = '';

    if (!file) {
        return;
    }

    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        error.value = 'Choose a PDF file.';
        return;
    }

    if (file.size > 15 * 1024 * 1024) {
        error.value = 'Keep the PDF under 15 MB for this browser editor.';
        return;
    }

    await loadPdfBytes(new Uint8Array(await file.arrayBuffer()), file.name);
}

async function startBlank() {
    const document = await PDFDocument.create();
    document.addPage([595.28, 841.89]);
    await loadPdfBytes(await document.save(), 'untitled.pdf');
    notice.value = 'Blank A4 page. Use Text, Draw, or Image to edit, then download.';
}

async function reload() {
    if (!bytes.value) {
        return;
    }

    try {
        const loaded = await PDFDocument.load(cloneBytes(bytes.value), { ignoreEncryption: true });
        pageCount.value = loaded.getPageCount();
        currentPage.value = Math.min(Math.max(currentPage.value, 1), Math.max(pageCount.value, 1));
        const size = loaded.getPage(currentPage.value - 1).getSize();
        pageWidth.value = size.width;
        pageHeight.value = size.height;
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'This PDF could not be opened.';
        return;
    }

    await refreshPreview();
    await renderThumbs();
}

async function pdfjsDoc() {
    return getDocument({ data: cloneBytes(bytes.value as Uint8Array), isEvalSupported: false }).promise;
}

async function renderPageToUrl(pageNumber: number, scale: number, type: 'image/png' | 'image/jpeg' = 'image/png', quality = 0.92) {
    const loaded = await pdfjsDoc();
    const page = await loaded.getPage(pageNumber);
    const viewport = page.getViewport({ scale });
    const canvas = window.document.createElement('canvas');
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    await page.render({ canvas, viewport }).promise;

    return { url: canvas.toDataURL(type, quality), width: viewport.width, height: viewport.height };
}

async function refreshPreview() {
    if (!bytes.value) {
        previewUrl.value = '';
        return;
    }

    rendering.value = true;

    try {
        const scale = Math.min(Math.max(zoom.value / 100, 0.4), 2.4) * 1.15;
        const rendered = await renderPageToUrl(currentPage.value, scale);
        previewUrl.value = rendered.url;
        canvasWidth.value = rendered.width;
        canvasHeight.value = rendered.height;
        error.value = '';
    } catch (caught) {
        previewUrl.value = '';
        const detail = caught instanceof Error ? caught.message : 'Unknown preview error';

        if (detail.toLowerCase().includes('password') || detail.toLowerCase().includes('encrypt')) {
            error.value = 'This PDF is password-protected. Unlock it elsewhere, then try again.';
        } else {
            error.value = `Page preview could not be drawn (${detail}).`;
        }
    } finally {
        rendering.value = false;
        await nextTick();
    }
}

async function renderThumbs() {
    if (!bytes.value) {
        thumbs.value = [];
        return;
    }

    const count = pageCount.value;
    const next = Array.from({ length: count }, () => '');
    thumbs.value = next;

    try {
        const loaded = await pdfjsDoc();

        for (let index = 1; index <= loaded.numPages; index += 1) {
            const page = await loaded.getPage(index);
            const viewport = page.getViewport({ scale: 0.18 });
            const canvas = window.document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            await page.render({ canvas, viewport }).promise;
            next[index - 1] = canvas.toDataURL('image/jpeg', 0.55);
            thumbs.value = [...next];
        }
    } catch {
        thumbs.value = next;
    }
}

async function goTo(page: number) {
    if (!bytes.value) {
        return;
    }

    currentPage.value = Math.min(Math.max(page, 1), pageCount.value);
    selectedId.value = '';

    try {
        const loaded = await PDFDocument.load(cloneBytes(bytes.value), { ignoreEncryption: true });
        const size = loaded.getPage(currentPage.value - 1).getSize();
        pageWidth.value = size.width;
        pageHeight.value = size.height;
    } catch {
        error.value = 'That page could not be opened.';
        return;
    }

    await refreshPreview();
}

async function setZoom(value: number) {
    zoom.value = Math.min(Math.max(value, 40), 240);
    await refreshPreview();
}

function overlayStyle(item: Overlay) {
    if (item.kind === 'text') {
        return {
            left: `${(item.x / pageWidth.value) * 100}%`,
            top: `${((pageHeight.value - item.y) / pageHeight.value) * 100}%`,
            fontSize: `${item.size * (zoom.value / 100)}px`,
            color: item.color,
            width: '46%',
        };
    }

    if (item.kind === 'highlight' || item.kind === 'image') {
        return {
            left: `${(item.x / pageWidth.value) * 100}%`,
            top: `${((pageHeight.value - item.y - item.h) / pageHeight.value) * 100}%`,
            width: `${(item.w / pageWidth.value) * 100}%`,
            height: `${(item.h / pageHeight.value) * 100}%`,
        };
    }

    return {};
}

function inkPath(item: Extract<Overlay, { kind: 'ink' }>) {
    return item.points
        .map((point, index) => {
            const x = (point.x / pageWidth.value) * 100;
            const y = ((pageHeight.value - point.y) / pageHeight.value) * 100;

            return `${index === 0 ? 'M' : 'L'} ${x} ${y}`;
        })
        .join(' ');
}

function onStageDown(event: MouseEvent) {
    if (event.button !== 0 || !bytes.value) {
        return;
    }

    const point = pdfFromEvent(event);

    if (!point) {
        return;
    }

    if (tool.value === 'text') {
        const overlay: Overlay = {
            kind: 'text',
            id: crypto.randomUUID(),
            page: currentPage.value,
            x: point.x,
            y: point.y,
            text: draft.value.trim() === '' ? 'Type here' : draft.value.trim(),
            size: fontSize.value,
            color: inkColor.value,
        };
        overlays.value = [...overlays.value, overlay];
        selectedId.value = overlay.id;
        return;
    }

    if (tool.value === 'image' && imagePayload.value) {
        const overlay: Overlay = {
            kind: 'image',
            id: crypto.randomUUID(),
            page: currentPage.value,
            x: point.x,
            y: point.y - imagePayload.value.h,
            w: imagePayload.value.w,
            h: imagePayload.value.h,
            dataUrl: imagePayload.value.dataUrl,
            mime: imagePayload.value.mime,
        };
        overlays.value = [...overlays.value, overlay];
        selectedId.value = overlay.id;
        imagePayload.value = null;
        return;
    }

    if (tool.value === 'draw') {
        drawing.value = [point];
        return;
    }

    if (tool.value === 'highlight') {
        drawing.value = [point];
    }
}

function onStageMove(event: MouseEvent) {
    const point = pdfFromEvent(event);

    if (!point) {
        return;
    }

    if (drawing.value && tool.value === 'draw') {
        drawing.value = [...drawing.value, point];
        return;
    }

    if (drag.value) {
        const item = overlays.value.find((overlay) => overlay.id === drag.value?.id);

        if (!item || item.kind === 'ink') {
            return;
        }

        item.x = point.x - drag.value.dx;
        item.y = point.y - drag.value.dy;
    }
}

function onStageUp(event: MouseEvent) {
    const point = pdfFromEvent(event);

    if (drawing.value && tool.value === 'draw' && drawing.value.length > 1) {
        overlays.value = [
            ...overlays.value,
            { kind: 'ink', id: crypto.randomUUID(), page: currentPage.value, points: drawing.value, color: inkColor.value, width: 2.2 },
        ];
    }

    if (drawing.value && tool.value === 'highlight' && point) {
        const start = drawing.value[0];
        const x = Math.min(start.x, point.x);
        const y = Math.min(start.y, point.y);
        const w = Math.abs(point.x - start.x);
        const h = Math.abs(point.y - start.y);

        if (w > 4 && h > 4) {
            overlays.value = [
                ...overlays.value,
                { kind: 'highlight', id: crypto.randomUUID(), page: currentPage.value, x, y, w, h, color: highlightColor.value },
            ];
        }
    }

    drawing.value = null;
    drag.value = null;
}

function startDrag(event: MouseEvent, item: Overlay) {
    if (tool.value !== 'select' || item.kind === 'ink') {
        return;
    }

    event.stopPropagation();
    selectedId.value = item.id;
    const point = pdfFromEvent(event);

    if (!point) {
        return;
    }

    drag.value = { id: item.id, dx: point.x - item.x, dy: point.y - item.y };
}

function removeSelected() {
    overlays.value = overlays.value.filter((item) => item.id !== selectedId.value);
    selectedId.value = '';
}

async function bake(): Promise<Uint8Array> {
    if (!bytes.value) {
        throw new Error('No PDF loaded.');
    }

    const document = await PDFDocument.load(cloneBytes(bytes.value), { ignoreEncryption: true });
    const font = await document.embedFont(StandardFonts.Helvetica);

    for (const overlay of overlays.value) {
        const page = document.getPage(overlay.page - 1);

        if (overlay.kind === 'text') {
            overlay.text.split('\n').forEach((line, index) => {
                page.drawText(line, {
                    x: overlay.x,
                    y: overlay.y - index * (overlay.size + 4),
                    size: overlay.size,
                    font,
                    color: hexToRgb(overlay.color),
                    maxWidth: page.getWidth() - overlay.x - 24,
                });
            });
        }

        if (overlay.kind === 'highlight') {
            page.drawRectangle({
                x: overlay.x,
                y: overlay.y,
                width: overlay.w,
                height: overlay.h,
                color: hexToRgb(overlay.color),
                opacity: 0.35,
            });
        }

        if (overlay.kind === 'ink') {
            for (let index = 1; index < overlay.points.length; index += 1) {
                page.drawLine({
                    start: overlay.points[index - 1],
                    end: overlay.points[index],
                    thickness: overlay.width,
                    color: hexToRgb(overlay.color),
                });
            }
        }

        if (overlay.kind === 'image') {
            const raw = dataUrlToBytes(overlay.dataUrl);
            const image = overlay.mime.includes('png') ? await document.embedPng(raw) : await document.embedJpg(raw);
            page.drawImage(image, { x: overlay.x, y: overlay.y, width: overlay.w, height: overlay.h });
        }
    }

    const saved = await document.save();
    overlays.value = [];
    selectedId.value = '';

    return saved;
}

async function persistBake() {
    bytes.value = await bake();
    await reload();
}

function downloadBytes(data: Uint8Array, suffix: string) {
    const link = window.document.createElement('a');
    link.href = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }));
    link.download = fileName.value.replace(/\.pdf$/i, '') + suffix;
    link.click();
    URL.revokeObjectURL(link.href);
}

async function downloadEdited() {
    busy.value = 'Preparing download…';

    try {
        const data = overlays.value.length ? await bake() : cloneBytes(bytes.value as Uint8Array);
        bytes.value = data;
        downloadBytes(data, '-edited.pdf');
        await reload();
        notice.value = 'Downloaded. The file was never sent to QuickSolve.';
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'The PDF could not be saved.';
    } finally {
        busy.value = '';
    }
}

async function mergePdfs(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    (event.target as HTMLInputElement).value = '';

    if (!bytes.value || files.length === 0) {
        return;
    }

    busy.value = 'Merging…';

    try {
        const document = await PDFDocument.load(overlays.value.length ? await bake() : cloneBytes(bytes.value), { ignoreEncryption: true });

        for (const file of files) {
            const donor = await PDFDocument.load(new Uint8Array(await file.arrayBuffer()), { ignoreEncryption: true });
            const copied = await document.copyPages(donor, donor.getPageIndices());
            copied.forEach((page) => document.addPage(page));
        }

        bytes.value = await document.save();
        notice.value = `Merged ${files.length} file${files.length === 1 ? '' : 's'} onto the end of this PDF.`;
        await reload();
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'Those PDFs could not be merged.';
    } finally {
        busy.value = '';
    }
}

async function splitCurrent() {
    if (!bytes.value) {
        return;
    }

    busy.value = 'Splitting…';

    try {
        const source = await PDFDocument.load(overlays.value.length ? await bake() : cloneBytes(bytes.value), { ignoreEncryption: true });
        const document = await PDFDocument.create();
        const [page] = await document.copyPages(source, [currentPage.value - 1]);
        document.addPage(page);
        downloadBytes(await document.save(), `-page-${currentPage.value}.pdf`);
        notice.value = `Downloaded page ${currentPage.value} as its own PDF.`;
        await reload();
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'The page could not be split out.';
    } finally {
        busy.value = '';
    }
}

async function rotatePage() {
    if (!bytes.value) {
        return;
    }

    const source = await PDFDocument.load(overlays.value.length ? await bake() : cloneBytes(bytes.value), { ignoreEncryption: true });
    const page = source.getPage(currentPage.value - 1);
    const current = page.getRotation().angle;
    page.setRotation(degrees((current + 90) % 360));
    bytes.value = await source.save();
    await reload();
}

async function deletePage() {
    if (!bytes.value || pageCount.value <= 1) {
        error.value = 'A PDF needs at least one page.';
        return;
    }

    const source = await PDFDocument.load(overlays.value.length ? await bake() : cloneBytes(bytes.value), { ignoreEncryption: true });
    const keep = source.getPageIndices().filter((index) => index !== currentPage.value - 1);
    const document = await PDFDocument.create();
    const copied = await document.copyPages(source, keep);
    copied.forEach((page) => document.addPage(page));
    bytes.value = await document.save();
    currentPage.value = Math.min(currentPage.value, keep.length);
    await reload();
}

async function compress() {
    if (!bytes.value) {
        return;
    }

    busy.value = 'Compressing…';

    try {
        await persistBake();
        const document = await PDFDocument.create();

        for (let index = 1; index <= pageCount.value; index += 1) {
            const rendered = await renderPageToUrl(index, 1.05, 'image/jpeg', 0.52);
            const image = await document.embedJpg(dataUrlToBytes(rendered.url));
            const page = document.addPage([image.width, image.height]);
            page.drawImage(image, { x: 0, y: 0, width: image.width, height: image.height });
        }

        const saved = await document.save();
        bytes.value = saved;
        downloadBytes(saved, '-compressed.pdf');
        notice.value = 'Compressed by redrawing each page as a JPEG. Text is no longer selectable in this copy.';
        await reload();
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'Compression failed.';
    } finally {
        busy.value = '';
    }
}

async function pageToPng() {
    if (!bytes.value) {
        return;
    }

    const rendered = await renderPageToUrl(currentPage.value, 1.6);
    const link = window.document.createElement('a');
    link.href = rendered.url;
    link.download = fileName.value.replace(/\.pdf$/i, '') + `-page-${currentPage.value}.png`;
    link.click();
    convertOpen.value = false;
}

async function imagesToPdf(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    (event.target as HTMLInputElement).value = '';
    convertOpen.value = false;

    if (files.length === 0) {
        return;
    }

    busy.value = 'Building PDF…';

    try {
        const document = await PDFDocument.create();

        for (const file of files) {
            const raw = new Uint8Array(await file.arrayBuffer());
            const png = file.type.includes('png') || file.name.toLowerCase().endsWith('.png');
            const image = png ? await document.embedPng(raw) : await document.embedJpg(raw);
            const maxW = 595.28;
            const maxH = 841.89;
            const scale = Math.min(maxW / image.width, maxH / image.height, 1);
            const width = image.width * scale;
            const height = image.height * scale;
            const page = document.addPage([maxW, maxH]);
            page.drawImage(image, { x: (maxW - width) / 2, y: (maxH - height) / 2, width, height });
        }

        await loadPdfBytes(await document.save(), 'images.pdf');
        notice.value = `Built a PDF from ${files.length} image${files.length === 1 ? '' : 's'}.`;
    } catch (caught) {
        error.value = caught instanceof Error ? caught.message : 'Those images could not be turned into a PDF.';
    } finally {
        busy.value = '';
    }
}

async function onPickImage(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    (event.target as HTMLInputElement).value = '';

    if (!file) {
        return;
    }

    const dataUrl = await new Promise<string>((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result));
        reader.onerror = () => reject(new Error('The image could not be read.'));
        reader.readAsDataURL(file);
    });

    const probe = new Image();
    await new Promise((resolve) => {
        probe.onload = resolve;
        probe.src = dataUrl;
    });
    const max = 180;
    const scale = Math.min(max / probe.width, max / probe.height, 1);
    imagePayload.value = {
        dataUrl,
        mime: file.type || 'image/jpeg',
        w: probe.width * scale,
        h: probe.height * scale,
    };
    tool.value = 'image';
    notice.value = 'Click the page to place the image.';
}

function onDrop(event: DragEvent) {
    event.preventDefault();
    const file = event.dataTransfer?.files?.[0];

    if (!file) {
        return;
    }

    void file.arrayBuffer().then((buffer) => loadPdfBytes(new Uint8Array(buffer), file.name));
}
</script>

<template>
    <div class="studio">
        <header class="topbar">
            <input ref="fileInput" type="file" accept="application/pdf,.pdf" class="hidden" @change="onOpen" />
            <input ref="mergeInput" type="file" accept="application/pdf,.pdf" multiple class="hidden" @change="mergePdfs" />
            <input ref="imageInput" type="file" accept="image/png,image/jpeg,.png,.jpg,.jpeg" class="hidden" @change="onPickImage" />
            <input ref="photosInput" type="file" accept="image/png,image/jpeg,.png,.jpg,.jpeg" multiple class="hidden" @change="imagesToPdf" />

            <nav class="actions" aria-label="PDF operations">
                <button type="button" class="action" @click="fileInput?.click()"><FileUp class="ico" /> Open</button>
                <button type="button" class="action" :disabled="!bytes" @click="mergeInput?.click()"><Layers class="ico" /> Merge PDF</button>
                <button type="button" class="action" :disabled="!bytes" @click="splitCurrent"><Scissors class="ico" /> Split PDF</button>
                <button type="button" class="action" :disabled="!bytes" @click="compress"><Minimize2 class="ico" /> Compress PDF</button>
                <div class="menu">
                    <button type="button" class="action" @click="convertOpen = !convertOpen">Convert PDF ▾</button>
                    <div v-if="convertOpen" class="dropdown" role="menu">
                        <button type="button" :disabled="!bytes" @click="pageToPng">This page to PNG</button>
                        <button type="button" @click="photosInput?.click()">Images to PDF</button>
                        <button type="button" @click="startBlank">Blank A4 PDF</button>
                    </div>
                </div>
            </nav>
        </header>

        <div class="toolbar" role="toolbar" aria-label="Edit tools">
            <button type="button" class="tool" :class="{ on: tool === 'select' }" title="Select" @click="tool = 'select'"><MousePointer2 /></button>
            <button type="button" class="tool" :class="{ on: tool === 'hand' }" title="Pan" @click="tool = 'hand'"><Hand /></button>
            <button type="button" class="tool" :class="{ on: tool === 'text' }" title="Text" @click="tool = 'text'"><Type /></button>
            <button type="button" class="tool" :class="{ on: tool === 'highlight' }" title="Highlight" @click="tool = 'highlight'"><Highlighter /></button>
            <button type="button" class="tool" :class="{ on: tool === 'draw' }" title="Draw" @click="tool = 'draw'"><Pencil /></button>
            <button type="button" class="tool" :class="{ on: tool === 'image' }" title="Image" @click="imageInput?.click()"><ImagePlus /></button>
            <span class="rule" />
            <label v-if="tool === 'text'" class="mini">Size <input v-model.number="fontSize" type="number" min="8" max="72" /></label>
            <label v-if="tool === 'text' || tool === 'draw'" class="mini">Color <input v-model="inkColor" type="color" /></label>
            <label v-if="tool === 'highlight'" class="mini">Color <input v-model="highlightColor" type="color" /></label>
            <input v-if="tool === 'text'" v-model="draft" class="draft" maxlength="400" placeholder="Text to place" />
            <span class="spacer" />
            <button type="button" class="tool" :disabled="!bytes" title="Rotate page" @click="rotatePage"><RotateCw /></button>
            <button type="button" class="tool" :disabled="!bytes || pageCount <= 1" title="Delete page" @click="deletePage"><Trash2 /></button>
            <Button size="sm" :disabled="!bytes || !!busy" @click="downloadEdited"><Download /> Download</Button>
        </div>

        <p class="banner">{{ notice }}</p>
        <p v-if="error" class="fail">{{ error }}</p>
        <p v-if="busy" class="meta">{{ busy }}</p>

        <div v-if="!bytes" class="drop" @dragover.prevent @drop="onDrop">
            <FilePlus class="big" />
            <p>Drop a PDF here, open a file, or start a blank page.</p>
            <div class="row">
                <Button type="button" @click="fileInput?.click()">Open PDF</Button>
                <Button type="button" variant="outline" @click="startBlank">Blank document</Button>
            </div>
        </div>

        <div v-else class="workspace">
            <aside class="thumbs" aria-label="Pages">
                <button
                    v-for="(thumb, index) in thumbs"
                    :key="index"
                    type="button"
                    class="thumb"
                    :class="{ current: index + 1 === currentPage }"
                    @click="goTo(index + 1)"
                >
                    <img v-if="thumb" :src="thumb" :alt="`Page ${index + 1}`" />
                    <span v-else class="ph">{{ index + 1 }}</span>
                    <span class="num">{{ index + 1 }}</span>
                </button>
            </aside>

            <section class="board" :class="{ pan: tool === 'hand' }">
                <div
                    v-if="previewUrl"
                    ref="stage"
                    class="stage"
                    :class="tool"
                    @mousedown="onStageDown"
                    @mousemove="onStageMove"
                    @mouseup="onStageUp"
                    @mouseleave="onStageUp"
                >
                    <img :src="previewUrl" class="page" :alt="`Page ${currentPage} of ${pageCount}`" :width="canvasWidth" :height="canvasHeight" />
                    <svg class="ink-layer" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path
                            v-for="item in inkMarks"
                            :key="item.id"
                            :d="inkPath(item)"
                            fill="none"
                            :stroke="item.color"
                            stroke-width="0.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            vector-effect="non-scaling-stroke"
                        />
                    </svg>
                    <button
                        v-for="item in highlightMarks"
                        :key="item.id"
                        type="button"
                        class="mark highlight"
                        :class="{ selected: item.id === selectedId }"
                        :style="{ ...overlayStyle(item), background: item.color }"
                        @mousedown="startDrag($event, item)"
                    />
                    <img
                        v-for="item in imageMarks"
                        :key="item.id"
                        :src="item.dataUrl"
                        class="mark picture"
                        :class="{ selected: item.id === selectedId }"
                        :style="overlayStyle(item)"
                        alt=""
                        @mousedown="startDrag($event, item)"
                    />
                    <textarea
                        v-for="item in textMarks"
                        :key="item.id"
                        v-model="item.text"
                        class="mark copy"
                        :class="{ selected: item.id === selectedId }"
                        :style="overlayStyle(item)"
                        @mousedown="startDrag($event, item)"
                    />
                </div>
                <p v-else-if="rendering" class="meta">Rendering page {{ currentPage }}…</p>
            </section>
        </div>

        <footer v-if="bytes" class="status">
            <button type="button" class="nav" :disabled="currentPage <= 1" @click="goTo(currentPage - 1)"><ChevronLeft /></button>
            <span>{{ currentPage }} / {{ pageCount }}</span>
            <button type="button" class="nav" :disabled="currentPage >= pageCount" @click="goTo(currentPage + 1)"><ChevronRight /></button>
            <span class="rule" />
            <button type="button" class="nav" @click="setZoom(zoom - 15)"><ZoomOut /></button>
            <span>{{ zoomLabel }}</span>
            <button type="button" class="nav" @click="setZoom(zoom + 15)"><ZoomIn /></button>
            <button type="button" class="nav" title="Fit" @click="setZoom(100)"><Maximize2 /></button>
            <span class="spacer" />
            <button v-if="selectedId" type="button" class="nav danger" @click="removeSelected">Remove</button>
            <span class="meta">{{ fileName }}</span>
        </footer>
    </div>
</template>

<style scoped>
.studio {
    @apply overflow-hidden rounded-xl border border-slate-200 bg-slate-50 text-slate-800 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100;
}

.topbar {
    @apply border-b border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-900;
}

.actions {
    @apply flex flex-wrap items-center gap-1;
}

.action,
.tool,
.nav {
    @apply inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 disabled:opacity-40 dark:text-slate-200 dark:hover:bg-slate-800;
}

.action .ico,
.tool :deep(svg),
.nav :deep(svg) {
    @apply h-4 w-4;
}

.toolbar {
    @apply flex flex-wrap items-center gap-1 border-b border-slate-200 bg-white px-3 py-1.5 dark:border-slate-800 dark:bg-slate-900;
}

.tool.on {
    @apply bg-slate-900 text-white dark:bg-blue-600;
}

.rule {
    @apply mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700;
}

.mini {
    @apply flex items-center gap-1 text-xs text-slate-500;
}

.mini input[type='number'] {
    @apply w-14 rounded border px-1 py-0.5;
}

.mini input[type='color'] {
    @apply h-7 w-8 cursor-pointer rounded border;
}

.draft {
    @apply w-48 rounded border px-2 py-1 text-sm;
}

.spacer {
    @apply flex-1;
}

.banner,
.meta {
    @apply px-4 py-2 text-xs leading-5 text-slate-500;
}

.fail {
    @apply px-4 text-sm text-red-700 dark:text-red-300;
}

.drop {
    @apply m-6 flex min-h-[22rem] flex-col items-center justify-center gap-4 rounded-xl border-2 border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900;
}

.big {
    @apply h-10 w-10 text-slate-400;
}

.row {
    @apply flex flex-wrap gap-2;
}

.workspace {
    @apply grid min-h-[36rem] grid-cols-1 bg-slate-200 dark:bg-slate-900 lg:grid-cols-[7.5rem_1fr];
}

.thumbs {
    @apply flex gap-2 overflow-x-auto border-b border-slate-200 bg-slate-100 p-2 lg:flex-col lg:overflow-y-auto lg:border-b-0 lg:border-r dark:border-slate-800 dark:bg-slate-950;
}

.thumb {
    @apply flex w-20 shrink-0 flex-col items-center gap-1 rounded-md p-1 hover:bg-white dark:hover:bg-slate-900;
}

.thumb.current {
    @apply bg-white ring-2 ring-blue-600 dark:bg-slate-900;
}

.thumb img,
.ph {
    @apply h-24 w-full rounded bg-white object-contain shadow-sm;
}

.ph {
    @apply flex items-center justify-center text-xs text-slate-400;
}

.num {
    @apply text-[11px] text-slate-500;
}

.board {
    @apply overflow-auto p-6;
}

.board.pan {
    @apply cursor-grab;
}

.stage {
    @apply relative mx-auto w-fit bg-white shadow-xl;
}

.stage.text,
.stage.image {
    @apply cursor-crosshair;
}

.stage.draw,
.stage.highlight {
    @apply cursor-crosshair;
}

.page {
    @apply block max-w-full select-none;
}

.ink-layer {
    @apply pointer-events-none absolute inset-0 h-full w-full;
}

.mark {
    @apply absolute;
}

.highlight {
    @apply opacity-50;
}

.copy {
    @apply -translate-y-1/2 resize-none border border-blue-400 bg-white/90 px-1 py-0.5 leading-snug;
}

.picture {
    @apply object-contain;
}

.selected {
    @apply ring-2 ring-blue-600;
}

.menu {
    @apply relative;
}

.dropdown {
    @apply absolute left-0 z-20 mt-1 w-48 rounded-md border bg-white p-1 shadow-lg dark:border-slate-700 dark:bg-slate-900;
}

.dropdown button {
    @apply block w-full rounded px-2 py-1.5 text-left text-sm hover:bg-slate-100 disabled:opacity-40 dark:hover:bg-slate-800;
}

.status {
    @apply flex flex-wrap items-center gap-2 border-t border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900;
}

.danger {
    @apply text-red-700;
}

.hidden {
    @apply sr-only;
}
</style>
