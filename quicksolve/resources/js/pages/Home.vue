<script setup lang="ts">
import PricingCard from '@/components/marketing/PricingCard.vue';
import SearchInput from '@/components/marketing/SearchInput.vue';
import TemplateCard from '@/components/marketing/TemplateCard.vue';
import ToolCard from '@/components/marketing/ToolCard.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    featuredTools: Array<any>;
    popularTools: Array<any>;
    templates: Array<any>;
    plans: Array<any>;
}>();

const query = ref('');

function search() {
    router.get('/tools', { q: query.value });
}

const faqs = [
    ['Do I need an account to use the calculators?', 'No. The profit calculator and the invoice PDF work without an account. Saving invoices and buying templates requires a login.'],
    ['When is a description a live DeepSeek response?', 'Only when a DeepSeek API key is configured on the server and the request succeeds. Otherwise the page stays in demo mode and says so.'],
    ['Does a checkout redirect activate Pro?', 'No. Pro and Business stay inactive until Stripe confirms the subscription through a verified webhook.'],
];
</script>

<template>
    <Head title="Home" />
    <MarketingLayout>
        <section class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-16 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                <div>
                    <p class="text-sm font-medium text-blue-700">Small tools. Smarter business.</p>
                    <h1 class="mt-3 max-w-xl text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl dark:text-white">Everything you need to run your small business.</h1>
                    <p class="mt-4 max-w-xl text-lg leading-8 text-slate-600 dark:text-slate-300">Calculate profits, create business documents, generate product descriptions, and access practical templates — all in one place.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Button as-child><Link href="/tools">Explore Free Tools</Link></Button>
                        <Button as-child variant="outline"><Link href="/templates">Browse Templates</Link></Button>
                    </div>
                </div>
                <form class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="search">
                    <SearchInput v-model="query" label="Find a tool" placeholder="Try profit, invoice, or description" />
                    <Button class="mt-3" type="submit">Search tools</Button>
                </form>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-14">
            <h2 class="text-2xl font-semibold">Featured free tools</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <ToolCard v-for="tool in featuredTools" :key="tool.slug" :tool="tool" />
            </div>
        </section>

        <section class="bg-white py-14 dark:bg-slate-950">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-2xl font-semibold">Popular business tools</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <ToolCard v-for="tool in popularTools" :key="tool.slug" :tool="tool" />
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-14">
            <h2 class="text-2xl font-semibold">Best-selling templates</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">These are the downloadable files currently in the catalog. Each one is a real file stored privately after purchase.</p>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <TemplateCard v-for="template in templates" :key="template.slug" :template="template" />
            </div>
        </section>

        <section class="bg-white py-14 dark:bg-slate-950">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 md:grid-cols-3">
                <div>
                    <h2 class="text-lg font-semibold">Clear numbers</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Fees are split into a percentage and a fixed amount, so a card fee is not a guess.</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Documents you can send</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Invoices preview as you type, then export to PDF. Saving them is optional.</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Honest writing help</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">If no AI provider is configured, the generator stays in demo mode instead of pretending.</p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-14">
            <h2 class="text-2xl font-semibold">How it works</h2>
            <ol class="mt-6 grid gap-4 md:grid-cols-3">
                <li class="rounded-xl border bg-white p-5 dark:bg-slate-900"><span class="text-sm font-medium text-blue-700">1</span><p class="mt-2 font-medium">Start with a free tool</p><p class="mt-1 text-sm text-slate-600">Run a calculation or draft an invoice without creating an account.</p></li>
                <li class="rounded-xl border bg-white p-5 dark:bg-slate-900"><span class="text-sm font-medium text-blue-700">2</span><p class="mt-2 font-medium">Keep what you want</p><p class="mt-1 text-sm text-slate-600">Sign in to store invoices, or buy a template as a one-time download.</p></li>
                <li class="rounded-xl border bg-white p-5 dark:bg-slate-900"><span class="text-sm font-medium text-blue-700">3</span><p class="mt-2 font-medium">Upgrade only if the limit is in the way</p><p class="mt-1 text-sm text-slate-600">Pro and Business raise the description allowance. Access changes after Stripe confirms payment.</p></li>
            </ol>
        </section>

        <section class="bg-white py-14 dark:bg-slate-950">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-2xl font-semibold">Pricing overview</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    <PricingCard v-for="plan in plans" :key="plan.key" :plan="plan" />
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-3xl px-4 py-14">
            <h2 class="text-2xl font-semibold">Frequently asked questions</h2>
            <div class="mt-6 space-y-4">
                <details v-for="[question, answer] in faqs" :key="question" class="rounded-xl border bg-white p-4 dark:bg-slate-900">
                    <summary class="cursor-pointer font-medium">{{ question }}</summary>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ answer }}</p>
                </details>
            </div>
        </section>
    </MarketingLayout>
</template>
