<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const ads = [
    {
        label: 'QuickSolve Pro',
        title: 'When will this invoice actually land?',
        copy: 'Net 30 is not payday. Payment landing date uses the client’s pay-run, weekends, holidays you type, and clearing days.',
        href: '/tools/payment-landing-date',
        cta: 'Open the tool',
    },
    {
        label: 'Stop unpaid extras',
        title: '“Can you just…” is a scope change.',
        copy: 'Write the original job, the extra request, and the extra cost. Download a PDF that holds the extra until the client confirms.',
        href: '/tools/scope-change-note',
        cta: 'Write a confirmation',
    },
    {
        label: 'One-time files',
        title: 'Templates you actually download.',
        copy: 'Spreadsheets and checklists are stored privately and handed over after Stripe confirms payment. There is no fake sample ZIP.',
        href: '/templates',
        cta: 'Browse templates',
    },
];

const index = ref(0);
const paused = ref(false);
let timer: ReturnType<typeof setInterval> | null = null;

function show(next: number) {
    index.value = (next + ads.length) % ads.length;
}

function start() {
    stop();
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    timer = setInterval(() => {
        if (! paused.value) {
            show(index.value + 1);
        }
    }, 7000);
}

function stop() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

onMounted(start);
onUnmounted(stop);
</script>

<template>
    <aside class="banner" aria-label="QuickSolve promotions" @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = false">
        <p class="kicker">From QuickSolve — not a third-party ad</p>
        <div class="stage">
            <article v-for="(ad, i) in ads" :key="ad.href" class="slide" :class="{ active: i === index }" :hidden="i !== index">
                <p class="label">{{ ad.label }}</p>
                <h2 class="title">{{ ad.title }}</h2>
                <p class="copy">{{ ad.copy }}</p>
                <Button as-child class="cta"><Link :href="ad.href">{{ ad.cta }}</Link></Button>
            </article>
        </div>
        <div class="dots" role="tablist" aria-label="Promotions">
            <button
                v-for="(ad, i) in ads"
                :key="ad.href + '-dot'"
                type="button"
                class="dot"
                :class="{ on: i === index }"
                :aria-label="'Show: ' + ad.label"
                :aria-selected="i === index"
                @click="show(i)"
            />
        </div>
    </aside>
</template>

<style scoped>
.banner {
    @apply relative overflow-hidden rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-700 via-blue-600 to-slate-900 p-6 text-white shadow-lg dark:border-blue-900;
}

.kicker {
    @apply text-xs font-medium uppercase tracking-wide text-blue-100;
}

.stage {
    @apply relative mt-3 min-h-[11rem];
}

.slide {
    @apply absolute inset-0 opacity-0 transition-opacity duration-500;
}

.slide.active {
    @apply relative opacity-100;
}

.title {
    @apply mt-1 max-w-xl text-2xl font-semibold tracking-tight;
}

.copy {
    @apply mt-2 max-w-2xl text-sm leading-6 text-blue-50;
}

.cta {
    @apply mt-4;
}

.dots {
    @apply mt-5 flex gap-2;
}

.dot {
    @apply h-2.5 w-2.5 rounded-full bg-white/40;
}

.dot.on {
    @apply bg-white;
}

@media (prefers-reduced-motion: reduce) {
    .slide {
        @apply transition-none;
    }
}
</style>
