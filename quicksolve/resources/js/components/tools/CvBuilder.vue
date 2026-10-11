<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { reactive, ref } from 'vue';

type Experience = { role: string; company: string; dates: string; details: string };
type Education = { school: string; credential: string; dates: string };

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
] as const;

const cv = reactive({
    template: 'classic' as (typeof templates)[number]['id'],
    full_name: 'Ada North',
    headline: 'Product designer',
    email: 'ada@example.com',
    phone: '+372 5555 0100',
    location: 'Tallinn',
    website: 'adanorth.example',
    summary: 'Designer who ships clear interfaces for small teams. Comfortable from research through handoff.',
    skills: ['Figma', 'User research', 'HTML/CSS'],
    experience: [
        {
            role: 'Lead designer',
            company: 'North Studio',
            dates: '2022 — present',
            details: 'Ran product design for a catalog of business tools. Set type, spacing, and review habits.',
        },
    ] as Experience[],
    education: [{ school: 'Estonian Academy of Arts', credential: 'BA Graphic design', dates: '2016 — 2020' }] as Education[],
});

const error = ref('');
const downloading = ref(false);

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

async function downloadPdf() {
    error.value = '';
    downloading.value = true;

    try {
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
            body: JSON.stringify({
                ...cv,
                skills: cv.skills.map((skill) => skill.trim()).filter(Boolean),
                experience: cv.experience.filter((job) => job.role.trim() !== ''),
                education: cv.education.filter((item) => item.school.trim() !== ''),
            }),
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

const contactLine = () => [cv.email, cv.phone, cv.location, cv.website].filter(Boolean).join(' · ');
</script>

<template>
    <div class="layout">
        <form class="panel" @submit.prevent="downloadPdf">
            <FormField label="Template">
                <div class="templates">
                    <label v-for="item in templates" :key="item.id" class="choice">
                        <input v-model="cv.template" type="radio" :value="item.id" />
                        {{ item.name }}
                    </label>
                </div>
            </FormField>
            <FormField label="Full name"><input v-model="cv.full_name" class="field" /></FormField>
            <FormField label="Headline"><input v-model="cv.headline" class="field" /></FormField>
            <FormField label="Email"><input v-model="cv.email" class="field" type="email" /></FormField>
            <FormField label="Phone"><input v-model="cv.phone" class="field" /></FormField>
            <FormField label="Location"><input v-model="cv.location" class="field" /></FormField>
            <FormField label="Website"><input v-model="cv.website" class="field" /></FormField>
            <FormField label="Summary"><textarea v-model="cv.summary" class="field" rows="4" /></FormField>
            <p class="label">Skills</p>
            <div v-for="(_, index) in cv.skills" :key="'s'+index" class="row">
                <input v-model="cv.skills[index]" class="field" />
                <Button type="button" variant="ghost" @click="cv.skills.splice(index, 1)">Remove</Button>
            </div>
            <Button type="button" variant="outline" @click="addSkill">Add skill</Button>
            <p class="label">Experience</p>
            <div v-for="(job, index) in cv.experience" :key="'j'+index" class="group">
                <input v-model="job.role" class="field" placeholder="Role" />
                <input v-model="job.company" class="field" placeholder="Company" />
                <input v-model="job.dates" class="field" placeholder="Dates" />
                <textarea v-model="job.details" class="field" rows="3" placeholder="What you did" />
                <Button type="button" variant="ghost" @click="cv.experience.splice(index, 1)">Remove role</Button>
            </div>
            <Button type="button" variant="outline" @click="addExperience">Add experience</Button>
            <p class="label">Education</p>
            <div v-for="(item, index) in cv.education" :key="'e'+index" class="group">
                <input v-model="item.school" class="field" placeholder="School" />
                <input v-model="item.credential" class="field" placeholder="Credential" />
                <input v-model="item.dates" class="field" placeholder="Dates" />
                <Button type="button" variant="ghost" @click="cv.education.splice(index, 1)">Remove</Button>
            </div>
            <Button type="button" variant="outline" @click="addEducation">Add education</Button>
            <p v-if="error" class="error">{{ error }}</p>
            <Button type="submit" :disabled="downloading">{{ downloading ? 'Preparing PDF…' : 'Download PDF' }}</Button>
        </form>
        <section class="preview" :class="cv.template" aria-label="CV preview">
            <div v-if="cv.template === 'modern'" class="modern-grid">
                <aside class="side">
                    <h2>{{ cv.full_name }}</h2>
                    <p>{{ cv.headline }}</p>
                    <p class="small">{{ contactLine() }}</p>
                    <p class="heading">Skills</p>
                    <p class="small">{{ cv.skills.filter(Boolean).join(', ') }}</p>
                </aside>
                <div>
                    <p class="heading">Profile</p>
                    <p class="body">{{ cv.summary }}</p>
                    <p class="heading">Experience</p>
                    <article v-for="job in cv.experience" :key="job.role + job.company" class="item">
                        <p class="strong">{{ job.role }} · {{ job.company }}</p>
                        <p class="small">{{ job.dates }}</p>
                        <p class="body">{{ job.details }}</p>
                    </article>
                    <p class="heading">Education</p>
                    <article v-for="item in cv.education" :key="item.school" class="item">
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }} {{ item.dates }}</p>
                    </article>
                </div>
            </div>
            <div v-else-if="cv.template === 'split'" class="modern-grid">
                <aside class="rail">
                    <h2>{{ cv.full_name }}</h2>
                    <p>{{ cv.headline }}</p>
                    <p class="small">{{ contactLine() }}</p>
                    <p class="heading">Skills</p>
                    <p class="small">{{ cv.skills.filter(Boolean).join(', ') }}</p>
                    <p class="heading">Education</p>
                    <article v-for="item in cv.education" :key="item.school" class="item">
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }} {{ item.dates }}</p>
                    </article>
                </aside>
                <div>
                    <p class="heading">Profile</p>
                    <p class="body">{{ cv.summary }}</p>
                    <p class="heading">Experience</p>
                    <article v-for="job in cv.experience" :key="job.role + job.company" class="item">
                        <p class="strong">{{ job.role }} · {{ job.company }}</p>
                        <p class="small">{{ job.dates }}</p>
                        <p class="body">{{ job.details }}</p>
                    </article>
                </div>
            </div>
            <div v-else-if="cv.template === 'banner'">
                <header class="banner">
                    <h2>{{ cv.full_name }}</h2>
                    <p>{{ cv.headline }}</p>
                    <p class="small">{{ contactLine() }}</p>
                </header>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="job in cv.experience" :key="job.role + job.company" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
                <p class="heading">Education</p>
                <article v-for="item in cv.education" :key="item.school" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
                <p class="heading">Skills</p>
                <p class="chips"><span v-for="skill in cv.skills.filter(Boolean)" :key="skill">{{ skill }}</span></p>
            </div>
            <div v-else-if="cv.template === 'mosaic'" class="mosaic">
                <h2>{{ cv.full_name }}</h2>
                <p class="small">{{ cv.headline }}</p>
                <p class="small">{{ contactLine() }}</p>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <div class="mosaic-cols">
                    <div>
                        <p class="heading">Education</p>
                        <article v-for="item in cv.education" :key="item.school" class="item">
                            <p class="strong">{{ item.school }}</p>
                            <p class="small">{{ item.credential }} {{ item.dates }}</p>
                        </article>
                        <p class="heading">Skills</p>
                        <p class="chips"><span v-for="skill in cv.skills.filter(Boolean)" :key="skill">{{ skill }}</span></p>
                    </div>
                    <div>
                        <p class="heading">Experience</p>
                        <article v-for="job in cv.experience" :key="job.role + job.company" class="item">
                            <p class="strong">{{ job.role }} · {{ job.company }}</p>
                            <p class="small">{{ job.dates }}</p>
                            <p class="body">{{ job.details }}</p>
                        </article>
                    </div>
                </div>
            </div>
            <div v-else-if="cv.template === 'timeline'" class="timeline">
                <h2>{{ cv.full_name }}</h2>
                <p class="small">{{ cv.headline }}</p>
                <p class="small">{{ contactLine() }}</p>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="job in cv.experience" :key="job.role + job.company" class="time-row">
                    <p class="when">{{ job.dates }}</p>
                    <div>
                        <p class="strong">{{ job.role }} · {{ job.company }}</p>
                        <p class="body">{{ job.details }}</p>
                    </div>
                </article>
                <p class="heading">Education</p>
                <article v-for="item in cv.education" :key="item.school" class="time-row">
                    <p class="when">{{ item.dates }}</p>
                    <div>
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }}</p>
                    </div>
                </article>
                <p class="heading">Skills</p>
                <p class="chips"><span v-for="skill in cv.skills.filter(Boolean)" :key="skill">{{ skill }}</span></p>
            </div>
            <div v-else>
                <header :class="{ exec: cv.template === 'executive' }">
                    <h2>{{ cv.full_name }}</h2>
                    <p class="small">{{ cv.headline }}</p>
                    <p class="small">{{ contactLine() }}</p>
                </header>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="job in cv.experience" :key="job.role + job.company" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
                <p class="heading">Education</p>
                <article v-for="item in cv.education" :key="item.school" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
                <p class="heading">Skills</p>
                <p class="chips"><span v-for="skill in cv.skills.filter(Boolean)" :key="skill">{{ skill }}</span></p>
            </div>
        </section>
    </div>
</template>

<style scoped>
.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.panel,
.preview {
    @apply space-y-3 rounded-xl border p-5;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.templates {
    @apply flex flex-wrap gap-3 text-sm;
}

.choice {
    @apply flex items-center gap-2;
}

.label {
    @apply mt-2 text-sm font-medium;
}

.row,
.actions {
    @apply flex gap-2;
}

.group {
    @apply space-y-2 rounded-lg border p-3;
}

.error {
    @apply text-sm text-red-700;
}

.preview {
    @apply bg-white text-slate-900;
}

.preview.classic h2 {
    @apply font-serif text-2xl;
}

.preview.compact {
    @apply text-sm;
}

.preview.compact .item {
    @apply mb-2;
}

.preview.executive .exec {
    @apply mb-4 border-b-2 border-amber-700 pb-4 text-center;
}

.preview.executive .exec h2 {
    @apply text-xl font-semibold uppercase tracking-[0.2em];
}

.preview.creative {
    @apply border-l-8 border-violet-700;
}

.preview.creative h2 {
    @apply text-3xl text-violet-800;
}

.time-row {
    @apply mb-3 grid gap-3 sm:grid-cols-[7rem_1fr];
}

.when {
    @apply text-sm text-slate-600;
}

.heading {
    @apply mt-4 border-b pb-1 text-xs font-semibold uppercase tracking-wide;
}

.small {
    @apply text-sm text-slate-600;
}

.body {
    @apply whitespace-pre-wrap text-sm leading-6;
}

.strong {
    @apply font-medium;
}

.item {
    @apply mb-3;
}

.chips {
    @apply flex flex-wrap gap-2;
}

.chips span {
    @apply rounded border px-2 py-0.5 text-xs;
}

.modern-grid {
    @apply grid gap-4 md:grid-cols-[12rem_1fr];
}

.side {
    @apply rounded-lg bg-slate-900 p-4 text-slate-100;
}

.side .heading,
.side .small {
    @apply text-slate-200;
}

.rail {
    @apply rounded-lg bg-teal-50 p-4 text-teal-950;
}

.preview.split .heading {
    @apply border-teal-200 text-teal-800;
}

.banner {
    @apply -mx-5 -mt-5 mb-4 bg-teal-800 px-5 py-4 text-teal-50;
}

.banner .small {
    @apply text-teal-100;
}

.mosaic-cols {
    @apply grid gap-4 md:grid-cols-2;
}

.preview.mosaic h2 {
    @apply text-sky-800;
}
</style>
