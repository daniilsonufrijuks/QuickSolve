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
        <section class="s1665r3c">
            <div class="s1wgivq5">
                <div>
                    <p class="s1d0sn9t">Small tools. Smarter business.</p>
                    <h1 class="s1uez7dt">Everything you need to run your small business.</h1>
                    <p class="sx13tis">Calculate profits, create business documents, generate product descriptions, and access practical templates — all in one place.</p>
                    <div class="sqgki1v">
                        <Button as-child><Link href="/tools">Explore Free Tools</Link></Button>
                        <Button as-child variant="outline"><Link href="/templates">Browse Templates</Link></Button>
                    </div>
                </div>
                <form class="suukg7i" @submit.prevent="search">
                    <SearchInput v-model="query" label="Find a tool" placeholder="Try profit, invoice, or description" />
                    <Button class="s200p9" type="submit">Search tools</Button>
                </form>
            </div>
        </section>

        <section class="s1w0rxx6">
            <h2 class="sixq2vr">Featured free tools</h2>
            <div class="s8emdm2">
                <ToolCard v-for="tool in featuredTools" :key="tool.slug" :tool="tool" />
            </div>
        </section>

        <section class="s9szkh0">
            <div class="s18rto4z">
                <h2 class="sixq2vr">Popular business tools</h2>
                <div class="s8emdm2">
                    <ToolCard v-for="tool in popularTools" :key="tool.slug" :tool="tool" />
                </div>
            </div>
        </section>

        <section class="s1w0rxx6">
            <h2 class="sixq2vr">Best-selling templates</h2>
            <p class="s1dj7fjp">These are the downloadable files currently in the catalog. Each one is a real file stored privately after purchase.</p>
            <div class="s8emdm2">
                <TemplateCard v-for="template in templates" :key="template.slug" :template="template" />
            </div>
        </section>

        <section class="s9szkh0">
            <div class="s10jvjj">
                <div>
                    <h2 class="s1cmvr70">Clear numbers</h2>
                    <p class="s1nsjtrh">Fees are split into a percentage and a fixed amount, so a card fee is not a guess.</p>
                </div>
                <div>
                    <h2 class="s1cmvr70">Documents you can send</h2>
                    <p class="s1nsjtrh">Invoices preview as you type, then export to PDF. Saving them is optional.</p>
                </div>
                <div>
                    <h2 class="s1cmvr70">Honest writing help</h2>
                    <p class="s1nsjtrh">If no AI provider is configured, the generator stays in demo mode instead of pretending.</p>
                </div>
            </div>
        </section>

        <section class="s1w0rxx6">
            <h2 class="sixq2vr">How it works</h2>
            <ol class="s8emdm2">
                <li class="spzmmle"><span class="s1d0sn9t">1</span><p class="s5t2knz">Start with a free tool</p><p class="sxuzv25">Run a calculation or draft an invoice without creating an account.</p></li>
                <li class="spzmmle"><span class="s1d0sn9t">2</span><p class="s5t2knz">Keep what you want</p><p class="sxuzv25">Sign in to store invoices, or buy a template as a one-time download.</p></li>
                <li class="spzmmle"><span class="s1d0sn9t">3</span><p class="s5t2knz">Upgrade only if the limit is in the way</p><p class="sxuzv25">Pro and Business raise the description allowance. Access changes after Stripe confirms payment.</p></li>
            </ol>
        </section>

        <section class="s9szkh0">
            <div class="s18rto4z">
                <h2 class="sixq2vr">Pricing overview</h2>
                <div class="s27kbim">
                    <PricingCard v-for="plan in plans" :key="plan.key" :plan="plan" />
                </div>
            </div>
        </section>

        <section class="sm2nijh">
            <h2 class="sixq2vr">Frequently asked questions</h2>
            <div class="smmcoax">
                <details v-for="[question, answer] in faqs" :key="question" class="s13yel9d">
                    <summary class="s18mzqs9">{{ question }}</summary>
                    <p class="s1nsjtrh">{{ answer }}</p>
                </details>
            </div>
        </section>
    </MarketingLayout>
</template>

<style scoped>
.s1665r3c {
    @apply border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950;
}

.s1wgivq5 {
    @apply mx-auto grid max-w-6xl gap-10 px-4 py-16 lg:grid-cols-[1.2fr_0.8fr] lg:items-center;
}

.s1d0sn9t {
    @apply text-sm font-medium text-blue-700;
}

.s1uez7dt {
    @apply mt-3 max-w-xl text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl dark:text-white;
}

.sx13tis {
    @apply mt-4 max-w-xl text-lg leading-8 text-slate-600 dark:text-slate-300;
}

.sqgki1v {
    @apply mt-6 flex flex-wrap gap-3;
}

.suukg7i {
    @apply rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900;
}

.s200p9 {
    @apply mt-3;
}

.s1w0rxx6 {
    @apply mx-auto max-w-6xl px-4 py-14;
}

.sixq2vr {
    @apply text-2xl font-semibold;
}

.s8emdm2 {
    @apply mt-6 grid gap-4 md:grid-cols-3;
}

.s9szkh0 {
    @apply bg-white py-14 dark:bg-slate-950;
}

.s18rto4z {
    @apply mx-auto max-w-6xl px-4;
}

.s1dj7fjp {
    @apply mt-2 max-w-2xl text-sm text-slate-600;
}

.s10jvjj {
    @apply mx-auto grid max-w-6xl gap-8 px-4 md:grid-cols-3;
}

.s1cmvr70 {
    @apply text-lg font-semibold;
}

.s1nsjtrh {
    @apply mt-2 text-sm leading-6 text-slate-600;
}

.spzmmle {
    @apply rounded-xl border bg-white p-5 dark:bg-slate-900;
}

.s5t2knz {
    @apply mt-2 font-medium;
}

.sxuzv25 {
    @apply mt-1 text-sm text-slate-600;
}

.s27kbim {
    @apply mt-6 grid gap-4 lg:grid-cols-3;
}

.sm2nijh {
    @apply mx-auto max-w-3xl px-4 py-14;
}

.smmcoax {
    @apply mt-6 space-y-4;
}

.s13yel9d {
    @apply rounded-xl border bg-white p-4 dark:bg-slate-900;
}

.s18mzqs9 {
    @apply cursor-pointer font-medium;
}
</style>
