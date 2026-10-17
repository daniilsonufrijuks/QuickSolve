<script setup lang="ts">
import CvAvatar from '@/components/tools/CvAvatar.vue';
import { sanitizeTheme, themeVars, type CvTheme } from '@/lib/cvTheme';
import { computed } from 'vue';

const props = defineProps<{
    cv: {
        full_name: string;
        headline: string;
        email: string;
        phone: string;
        location: string;
        website: string;
        summary: string;
        photo: string;
        skills: string[];
        experience: Array<{ role: string; company: string; dates: string; details: string }>;
        education: Array<{ school: string; credential: string; dates: string }>;
    };
    theme: CvTheme;
}>();

const theme = computed(() => sanitizeTheme(props.theme));
const style = computed(() => themeVars(theme.value));
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
const showPhoto = computed(() => theme.value.photo !== 'hidden');
const photoShape = computed(() => (theme.value.photo === 'rounded' ? 'rounded' : 'circle'));
const isSidebar = computed(() => theme.value.layout === 'sidebar_left' || theme.value.layout === 'sidebar_right');
</script>

<template>
    <section class="sheet" :class="[theme.layout, theme.name_size, theme.header_align]" :style="style" aria-label="CV preview">
        <div v-if="isSidebar" class="split" :class="{ reverse: theme.layout === 'sidebar_right' }">
            <aside class="side">
                <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" size="lg" :shape="photoShape" />
                <template v-if="theme.layout === 'sidebar_right'">
                    <h2>{{ cv.full_name }}</h2>
                    <p>{{ cv.headline }}</p>
                </template>
                <p class="heading">Contact</p>
                <p v-for="line in contactLines" :key="line" class="small">{{ line }}</p>
                <p class="heading">Skills</p>
                <p v-if="theme.skills === 'list'" class="small">{{ skillList.join(', ') }}</p>
                <p v-else class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
            </aside>
            <div class="main">
                <template v-if="theme.layout !== 'sidebar_right'">
                    <h2>{{ cv.full_name }}</h2>
                    <p class="small">{{ cv.headline }}</p>
                </template>
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'job-' + index" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
            </div>
        </div>
        <div v-else-if="theme.layout === 'header_band'">
            <header class="band">
                <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" :shape="photoShape" />
                <h2>{{ cv.full_name }}</h2>
                <p>{{ cv.headline }}</p>
                <p class="small">{{ contactLine }}</p>
            </header>
            <div class="pad">
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <div class="cols">
                    <div>
                        <p class="heading">Education</p>
                        <article v-for="(item, index) in cv.education" :key="'b-ed-' + index" class="item">
                            <p class="strong">{{ item.school }}</p>
                            <p class="small">{{ item.credential }} {{ item.dates }}</p>
                        </article>
                        <p class="heading">Skills</p>
                        <p v-if="theme.skills === 'list'" class="small">{{ skillList.join(', ') }}</p>
                        <p v-else class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                    </div>
                    <div>
                        <p class="heading">Experience</p>
                        <article v-for="(job, index) in cv.experience" :key="'b-job-' + index" class="item">
                            <p class="strong">{{ job.role }} · {{ job.company }}</p>
                            <p class="small">{{ job.dates }}</p>
                            <p class="body">{{ job.details }}</p>
                        </article>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="theme.layout === 'corner_frame'" class="frame-pad">
            <span class="corner tl" />
            <span class="corner tr" />
            <span class="corner bl" />
            <header class="frame-head">
                <div>
                    <h2>{{ cv.full_name }}</h2>
                    <p class="headline">{{ cv.headline }}</p>
                    <p v-for="line in contactLines" :key="line" class="small contact-line">{{ line }}</p>
                </div>
                <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" size="xl" :shape="photoShape" />
            </header>
            <p class="body intro">{{ cv.summary }}</p>
            <p class="heading">Professional experience</p>
            <article v-for="(job, index) in cv.experience" :key="'f-job-' + index" class="time-row">
                <p class="when">{{ job.dates }}</p>
                <div>
                    <p class="strong">{{ job.company }}{{ job.company && job.role ? ', ' : '' }}{{ job.role }}</p>
                    <p class="body">{{ job.details }}</p>
                </div>
            </article>
            <p class="heading">Education</p>
            <article v-for="(item, index) in cv.education" :key="'f-ed-' + index" class="time-row">
                <p class="when">{{ item.dates }}</p>
                <div>
                    <p class="strong">{{ item.credential }}</p>
                    <p class="small">{{ item.school }}</p>
                </div>
            </article>
            <p class="heading">Skills</p>
            <p class="small">{{ skillList.join(' · ') }}</p>
        </div>
        <div v-else-if="theme.layout === 'split_panel'" class="panel-split">
            <aside class="panel-left">
                <h2 class="display-name">{{ cv.full_name }}</h2>
                <p class="headline">{{ cv.headline }}</p>
                <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" size="xl" :shape="photoShape" />
                <p v-for="line in contactLines" :key="line" class="small">{{ line }}</p>
                <p class="heading">Skills</p>
                <p v-for="skill in skillList" :key="skill" class="skill-track">
                    <span>{{ skill }}</span>
                    <span class="track" />
                </p>
            </aside>
            <div class="panel-right">
                <p class="body intro">{{ cv.summary }}</p>
                <p class="heading">Professional experience</p>
                <article v-for="(job, index) in cv.experience" :key="'v-job-' + index" class="item">
                    <p class="strong">{{ job.company }}{{ job.company && job.role ? ', ' : '' }}{{ job.role }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'v-ed-' + index" class="item">
                    <p class="strong">{{ item.credential }}</p>
                    <p class="small">{{ item.school }} {{ item.dates }}</p>
                </article>
            </div>
        </div>
        <div v-else-if="theme.layout === 'centered_portrait'" class="harbor">
            <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" size="xl" :shape="photoShape" />
            <h2>{{ cv.full_name }}</h2>
            <p class="headline">{{ cv.headline }}</p>
            <p class="small">{{ contactLine }}</p>
            <p class="body intro">{{ cv.summary }}</p>
            <p class="heading">Professional experience</p>
            <article v-for="(job, index) in cv.experience" :key="'h-job-' + index" class="time-row">
                <p class="when">{{ job.dates }}</p>
                <div>
                    <p class="strong">{{ job.role }}{{ job.company ? ', ' + job.company : '' }}</p>
                    <p class="body">{{ job.details }}</p>
                </div>
            </article>
            <p class="heading">Education</p>
            <article v-for="(item, index) in cv.education" :key="'h-ed-' + index" class="time-row">
                <p class="when">{{ item.dates }}</p>
                <div>
                    <p class="strong">{{ item.credential }}</p>
                    <p class="small">{{ item.school }}</p>
                </div>
            </article>
            <p class="heading">Skills</p>
            <p class="chips filled"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
        </div>
        <div v-else-if="theme.layout === 'photo_top'" class="pad">
            <header class="portrait">
                <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" size="lg" :shape="photoShape" />
                <div>
                    <h2>{{ cv.full_name }}</h2>
                    <p class="small">{{ cv.headline }}</p>
                    <p class="small">{{ contactLine }}</p>
                </div>
            </header>
            <div class="rule" />
            <p class="heading">Profile</p>
            <p class="body">{{ cv.summary }}</p>
            <p class="heading">Experience</p>
            <article v-for="(job, index) in cv.experience" :key="'p-job-' + index" class="item">
                <p class="strong">{{ job.role }} · {{ job.company }}</p>
                <p class="small">{{ job.dates }}</p>
                <p class="body">{{ job.details }}</p>
            </article>
            <div class="cols">
                <div>
                    <p class="heading">Education</p>
                    <article v-for="(item, index) in cv.education" :key="'p-ed-' + index" class="item">
                        <p class="strong">{{ item.school }}</p>
                        <p class="small">{{ item.credential }} {{ item.dates }}</p>
                    </article>
                </div>
                <div>
                    <p class="heading">Skills</p>
                    <p v-if="theme.skills === 'list'" class="small">{{ skillList.join(', ') }}</p>
                    <p v-else class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
                </div>
            </div>
        </div>
        <div v-else class="stack">
            <CvAvatar v-if="showPhoto" :src="cv.photo" :initials="initials" size="lg" :shape="photoShape" />
            <h2>{{ cv.full_name }}</h2>
            <p class="small">{{ cv.headline }}</p>
            <p class="small">{{ contactLine }}</p>
            <div class="rule" />
            <div class="leftish">
                <p class="heading">Profile</p>
                <p class="body">{{ cv.summary }}</p>
                <p class="heading">Experience</p>
                <article v-for="(job, index) in cv.experience" :key="'s-job-' + index" class="item">
                    <p class="strong">{{ job.role }} · {{ job.company }}</p>
                    <p class="small">{{ job.dates }}</p>
                    <p class="body">{{ job.details }}</p>
                </article>
                <p class="heading">Education</p>
                <article v-for="(item, index) in cv.education" :key="'s-ed-' + index" class="item">
                    <p class="strong">{{ item.school }}</p>
                    <p class="small">{{ item.credential }} {{ item.dates }}</p>
                </article>
                <p class="heading">Skills</p>
                <p v-if="theme.skills === 'list'" class="small">{{ skillList.join(', ') }}</p>
                <p v-else class="chips"><span v-for="skill in skillList" :key="skill">{{ skill }}</span></p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sheet {
    color: var(--cv-ink);
    background: var(--cv-paper);
    @apply space-y-3 overflow-hidden rounded-xl border p-0 text-sm;
}

.sheet h2 {
    @apply font-semibold;
}

.md h2 {
    @apply text-2xl;
}

.lg h2 {
    @apply text-3xl;
}

.xl h2 {
    @apply text-4xl leading-none;
}

.center {
    @apply text-center;
}

.split {
    @apply grid gap-0 md:grid-cols-[13rem_1fr];
}

.split.reverse {
    @apply md:grid-cols-[1fr_13rem];
}

.split.reverse .side {
    @apply md:order-2;
}

.side {
    color: var(--cv-sidebar-ink);
    background: var(--cv-sidebar);
    @apply space-y-2 p-4;
}

.side .heading {
    border-color: color-mix(in srgb, var(--cv-sidebar-ink) 35%, transparent);
    color: var(--cv-sidebar-ink);
}

.side .small,
.side .strong {
    color: var(--cv-sidebar-ink);
}

.main,
.pad {
    @apply p-5;
}

.band {
    color: var(--cv-sidebar-ink);
    background: var(--cv-sidebar);
    @apply space-y-1 px-5 py-4;
}

.band .small {
    color: var(--cv-sidebar-ink);
}

.portrait {
    @apply mb-3 flex items-center gap-4 text-left;
}

.stack {
    @apply space-y-2 px-5 py-6;
}

.leftish {
    @apply text-left;
}

.heading {
    color: var(--cv-heading);
    border-color: var(--cv-accent);
    @apply mt-4 border-b pb-1 text-xs font-semibold uppercase tracking-wide;
}

.small {
    color: var(--cv-muted);
    @apply text-sm;
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
    color: var(--cv-heading);
    border-color: var(--cv-accent);
    @apply rounded border px-2 py-0.5 text-xs;
}

.cols {
    @apply grid gap-4 md:grid-cols-2;
}

.rule {
    background: var(--cv-accent);
    @apply mx-auto my-3 h-0.5 w-2/5;
}

.left .rule,
.sheet:not(.center) .rule {
    @apply ml-0;
}

.frame-pad {
    @apply relative space-y-3 px-6 py-8;
}

.corner {
    position: absolute;
    width: 0;
    height: 0;
    pointer-events: none;
}

.corner.tl {
    top: 0;
    left: 0;
    border-top: 4.5rem solid var(--cv-accent);
    border-right: 5.5rem solid transparent;
}

.corner.tr {
    top: 0;
    right: 0;
    border-top: 4.5rem solid var(--cv-accent);
    border-left: 5.5rem solid transparent;
}

.corner.bl {
    bottom: 0;
    left: 0;
    border-bottom: 6rem solid var(--cv-sidebar);
    border-right: 7rem solid transparent;
}

.frame-head {
    @apply flex items-start justify-between gap-4;
}

.headline {
    color: var(--cv-muted);
    @apply mb-2 text-lg italic;
}

.intro {
    @apply mt-2;
}

.time-row {
    @apply mb-3 grid gap-3 sm:grid-cols-[8.5rem_1fr];
}

.when {
    color: var(--cv-muted);
    @apply text-xs;
}

.corner_frame .heading,
.harbor .heading {
    @apply mt-5 flex items-center gap-2 border-b-0;
}

.corner_frame .heading::before,
.harbor .heading::before {
    content: '';
    width: 0.7rem;
    height: 0.7rem;
    border-radius: 2px;
    background: var(--cv-accent);
}

.harbor .heading {
    border-bottom: 1px solid var(--cv-accent);
    padding-bottom: 0.25rem;
}

.harbor .heading::before {
    display: none;
}

.panel-split {
    @apply grid min-h-[36rem] md:grid-cols-[18rem_1fr];
}

.panel-left {
    @apply space-y-3 p-5;
}

.panel-right {
    color: var(--cv-sidebar-ink);
    background: var(--cv-sidebar);
    @apply space-y-3 p-5;
}

.panel-right .heading,
.panel-right .small,
.panel-right .strong,
.panel-right .body {
    color: var(--cv-sidebar-ink);
    border-color: color-mix(in srgb, var(--cv-sidebar-ink) 35%, transparent);
}

.display-name {
    @apply text-3xl font-black uppercase leading-none tracking-wide;
}

.split_panel .panel-left .heading {
    @apply border-b-0;
}

.skill-track {
    @apply space-y-1 text-sm;
}

.track {
    display: block;
    height: 5px;
    border-radius: 99px;
    background: var(--cv-heading);
    opacity: 0.35;
}

.harbor {
    @apply space-y-3 px-6 py-8 text-center;
}

.harbor .time-row,
.harbor .body,
.harbor .heading,
.harbor .chips {
    @apply text-left;
}

.harbor h2 {
    color: var(--cv-heading);
}

.chips.filled span {
    color: #fff;
    background: var(--cv-accent);
    border-color: transparent;
    @apply rounded-full px-3 py-1;
}

.contact-line {
    @apply mb-0.5;
}
</style>
