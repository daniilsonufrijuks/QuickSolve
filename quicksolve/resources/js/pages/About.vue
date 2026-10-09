<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="About" />
    <MarketingLayout>
        <article class="mx-auto max-w-3xl px-4 py-12">
            <h1 class="text-3xl font-semibold">About QuickSolve</h1>
            <p class="mt-4 leading-7 text-slate-700">QuickSolve is a small set of tools and downloadable files for people who run a business and want the practical pieces in one place: a margin calculation, an invoice, a product description, and a few templates.</p>
            <p class="mt-4 leading-7 text-slate-700">The free tools are there to be useful on their own. Paid plans raise limits. Downloads are sold separately, and only after a verified payment. The site does not publish customer counts, ratings, or testimonials.</p>
            <h2 class="mt-8 text-xl font-semibold">What it does not do</h2>
            <p class="mt-3 leading-7 text-slate-700">It does not file taxes, collect invoice payments, or guarantee that a generated description is ready to publish. You still review the work before you send it.</p>
        </article>
    </MarketingLayout>
</template>
