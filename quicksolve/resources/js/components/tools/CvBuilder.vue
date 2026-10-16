<script setup lang="ts">
import ErrorState from '@/components/marketing/ErrorState.vue';
import FormField from '@/components/marketing/FormField.vue';
import CvPreview from '@/components/tools/CvPreview.vue';
import type { CvDraft, CvEducation, CvExperience } from '@/components/tools/CvPreview.vue';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { reactive, ref } from 'vue';

const props = defineProps<{
    usage: { used: number; limit: number; remaining: number; window: string; plan: string } | null;
    demoMode: boolean;
}>();

const templates = [
    { id: 'classic', name: 'Classic' },
    { id: 'modern', name: 'Modern sidebar' },
    { id: 'compact', name: 'Compact' },
    { id: 'executive', name: 'Executive' },
    { id: 'timeline', name: 'Timeline' },
    { id: 'creative', name: 'Creative band' },
    { id: 'split', name: 'Split rail' },
    { id: 'banner', name: 'Banner' },
    { id: 'mosaic', name: 'Mosaic' },
    { id: 'atlantic', name: 'Navy sidebar' },
    { id: 'portrait', name: 'Photo header' },
    { id: 'mono', name: 'Black contrast' },
    { id: 'academic', name: 'Centered serif' },
    { id: 'sand', name: 'Warm beige' },
    { id: 'rose', name: 'Maroon sidebar' },
    { id: 'slate', name: 'Dark masthead' },
    { id: 'forest', name: 'Green sidebar' },
] as const;

const cv = reactive<CvDraft>({
    template: 'atlantic',
    full_name: 'Ada North',
    headline: 'Product designer',
    email: 'ada@example.com',
    phone: '+372 5555 0100',
    location: 'Tallinn',
    website: 'adanorth.example',
    summary: 'Designer who ships clear interfaces for small teams. Comfortable from research through handoff.',
    photo: '',
    skills: ['Figma', 'User research', 'HTML/CSS'],
    experience: [
        {
            role: 'Lead designer',
            company: 'North Studio',
            dates: '2022 — present',
            details: 'Ran product design for a catalog of business tools. Set type, spacing, and review habits.',
        },
    ] as CvExperience[],
    education: [{ school: 'Estonian Academy of Arts', credential: 'BA Graphic design', dates: '2016 — 2020' }] as CvEducation[],
});

const error = ref('');
const downloading = ref(false);
const panel = ref<'edit' | 'json' | 'chat'>('edit');
const jsonPaste = ref('');
const registryUser = ref('');
const jsonBusy = ref('');
const chatBusy = ref(false);
const instruction = ref('');
const usageState = ref(props.usage);
const chat = ref<Array<{ role: 'user' | 'assistant'; content: string }>>([]);
const suggestion = ref<{
    mode: string;
    notice: string | null;
    reply: string;
    patch: Partial<CvDraft> | null;
} | null>(null);

function addSkill() {
    cv.skills.push('');
}

function addExperience() {
    cv.experience.push({ role: '', company: '', dates: '', details: '' });
}

function addEducation() {
    cv.education.push({ school: '', credential: '', dates: '' });
}

function csrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    const cookie = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');

    return meta?.content || cookie;
}

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Could not read that photo.'));
        image.src = src;
    });
}

async function onPhoto(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    error.value = '';

    if (!file) {
        return;
    }

    if (!/^image\/(jpeg|png|webp)$/i.test(file.type) || file.size > 2 * 1024 * 1024) {
        error.value = 'Use a JPEG, PNG, or WebP photo under 2 MB. Initials are used if you skip this.';
        input.value = '';

        return;
    }

    const objectUrl = URL.createObjectURL(file);

    try {
        const image = await loadImage(objectUrl);
        const canvas = document.createElement('canvas');
        const size = 240;
        canvas.width = size;
        canvas.height = size;
        const context = canvas.getContext('2d');

        if (!context) {
            throw new Error('Could not prepare that photo.');
        }

        const scale = Math.max(size / image.width, size / image.height);
        const width = image.width * scale;
        const height = image.height * scale;
        context.drawImage(image, (size - width) / 2, (size - height) / 2, width, height);

        let quality = 0.82;
        let dataUrl = canvas.toDataURL('image/jpeg', quality);

        while (dataUrl.length > 110000 && quality > 0.45) {
            quality -= 0.12;
            dataUrl = canvas.toDataURL('image/jpeg', quality);
        }

        if (dataUrl.length > 110000) {
            error.value = 'That photo is too detailed after resize. Try a simpler crop.';

            return;
        }

        cv.photo = dataUrl;
    } catch {
        error.value = 'That photo could not be read. Try a JPEG or PNG.';
    } finally {
        URL.revokeObjectURL(objectUrl);
        input.value = '';
    }
}

function clearPhoto() {
    cv.photo = '';
}

async function downloadPdf() {
    error.value = '';
    downloading.value = true;

    try {
        const payload: Record<string, unknown> = {
            ...cv,
            skills: cv.skills.map((skill) => skill.trim()).filter(Boolean),
            experience: cv.experience.filter((job) => job.role.trim() !== ''),
            education: cv.education.filter((item) => item.school.trim() !== ''),
        };

        if (!cv.photo) {
            delete payload.photo;
        }

        const response = await fetch('/tools/cv-builder/pdf', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/pdf',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-XSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        if (response.status === 403) {
            error.value = 'The five free Pro uses are finished. Subscribe to keep downloading CVs.';
            return;
        }

        if (!response.ok) {
            error.value = 'The CV could not be exported. Check the required name field and try again.';
            return;
        }

        const blob = await response.blob();
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'cv.pdf';
        link.click();
        URL.revokeObjectURL(link.href);
    } finally {
        downloading.value = false;
    }
}

function cvPayload() {
    return {
        template: cv.template,
        full_name: cv.full_name,
        headline: cv.headline,
        email: cv.email,
        phone: cv.phone,
        location: cv.location,
        website: cv.website,
        summary: cv.summary,
        skills: cv.skills.map((skill) => skill.trim()).filter(Boolean),
        experience: cv.experience.filter((job) => job.role.trim() !== ''),
        education: cv.education.filter((item) => item.school.trim() !== ''),
    };
}

function applyMapped(mapped: Partial<CvDraft>) {
    cv.full_name = mapped.full_name ?? '';
    cv.headline = mapped.headline ?? '';
    cv.email = mapped.email ?? '';
    cv.phone = mapped.phone ?? '';
    cv.location = mapped.location ?? '';
    cv.website = mapped.website ?? '';
    cv.summary = mapped.summary ?? '';
    cv.skills = mapped.skills?.length ? [...mapped.skills] : [];
    cv.experience = mapped.experience?.length ? mapped.experience.map((job) => ({ ...job })) : [];
    cv.education = mapped.education?.length ? mapped.education.map((item) => ({ ...item })) : [];
}

async function postToolJson<T>(url: string, body: unknown, accept = 'application/json'): Promise<Response> {
    return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: accept,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-XSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });
}

async function importResume(resume: unknown) {
    error.value = '';
    jsonBusy.value = 'import';

    try {
        const response = await postToolJson('/tools/cv-builder/json-resume/import', {
            template: cv.template,
            resume,
        });
        const payload = (await response.json().catch(() => null)) as { data?: CvDraft; message?: string } | null;

        if (response.status === 403) {
            error.value = 'The five free Pro uses are finished. Subscribe to keep using CV import.';
            return;
        }

        if (!response.ok || !payload?.data) {
            error.value = payload?.message || 'That file is not a JSON Resume object we can map.';
            return;
        }

        applyMapped(payload.data);
        panel.value = 'edit';
    } finally {
        jsonBusy.value = '';
    }
}

async function importPasted() {
    try {
        await importResume(JSON.parse(jsonPaste.value));
    } catch {
        error.value = 'Paste valid JSON from a resume.json file.';
    }
}

async function importFile(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    try {
        await importResume(JSON.parse(await file.text()));
    } catch {
        error.value = 'That file is not valid JSON.';
    } finally {
        input.value = '';
    }
}

async function importRegistry() {
    const username = registryUser.value.trim();

    if (username === '') {
        return;
    }

    error.value = '';
    jsonBusy.value = 'registry';

    try {
        const response = await postToolJson('/tools/cv-builder/json-resume/registry', {
            template: cv.template,
            username,
        });
        const payload = (await response.json().catch(() => null)) as { data?: CvDraft; message?: string } | null;

        if (!response.ok || !payload?.data) {
            error.value = payload?.message || 'The JSON Resume registry could not be read.';
            return;
        }

        applyMapped(payload.data);
        panel.value = 'edit';
    } finally {
        jsonBusy.value = '';
    }
}

async function exportJsonResume() {
    error.value = '';
    jsonBusy.value = 'export';

    try {
        const response = await postToolJson('/tools/cv-builder/json-resume/export', cvPayload());

        if (response.status === 403) {
            error.value = 'The five free Pro uses are finished. Subscribe to keep exporting JSON Resume files.';
            return;
        }

        if (!response.ok) {
            error.value = 'The JSON Resume file could not be exported. Check the required name field.';
            return;
        }

        const blob = await response.blob();
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'resume.json';
        link.click();
        URL.revokeObjectURL(link.href);
    } finally {
        jsonBusy.value = '';
    }
}

function applySuggestion() {
    if (!suggestion.value?.patch) {
        return;
    }

    const patch = suggestion.value.patch;

    if (patch.headline !== undefined) {
        cv.headline = patch.headline;
    }

    if (patch.summary !== undefined) {
        cv.summary = patch.summary;
    }

    if (patch.skills) {
        cv.skills = [...patch.skills];
    }

    if (patch.experience) {
        cv.experience = patch.experience.map((job) => ({ ...job }));
    }

    if (patch.education) {
        cv.education = patch.education.map((item) => ({ ...item }));
    }

    panel.value = 'edit';
}

async function sendChat() {
    const message = instruction.value.trim();

    if (message === '') {
        return;
    }

    chatBusy.value = true;
    error.value = '';

    const response = await postJson<{
        mode: string;
        notice: string | null;
        reply: string;
        patch: Partial<CvDraft> | null;
        usage: { used: number; limit: number; remaining: number; window: string; plan: string };
    }>('/api/v1/tools/cv-builder/generate', {
        instruction: message,
        cv: cvPayload(),
        history: chat.value.slice(-8),
    });

    chatBusy.value = false;

    if (response.status === 429) {
        error.value = response.message;
        return;
    }

    if (!response.ok || !response.data) {
        error.value = response.message;
        return;
    }

    chat.value.push({ role: 'user', content: message }, { role: 'assistant', content: response.data.reply });
    suggestion.value = response.data;
    usageState.value = response.data.usage;
    instruction.value = '';
}
</script>

<template>
    <div class="layout">
        <div class="toolbar">
            <Button type="button" :variant="panel === 'edit' ? 'default' : 'outline'" @click="panel = 'edit'">CV</Button>
            <Button type="button" :variant="panel === 'json' ? 'default' : 'outline'" @click="panel = 'json'">JSON Resume</Button>
            <Button type="button" :variant="panel === 'chat' ? 'default' : 'outline'" @click="panel = 'chat'">DeepSeek chat</Button>
        </div>
        <fieldset v-if="panel !== 'json'" class="gallery-wrap">
            <legend class="label">Template</legend>
            <div class="gallery">
                <button
                    v-for="item in templates"
                    :key="item.id"
                    type="button"
                    class="thumb"
                    :class="{ on: cv.template === item.id }"
                    :aria-pressed="cv.template === item.id"
                    @click="cv.template = item.id"
                >
                    <span class="shot" :class="item.id" aria-hidden="true">
                        <span class="s" />
                        <span class="p" />
                        <span class="h" />
                        <span class="l" />
                        <span class="l2" />
                        <span class="l3" />
                    </span>
                    <span class="thumb-name">{{ item.name }}</span>
                </button>
            </div>
        </fieldset>
        <form v-if="panel === 'edit'" class="panel" @submit.prevent="downloadPdf">
            <FormField label="Full name"><input v-model="cv.full_name" class="field" /></FormField>
            <FormField label="Headline"><input v-model="cv.headline" class="field" /></FormField>
            <FormField
                label="Photo"
                hint="Optional JPEG, PNG, or WebP under 2 MB. Cropped to a circle in the browser. Initials show if you skip it. HEIC is not supported."
            >
                <div class="photo-row">
                    <input class="field" type="file" accept="image/jpeg,image/png,image/webp" @change="onPhoto" />
                    <Button v-if="cv.photo" type="button" variant="ghost" @click="clearPhoto">Remove</Button>
                </div>
            </FormField>
            <FormField label="Email"><input v-model="cv.email" class="field" type="email" /></FormField>
            <FormField label="Phone"><input v-model="cv.phone" class="field" /></FormField>
            <FormField label="Location"><input v-model="cv.location" class="field" /></FormField>
            <FormField label="Website"><input v-model="cv.website" class="field" /></FormField>
            <FormField label="Summary"><textarea v-model="cv.summary" class="field" rows="4" /></FormField>
            <p class="label">Skills</p>
            <div v-for="(_, index) in cv.skills" :key="'s' + index" class="row">
                <input v-model="cv.skills[index]" class="field" />
                <Button type="button" variant="ghost" @click="cv.skills.splice(index, 1)">Remove</Button>
            </div>
            <Button type="button" variant="outline" @click="addSkill">Add skill</Button>
            <p class="label">Experience</p>
            <div v-for="(job, index) in cv.experience" :key="'j' + index" class="group">
                <input v-model="job.role" class="field" placeholder="Role" />
                <input v-model="job.company" class="field" placeholder="Company" />
                <input v-model="job.dates" class="field" placeholder="Dates" />
                <textarea v-model="job.details" class="field" rows="3" placeholder="What you did" />
                <Button type="button" variant="ghost" @click="cv.experience.splice(index, 1)">Remove role</Button>
            </div>
            <Button type="button" variant="outline" @click="addExperience">Add experience</Button>
            <p class="label">Education</p>
            <div v-for="(item, index) in cv.education" :key="'e' + index" class="group">
                <input v-model="item.school" class="field" placeholder="School" />
                <input v-model="item.credential" class="field" placeholder="Credential" />
                <input v-model="item.dates" class="field" placeholder="Dates" />
                <Button type="button" variant="ghost" @click="cv.education.splice(index, 1)">Remove</Button>
            </div>
            <Button type="button" variant="outline" @click="addEducation">Add education</Button>
            <p v-if="error" class="error">{{ error }}</p>
            <Button type="submit" :disabled="downloading">{{ downloading ? 'Preparing PDF…' : 'Download PDF' }}</Button>
        </form>
        <div v-else-if="panel === 'json'" class="panel">
            <p class="meta">
                JSON Resume is an open <code>resume.json</code> format.
                This tool maps name, headline, contact, summary, work, education, and skills.
                Awards, volunteer, projects, languages, and remote photo URLs are skipped.
                QuickSolve does not publish to the registry.
            </p>
            <FormField label="Upload resume.json">
                <input class="field" type="file" accept="application/json,.json" @change="importFile" />
            </FormField>
            <FormField label="Paste JSON">
                <textarea v-model="jsonPaste" class="field" rows="8" placeholder='{"basics":{"name":"Ada North"}}' />
            </FormField>
            <Button type="button" variant="outline" :disabled="jsonBusy !== ''" @click="importPasted">
                {{ jsonBusy === 'import' ? 'Importing…' : 'Import pasted JSON' }}
            </Button>
            <FormField
                label="Registry username"
                hint="Loads https://registry.jsonresume.org/your-github-username.json. The username must already have a public resume.json gist."
            >
                <input v-model="registryUser" class="field" placeholder="thomasdavis" />
            </FormField>
            <div class="row">
                <Button type="button" variant="outline" :disabled="jsonBusy !== ''" @click="importRegistry">
                    {{ jsonBusy === 'registry' ? 'Loading…' : 'Load from registry' }}
                </Button>
                <Button type="button" :disabled="jsonBusy !== ''" @click="exportJsonResume">
                    {{ jsonBusy === 'export' ? 'Preparing…' : 'Download resume.json' }}
                </Button>
            </div>
            <p v-if="error && panel === 'json'" class="error">{{ error }}</p>
        </div>
        <div v-else class="panel">
            <p v-if="demoMode" class="demo">
                Demo mode is on because no DeepSeek API key is configured. Replies are assembled from the form and labeled as a demo.
            </p>
            <p v-else class="live">Requests are sent to DeepSeek on the server. A failed request stays an error and is not replaced with demo text.</p>
            <p v-if="usageState" class="meta">{{ usageState.remaining }} of {{ usageState.limit }} generations left this {{ usageState.window }}.</p>
            <div class="chat" aria-live="polite">
                <p v-if="chat.length === 0" class="meta">Ask to tighten the summary or rephrase bullets already on the form. The model should not invent jobs or dates.</p>
                <article v-for="(turn, index) in chat" :key="index" class="turn" :class="turn.role">
                    <p class="label">{{ turn.role === 'user' ? 'You' : 'Reply' }}</p>
                    <pre class="body">{{ turn.content }}</pre>
                </article>
            </div>
            <FormField label="Message">
                <textarea v-model="instruction" class="field" rows="3" placeholder="Tighten the summary using only the facts already in the form" />
            </FormField>
            <ErrorState v-if="error && panel === 'chat'" :message="error" />
            <div class="row">
                <Button type="button" :disabled="chatBusy" @click="sendChat">{{ chatBusy ? 'Working…' : 'Send to DeepSeek' }}</Button>
                <Button type="button" variant="outline" :disabled="!suggestion?.patch" @click="applySuggestion">Apply to form</Button>
            </div>
            <p v-if="suggestion?.mode === 'demo'" class="demo">{{ suggestion.notice }}</p>
            <p v-else-if="suggestion?.mode === 'live'" class="ok">Live DeepSeek response.</p>
        </div>
        <CvPreview :cv="cv" />
    </div>
</template>

<style scoped>
.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.toolbar,
.gallery-wrap {
    @apply min-w-0 lg:col-span-2;
}

.toolbar {
    @apply flex flex-wrap gap-2;
}

.meta {
    @apply text-sm text-slate-600 dark:text-slate-300;
}

.demo {
    @apply text-sm text-amber-800 dark:text-amber-200;
}

.live,
.ok {
    @apply text-sm text-emerald-800 dark:text-emerald-200;
}

.chat {
    @apply max-h-72 space-y-3 overflow-y-auto rounded-lg border p-3;
}

.turn .label {
    @apply text-xs font-medium uppercase tracking-wide text-slate-500;
}

.turn .body {
    @apply whitespace-pre-wrap text-sm;
}

.gallery {
    @apply grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6;
}

.thumb {
    @apply rounded-xl border bg-white p-2 text-left shadow-sm transition hover:border-sky-400 dark:border-slate-600 dark:bg-slate-900;
}

.thumb.on {
    @apply border-sky-600 ring-2 ring-sky-200 dark:ring-sky-800;
}

.thumb-name {
    @apply mt-1.5 block text-xs font-medium text-slate-700 dark:text-slate-200;
}

.shot {
    position: relative;
    display: block;
    height: 88px;
    overflow: hidden;
    border-radius: 8px;
    background: #fff;
    box-shadow: inset 0 0 0 1px #e2e8f0;
}

.shot .s,
.shot .p,
.shot .h,
.shot .l,
.shot .l2,
.shot .l3 {
    position: absolute;
}

.shot .p {
    border-radius: 999px;
    background: #cbd5e1;
}

.shot .l,
.shot .l2,
.shot .l3 {
    height: 4px;
    border-radius: 2px;
    background: #e2e8f0;
}

.shot.classic .h {
    top: 12px;
    right: 14px;
    left: 14px;
    height: 8px;
    background: #0f172a;
}

.shot.classic .l {
    top: 30px;
    right: 20px;
    left: 14px;
}

.shot.classic .l2 {
    top: 48px;
    right: 10px;
    left: 14px;
}

.shot.classic .l3 {
    top: 64px;
    right: 28px;
    left: 14px;
}

.shot.modern .s {
    inset: 0 auto 0 0;
    width: 34%;
    background: #0f172a;
}

.shot.modern .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 7px;
    background: #1d4ed8;
}

.shot.modern .l {
    top: 32px;
    right: 12px;
    left: 42%;
}

.shot.modern .l2 {
    top: 50px;
    right: 8px;
    left: 42%;
}

.shot.modern .l3 {
    top: 66px;
    right: 22px;
    left: 42%;
}

.shot.compact .h {
    top: 8px;
    right: 10px;
    left: 10px;
    height: 6px;
    background: #334155;
}

.shot.compact .l,
.shot.compact .l2,
.shot.compact .l3 {
    height: 3px;
}

.shot.compact .l {
    top: 24px;
    right: 10px;
    left: 10px;
}

.shot.compact .l2 {
    top: 40px;
    right: 10px;
    left: 10px;
}

.shot.compact .l3 {
    top: 56px;
    right: 10px;
    left: 10px;
}

.shot.executive .h {
    top: 14px;
    right: 22px;
    left: 22px;
    height: 7px;
    background: #0f172a;
}

.shot.executive .s {
    top: 28px;
    right: 32%;
    left: 32%;
    height: 2px;
    background: #b45309;
}

.shot.executive .l {
    top: 42px;
    right: 14px;
    left: 14px;
}

.shot.executive .l2 {
    top: 58px;
    right: 18px;
    left: 14px;
}

.shot.timeline .h {
    top: 10px;
    right: 12px;
    left: 12px;
    height: 7px;
    background: #0f172a;
}

.shot.timeline .s {
    top: 28px;
    bottom: 10px;
    left: 10px;
    width: 18%;
    background: #e2e8f0;
}

.shot.timeline .l {
    top: 32px;
    right: 12px;
    left: 36%;
}

.shot.timeline .l2 {
    top: 50px;
    right: 12px;
    left: 36%;
}

.shot.timeline .l3 {
    top: 68px;
    right: 20px;
    left: 36%;
}

.shot.creative .s {
    inset: 0 auto 0 0;
    width: 8px;
    background: #6d28d9;
}

.shot.creative .h {
    top: 12px;
    right: 16px;
    left: 18px;
    height: 10px;
    background: #6d28d9;
}

.shot.creative .l {
    top: 36px;
    right: 14px;
    left: 18px;
}

.shot.creative .l2 {
    top: 54px;
    right: 10px;
    left: 18px;
}

.shot.split .s {
    inset: 0 auto 0 0;
    width: 34%;
    background: #ccfbf1;
}

.shot.split .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 7px;
    background: #0f766e;
}

.shot.split .l {
    top: 32px;
    right: 12px;
    left: 42%;
}

.shot.split .l2 {
    top: 50px;
    right: 8px;
    left: 42%;
}

.shot.banner .h {
    top: 0;
    right: 0;
    left: 0;
    height: 28px;
    background: #0f766e;
}

.shot.banner .l {
    top: 40px;
    right: 12px;
    left: 10px;
}

.shot.banner .l2 {
    top: 56px;
    right: 16px;
    left: 10px;
}

.shot.banner .l3 {
    top: 72px;
    right: 28px;
    left: 10px;
}

.shot.mosaic .h {
    top: 10px;
    right: 36%;
    left: 10px;
    height: 7px;
    background: #0369a1;
}

.shot.mosaic .l {
    top: 32px;
    bottom: 10px;
    left: 10px;
    width: 38%;
    height: auto;
    background: #e0f2fe;
}

.shot.mosaic .l2 {
    top: 32px;
    right: 10px;
    left: 54%;
}

.shot.mosaic .l3 {
    top: 52px;
    right: 10px;
    left: 54%;
}

.shot.atlantic .s {
    inset: 0 auto 0 0;
    width: 34%;
    background: #1e3a5f;
}

.shot.atlantic .p {
    top: 8px;
    left: 9%;
    width: 16px;
    height: 16px;
    background: #94a3b8;
}

.shot.atlantic .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 8px;
    background: #1e3a5f;
}

.shot.atlantic .l {
    top: 32px;
    right: 12px;
    left: 42%;
}

.shot.atlantic .l2 {
    top: 50px;
    right: 8px;
    left: 42%;
}

.shot.atlantic .l3 {
    top: 68px;
    right: 20px;
    left: 42%;
}

.shot.portrait .p {
    top: 10px;
    left: 10px;
    width: 22px;
    height: 22px;
}

.shot.portrait .h {
    top: 12px;
    right: 12px;
    left: 40px;
    height: 8px;
    background: #0f172a;
}

.shot.portrait .l {
    top: 42px;
    bottom: 10px;
    left: 10px;
    width: 32%;
    height: auto;
    background: #e2e8f0;
}

.shot.portrait .l2 {
    top: 42px;
    right: 10px;
    left: 48%;
}

.shot.portrait .l3 {
    top: 60px;
    right: 10px;
    left: 48%;
}

.shot.mono .s {
    inset: 0 auto 0 0;
    width: 36%;
    background: #111827;
}

.shot.mono .p {
    top: 28px;
    left: 10%;
    width: 18px;
    height: 18px;
    background: #9ca3af;
}

.shot.mono .h {
    top: 16px;
    right: 10px;
    left: 44%;
    height: 16px;
    background: #111827;
}

.shot.mono .l {
    top: 44px;
    right: 14px;
    left: 44%;
}

.shot.mono .l2 {
    top: 62px;
    right: 10px;
    left: 44%;
}

.shot.academic .h {
    top: 12px;
    right: 18px;
    left: 18px;
    height: 8px;
    background: #0f172a;
}

.shot.academic .s {
    top: 28px;
    right: 22%;
    left: 22%;
    height: 2px;
    background: #334155;
}

.shot.academic .l,
.shot.academic .l2,
.shot.academic .l3 {
    top: 42px;
    width: 22%;
    height: 28px;
    background: #f1f5f9;
}

.shot.academic .l {
    left: 8%;
}

.shot.academic .l2 {
    left: 39%;
}

.shot.academic .l3 {
    left: 70%;
}

.shot.sand {
    background: #f5efe6;
}

.shot.sand .s {
    inset: 0 auto 0 0;
    width: 34%;
    background: #efe4d4;
}

.shot.sand .p {
    top: 8px;
    left: 9%;
    width: 16px;
    height: 16px;
    background: #d6bfa3;
}

.shot.sand .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 8px;
    background: #b45309;
}

.shot.sand .l {
    top: 34px;
    right: 12px;
    left: 42%;
}

.shot.sand .l2 {
    top: 52px;
    right: 8px;
    left: 42%;
}

.shot.rose .s {
    inset: 0 auto 0 0;
    width: 34%;
    background: #9f1239;
}

.shot.rose .p {
    top: 8px;
    left: 9%;
    width: 16px;
    height: 16px;
    background: #fecdd3;
}

.shot.rose .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 8px;
    background: #9f1239;
}

.shot.rose .l {
    top: 32px;
    right: 12px;
    left: 42%;
}

.shot.rose .l2 {
    top: 50px;
    right: 8px;
    left: 42%;
}

.shot.rose .l3 {
    top: 68px;
    right: 18px;
    left: 42%;
}

.shot.slate .h {
    top: 0;
    right: 0;
    left: 0;
    height: 30px;
    background: #1e293b;
}

.shot.slate .p {
    top: 7px;
    left: 8px;
    width: 16px;
    height: 16px;
    background: #94a3b8;
}

.shot.slate .l {
    top: 42px;
    bottom: 8px;
    left: 8px;
    width: 34%;
    height: auto;
    background: #e2e8f0;
}

.shot.slate .l2 {
    top: 42px;
    right: 8px;
    left: 48%;
}

.shot.slate .l3 {
    top: 60px;
    right: 8px;
    left: 48%;
}

.shot.forest .s {
    inset: 0 auto 0 0;
    width: 34%;
    background: #3f6212;
}

.shot.forest .p {
    top: 8px;
    left: 9%;
    width: 16px;
    height: 16px;
    background: #d9f99d;
}

.shot.forest .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 8px;
    background: #3f6212;
}

.shot.forest .l {
    top: 32px;
    right: 12px;
    left: 42%;
}

.shot.forest .l2 {
    top: 50px;
    right: 8px;
    left: 42%;
}

.shot.forest .l3 {
    top: 68px;
    right: 20px;
    left: 42%;
}

.panel {
    @apply space-y-3 rounded-xl border p-5;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.photo-row {
    @apply flex items-center gap-2;
}

.label {
    @apply mt-2 text-sm font-medium;
}

.row {
    @apply flex gap-2;
}

.group {
    @apply space-y-2 rounded-lg border p-3;
}

.error {
    @apply text-sm text-red-700;
}
</style>
