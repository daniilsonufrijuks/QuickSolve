<script setup lang="ts">
import { computed } from 'vue';
import CvAvatar from '@/components/tools/CvAvatar.vue';
import CvThemedPreview from '@/components/tools/CvThemedPreview.vue';
import type { CvTheme } from '@/lib/cvTheme';

export type CvExperience = { role: string; company: string; dates: string; details: string };
export type CvEducation = { school: string; credential: string; dates: string };

export type CvDraft = {
    template: string;
    full_name: string;
    headline: string;
    email: string;
    phone: string;
    location: string;
    website: string;
    summary: string;
    photo: string;
    skills: string[];
    experience: CvExperience[];
    education: CvEducation[];
    theme?: CvTheme | null;
};

const props = defineProps<{ cv: CvDraft; theme?: CvTheme | null }>();

const contactLine = computed(() => [props.cv.email, props.cv.phone, props.cv.location, props.cv.website].filter(Boolean).join(' · '));
const contactLines = computed(() => [props.cv.email, props.cv.phone, props.cv.location, props.cv.website].filter(Boolean));
const skillList = computed(() => props.cv.skills.map((skill) => skill.trim()).filter(Boolean));
const initials = computed(() =>
    props.cv.full_name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join(''),
);

const isSidebar = computed(() => ['atlantic', 'rose', 'forest'].includes(props.cv.template));
</script>

<template>
    <CvThemedPreview v-if="theme" :cv="cv" :theme="theme" />
    <section v-else class="preview" :class="cv.template" aria-label="CV preview">
        <div v-if="cv.template === 'modern'" class="modern-grid">
            <aside class="side">
                <CvAvatar :src="cv.photo" :initials="initials" />
                <h2>{{ cv.full_name }}</h2>
                <p>{{ cv.headline }}</p>
                <p class="heading">Contact</p>
                <p v-for="line in contactLines" :key="line" class="small">{{ line }}</p>
                <p class="heading">Skills</p>
                <p class="small">{{ skillList.join(', ') }}</p>
            </aside>
            <div>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'m-job-' + index" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'m-ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
            </div>
        </div>
        <div v-else-if="cv.template === 'split'" class="modern-grid">
            <aside class="rail">
                <CvAvatar :src="cv.photo" :initials="initials" />
                <h2>{{ cv.full_name }}</h2>
                <p>{{ cv.headline }}</p>
                <p class="heading">Contact</p>
                <p v-for="line in contactLines" :key="line" class="small">{{ line }}</p>
                <p class="heading">Skills</p>
                <p class="small">{{ skillList.join(', ') }}</p>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'s-ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
            </aside>
            <div>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'s-job-' + index" class="item">
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
                <p class="small">{{ contactLine }}</p>
            </header>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <p class="heading">Experience</p>
            <article v-for="(job, index) in cv.experience" :key="'b-job-' + index" class="item">
                <p class="strong">{{ job.role }} · {{ job.company }}</p>
                <p class="small">{{ job.dates }}</p>
                <p class="body">{{ job.details }}</p>
            </article>
            <p class="heading">Education</p>
            <article v-for="(item, index) in cv.education" :key="'b-ed-' + index" class="item">
                <p class="strong">{{ item.school }}</p>
                <p class="small">{{ item.credential }} {{ item.dates }}</p>
            </article>
            <p class="heading">Skills</p>
            <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
        </div>
        <div v-else-if="cv.template === 'mosaic'" class="mosaic">
            <h2>{{ cv.full_name }}</h2>
            <p class="small">{{ cv.headline }}</p>
            <p class="small">{{ contactLine }}</p>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <div class="mosaic-cols">
                <div>
                    <p class="heading">Education</p>
                    <article v-for="(item, index) in cv.education" :key="'z-ed-' + index" class="item">
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }} {{ item.dates }}</p>
                    </article>
                    <p class="heading">Skills</p>
                    <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                </div>
                <div>
                    <p class="heading">Experience</p>
                    <article v-for="(job, index) in cv.experience" :key="'z-job-' + index" class="item">
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
            <p class="small">{{ contactLine }}</p>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <p class="heading">Experience</p>
            <article v-for="(job, index) in cv.experience" :key="'t-job-' + index" class="time-row">
                <p class="when">{{ job.dates }}</p>
                <div>
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="body">{{ job.details }}</p>
                </div>
            </article>
            <p class="heading">Education</p>
            <article v-for="(item, index) in cv.education" :key="'t-ed-' + index" class="time-row">
                <p class="when">{{ item.dates }}</p>
                <div>
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }}</p>
                </div>
            </article>
            <p class="heading">Skills</p>
            <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
        </div>
        <div v-else-if="isSidebar" class="modern-grid">
            <aside class="panel-side">
                <CvAvatar :src="cv.photo" :initials="initials" size="lg" />
                <p class="heading">Contact</p>
                <p v-for="line in contactLines" :key="line" class="small">{{ line }}</p>
                <p class="heading">Skills</p>
                <p class="chips light"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'side-ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
            </aside>
            <div>
                <h2 class="display">{{ cv.full_name }}</h2>
                <p class="small">{{ cv.headline }}</p>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'side-job-' + index" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
            </div>
        </div>
        <div v-else-if="cv.template === 'portrait'">
            <header class="portrait-head">
                <CvAvatar :src="cv.photo" :initials="initials" size="lg" />
                <div>
                    <h2 class="display">{{ cv.full_name }}</h2>
                    <p class="small">{{ cv.headline }}</p>
                    <p class="small">{{ contactLine }}</p>
                </div>
            </header>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <div class="mosaic-cols">
                <div>
                    <p class="heading">Education</p>
                    <article v-for="(item, index) in cv.education" :key="'p-ed-' + index" class="item">
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }} {{ item.dates }}</p>
                    </article>
                    <p class="heading">Skills</p>
                    <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                </div>
                <div>
                    <p class="heading">Experience</p>
                    <article v-for="(job, index) in cv.experience" :key="'p-job-' + index" class="item">
                        <p class="strong">{{ job.role }} · {{ job.company }}</p>
                        <p class="small">{{ job.dates }}</p>
                        <p class="body">{{ job.details }}</p>
                    </article>
                </div>
            </div>
        </div>
        <div v-else-if="cv.template === 'mono'">
            <div class="mono-head">
                <aside class="ink">
                    <CvAvatar :src="cv.photo" :initials="initials" size="lg" />
                </aside>
                <div>
                    <h2 class="mega">{{ cv.full_name }}</h2>
                    <p class="small">{{ cv.headline }}</p>
                    <p class="small">{{ contactLine }}</p>
                </div>
            </div>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <p class="heading">Experience</p>
            <article v-for="(job, index) in cv.experience" :key="'k-job-' + index" class="item">
                <p class="strong">{{ job.role }} · {{ job.company }}</p>
                <p class="small">{{ job.dates }}</p>
                <p class="body">{{ job.details }}</p>
            </article>
            <p class="heading">Education</p>
            <article v-for="(item, index) in cv.education" :key="'k-ed-' + index" class="item">
                <p class="strong">{{ item.school }}</p>
                <p class="small">{{ item.credential }} {{ item.dates }}</p>
            </article>
            <p class="heading">Skills</p>
            <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
        </div>
        <div v-else-if="cv.template === 'academic'" class="academic">
            <h2 class="display">{{ cv.full_name }}</h2>
            <p class="small">{{ cv.headline }}</p>
            <div class="rule"></div>
            <div class="tri">
                <p>{{ cv.phone }}</p>
                <p>{{ cv.email }}</p>
                <p>{{ cv.location }}{{ cv.website ? ' · ' + cv.website : '' }}</p>
            </div>
            <div class="leftish">
                <p class="heading">Professional summary</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'a-job-' + index" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'a-ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
                <p class="heading">Skills</p>
                <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
            </div>
        </div>
        <div v-else-if="cv.template === 'sand'" class="modern-grid">
            <aside class="sand-side">
                <CvAvatar :src="cv.photo" :initials="initials" size="lg" />
                <p class="heading">Contact</p>
                <p v-for="line in contactLines" :key="line" class="small">{{ line }}</p>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'n-ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
                <p class="heading">Skills</p>
                <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
            </aside>
            <div>
                <h2 class="display">{{ cv.full_name }}</h2>
                <p class="small">{{ cv.headline }}</p>
                <p class="heading">About me</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'n-job-' + index" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
            </div>
        </div>
        <div v-else-if="cv.template === 'slate'">
            <header class="slate-bar">
                <CvAvatar :src="cv.photo" :initials="initials" />
                <div>
                    <h2>{{ cv.full_name }}</h2>
                    <p>{{ cv.headline }}</p>
                    <p class="small">{{ contactLine }}</p>
                </div>
            </header>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <div class="mosaic-cols">
                <div>
                    <p class="heading">Education</p>
                    <article v-for="(item, index) in cv.education" :key="'l-ed-' + index" class="item">
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }} {{ item.dates }}</p>
                    </article>
                    <p class="heading">Skills</p>
                    <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                </div>
                <div>
                    <p class="heading">Experience</p>
                    <article v-for="(job, index) in cv.experience" :key="'l-job-' + index" class="item">
                        <p class="strong">{{ job.role }} · {{ job.company }}</p>
                        <p class="small">{{ job.dates }}</p>
                        <p class="body">{{ job.details }}</p>
                    </article>
                </div>
            </div>
        </div>
        <div v-else>
            <header :class="{ exec: cv.template === 'executive' }">
                <h2>{{ cv.full_name }}</h2>
                <p class="small">{{ cv.headline }}</p>
                <p class="small">{{ contactLine }}</p>
            </header>
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <p class="heading">Experience</p>
            <article v-for="(job, index) in cv.experience" :key="'c-job-' + index" class="item">
                <p class="strong">{{ job.role }} · {{ job.company }}</p>
                <p class="small">{{ job.dates }}</p>
                <p class="body">{{ job.details }}</p>
            </article>
            <p class="heading">Education</p>
            <article v-for="(item, index) in cv.education" :key="'c-ed-' + index" class="item">
                <p class="strong">{{ item.school }}</p>
                <p class="small">{{ item.credential }} {{ item.dates }}</p>
            </article>
            <p class="heading">Skills</p>
            <p class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
        </div>
    </section>
</template>

<style scoped>
.preview {
    @apply space-y-3 rounded-xl border bg-white p-5 text-slate-900;
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

.chips.light span {
    @apply border-white/40 text-white;
}

.modern-grid {
    @apply grid gap-4 md:grid-cols-[13rem_1fr];
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

.panel-side {
    @apply rounded-lg p-4 text-slate-50;
}

.preview.atlantic .panel-side {
    @apply bg-[#1e3a5f];
}

.preview.rose .panel-side {
    @apply bg-rose-800;
}

.preview.forest .panel-side {
    @apply bg-lime-800;
}

.panel-side .heading,
.panel-side .small,
.panel-side .strong {
    @apply border-white/20 text-slate-100;
}

.display {
    @apply text-3xl font-semibold uppercase tracking-wide;
}

.portrait-head {
    @apply mb-4 flex items-center gap-4;
}

.preview.portrait .display {
    @apply text-sky-900;
}

.mono-head {
    @apply mb-4 grid gap-4 md:grid-cols-[11rem_1fr];
}

.ink {
    @apply -ml-5 -mt-5 flex items-center justify-center bg-zinc-950 px-4 py-8;
}

.mega {
    @apply text-4xl font-bold uppercase leading-none tracking-tight;
}

.academic {
    @apply text-center;
}

.academic .display {
    @apply tracking-[0.18em];
}

.rule {
    @apply mx-auto my-3 h-px w-3/4 bg-slate-800;
}

.tri {
    @apply mb-4 grid gap-2 text-xs text-slate-600 sm:grid-cols-3;
}

.leftish {
    @apply text-left;
}

.preview.sand {
    @apply bg-[#f5efe6];
}

.preview.sand .heading {
    @apply border-amber-200 text-amber-800;
}

.sand-side {
    @apply p-1;
}

.slate-bar {
    @apply -mx-5 -mt-5 mb-4 flex items-center gap-4 bg-slate-800 px-5 py-4 text-slate-50;
}

.slate-bar .small {
    @apply text-slate-200;
}
</style>
