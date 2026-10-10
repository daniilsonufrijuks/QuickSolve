<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="About" />
    <MarketingLayout>
        <article class="sm2nijf">
            <h1 class="s15bg7bs">About QuickSolve</h1>
            <p class="sgg612d">QuickSolve is a small set of tools and downloadable files for people who run a business and want the practical pieces in one place: a margin calculation, an invoice, a product description, and a few templates.</p>
            <p class="sgg612d">The free tools are there to be useful on their own. Paid plans raise limits. Downloads are sold separately, and only after a verified payment. The site does not publish customer counts, ratings, or testimonials.</p>
            <h2 class="s2yk0t3">What it does not do</h2>
            <p class="sfxp612">It does not file taxes, collect invoice payments, or guarantee that a generated description is ready to publish. You still review the work before you send it.</p>
        </article>
    </MarketingLayout>
</template>

<style scoped>
.sm2nijf {
    @apply mx-auto max-w-3xl px-4 py-12;
}

.s15bg7bs {
    @apply text-3xl font-semibold;
}

.sgg612d {
    @apply mt-4 leading-7 text-slate-700;
}

.s2yk0t3 {
    @apply mt-8 text-xl font-semibold;
}

.sfxp612 {
    @apply mt-3 leading-7 text-slate-700;
}
</style>
