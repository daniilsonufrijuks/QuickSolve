<script setup lang="ts">
import EmptyState from '@/components/marketing/EmptyState.vue';
import PriceBadge from '@/components/marketing/PriceBadge.vue';
import ToolCard from '@/components/marketing/ToolCard.vue';
import DescriptionGenerator from '@/components/tools/DescriptionGenerator.vue';
import InvoiceGenerator from '@/components/tools/InvoiceGenerator.vue';
import ProfitCalculator from '@/components/tools/ProfitCalculator.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
}>();
</script>

<template>
    <Head :title="tool.name">
        <meta head-key="description" name="description" :content="tool.description" />
    </Head>
    <MarketingLayout>
        <article class="mx-auto max-w-6xl px-4 py-12">
            <nav class="text-sm text-slate-500" aria-label="Breadcrumb">
                <Link href="/">Home</Link>
                <span aria-hidden="true"> / </span>
                <Link href="/tools">Tools</Link>
                <span aria-hidden="true"> / </span>
                <span>{{ tool.name }}</span>
            </nav>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <p class="text-sm text-slate-500">{{ tool.category?.name }}</p>
                <PriceBadge :access="tool.access_type" />
            </div>
            <h1 class="mt-2 text-3xl font-semibold">{{ tool.name }}</h1>
            <p class="mt-3 max-w-3xl text-lg leading-8 text-slate-600">{{ tool.description }}</p>

            <div v-if="locked" class="mt-8">
                <EmptyState title="This tool is included with Pro" message="Premium tools stay locked until a verified subscription is active on the account. Opening this page does not grant access." />
                <Button as-child class="mt-4"><Link href="/pricing">View plans</Link></Button>
            </div>
            <div v-else class="mt-8">
                <ProfitCalculator v-if="tool.slug === 'profit-margin-calculator'" />
                <DescriptionGenerator v-else-if="tool.slug === 'product-description-generator'" :usage="usage" :demo-mode="demoMode" />
                <InvoiceGenerator v-else-if="tool.slug === 'invoice-generator'" />
            </div>

            <section class="prose mt-12 max-w-3xl">
                <h2 class="text-2xl font-semibold">How to use it</h2>
                <p class="mt-3 leading-7 text-slate-700">{{ tool.long_description }}</p>
            </section>

            <section v-if="tool.metadata?.faqs?.length" class="mt-10 max-w-3xl">
                <h2 class="text-2xl font-semibold">Questions</h2>
                <div class="mt-4 space-y-3">
                    <details v-for="faq in tool.metadata.faqs" :key="faq.q" class="rounded-xl border bg-white p-4">
                        <summary class="cursor-pointer font-medium">{{ faq.q }}</summary>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ faq.a }}</p>
                    </details>
                </div>
            </section>

            <section v-if="related.length" class="mt-12">
                <h2 class="text-2xl font-semibold">Related tools</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <ToolCard v-for="item in related" :key="item.slug" :tool="item" />
                </div>
            </section>
        </article>
    </MarketingLayout>
</template>
