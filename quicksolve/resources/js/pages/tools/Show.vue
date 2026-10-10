<script setup lang="ts">
import EmptyState from '@/components/marketing/EmptyState.vue';
import PriceBadge from '@/components/marketing/PriceBadge.vue';
import ToolCard from '@/components/marketing/ToolCard.vue';
import DescriptionGenerator from '@/components/tools/DescriptionGenerator.vue';
import DiscountCalculator from '@/components/tools/DiscountCalculator.vue';
import FreelanceRateCalculator from '@/components/tools/FreelanceRateCalculator.vue';
import InvoiceGenerator from '@/components/tools/InvoiceGenerator.vue';
import ProfitCalculator from '@/components/tools/ProfitCalculator.vue';
import QrCodeGenerator from '@/components/tools/QrCodeGenerator.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

const ImageResizer = defineAsyncComponent(() => import('@/components/tools/ImageResizer.vue'));
const PdfEditor = defineAsyncComponent(() => import('@/components/tools/PdfEditor.vue'));

defineProps<{
    tool: {
        name: string;
        slug: string;
        description: string;
        long_description?: string | null;
        access_type: string;
        category?: { name?: string | null; slug?: string | null };
        metadata?: { faqs?: Array<{ q: string; a: string }> };
    };
    related: Array<any>;
    locked: boolean;
    usage: { used: number; limit: number; remaining: number; window: string; plan: string } | null;
    demoMode: boolean;
    premium: boolean;
}>();
</script>

<template>
    <Head :title="tool.name">
        <meta head-key="description" name="description" :content="tool.description" />
    </Head>
    <MarketingLayout>
        <article class="s1w0rxx4">
            <nav class="s1bkxu62" aria-label="Breadcrumb">
                <Link href="/">Home</Link>
                <span aria-hidden="true"> / </span>
                <Link href="/tools">Tools</Link>
                <span aria-hidden="true"> / </span>
                <span>{{ tool.name }}</span>
            </nav>
            <div class="susfc2d">
                <p class="syp5vk7">{{ tool.category?.name }}</p>
                <PriceBadge :access="tool.access_type" />
            </div>
            <h1 class="s13ddzcc">{{ tool.name }}</h1>
            <p class="s1fu2krl">{{ tool.description }}</p>

            <div v-if="locked" class="s200pe">
                <EmptyState title="This tool is included with Pro" message="Premium tools stay locked until a verified subscription is active on the account. Opening this page does not grant access." />
                <Button as-child class="s200pa"><Link href="/pricing">View plans</Link></Button>
            </div>
            <div v-else class="s200pe">
                <ProfitCalculator v-if="tool.slug === 'profit-margin-calculator'" />
                <DescriptionGenerator v-else-if="tool.slug === 'product-description-generator'" :usage="usage" :demo-mode="demoMode" />
                <InvoiceGenerator v-else-if="tool.slug === 'invoice-generator'" />
                <DiscountCalculator v-else-if="tool.slug === 'discount-calculator'" />
                <FreelanceRateCalculator v-else-if="tool.slug === 'freelance-rate-calculator'" :premium="premium" />
                <QrCodeGenerator v-else-if="tool.slug === 'qr-code-generator'" :branding="premium" />
                <PdfEditor v-else-if="tool.slug === 'pdf-viewer-editor'" />
                <ImageResizer v-else-if="tool.slug === 'image-resizer'" />
            </div>

            <section class="srtekoe">
                <h2 class="sixq2vr">How to use it</h2>
                <p class="sfxp612">{{ tool.long_description }}</p>
            </section>

            <section v-if="tool.metadata?.faqs?.length" class="s4xz6dp">
                <h2 class="sixq2vr">Questions</h2>
                <div class="sb0yfeu">
                    <details v-for="faq in tool.metadata.faqs" :key="faq.q" class="s1na2kcg">
                        <summary class="s18mzqs9">{{ faq.q }}</summary>
                        <p class="s1nsjtrh">{{ faq.a }}</p>
                    </details>
                </div>
            </section>

            <section v-if="related.length" class="s1q0lqf">
                <h2 class="sixq2vr">Related tools</h2>
                <div class="s1bmozc8">
                    <ToolCard v-for="item in related" :key="item.slug" :tool="item" />
                </div>
            </section>
        </article>
    </MarketingLayout>
</template>

<style scoped>
.s1w0rxx4 {
    @apply mx-auto max-w-6xl px-4 py-12;
}

.s1bkxu62 {
    @apply text-sm;
}

.susfc2d {
    @apply mt-4 flex flex-wrap items-center gap-3;
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}

.s13ddzcc {
    @apply mt-2 text-3xl font-semibold;
}

.s1fu2krl {
    @apply mt-3 max-w-3xl text-lg leading-8;
}

.s200pe {
    @apply mt-8;
}

.s200pa {
    @apply mt-4;
}

.srtekoe {
    @apply mt-12 max-w-3xl;
}

.sixq2vr {
    @apply text-2xl font-semibold;
}

.sfxp612 {
    @apply mt-3 leading-7 text-slate-700;
}

.s4xz6dp {
    @apply mt-10 max-w-3xl;
}

.sb0yfeu {
    @apply mt-4 space-y-3;
}

.s1na2kcg {
    @apply rounded-xl border bg-white p-4;
}

.s18mzqs9 {
    @apply cursor-pointer font-medium;
}

.s1nsjtrh {
    @apply mt-2 text-sm leading-6 text-slate-600;
}

.s1q0lqf {
    @apply mt-12;
}

.s1bmozc8 {
    @apply mt-4 grid gap-4 md:grid-cols-3;
}
</style>
