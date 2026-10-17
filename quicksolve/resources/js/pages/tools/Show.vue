<script setup lang="ts">
import EmptyState from '@/components/marketing/EmptyState.vue';
import PriceBadge from '@/components/marketing/PriceBadge.vue';
import ToolCard from '@/components/marketing/ToolCard.vue';
import BreakEvenCalculator from '@/components/tools/BreakEvenCalculator.vue';
import BusinessCardMaker from '@/components/tools/BusinessCardMaker.vue';
import CashRunwayCalculator from '@/components/tools/CashRunwayCalculator.vue';
import CoverLetterBuilder from '@/components/tools/CoverLetterBuilder.vue';
import CvBuilder from '@/components/tools/CvBuilder.vue';
import EmailTemplates from '@/components/tools/EmailTemplates.vue';
import DateDifferenceCalculator from '@/components/tools/DateDifferenceCalculator.vue';
import DescriptionGenerator from '@/components/tools/DescriptionGenerator.vue';
import DiscountCalculator from '@/components/tools/DiscountCalculator.vue';
import FreelanceRateCalculator from '@/components/tools/FreelanceRateCalculator.vue';
import HttpStatusLookup from '@/components/tools/HttpStatusLookup.vue';
import InvoiceGenerator from '@/components/tools/InvoiceGenerator.vue';
import JwtDecoder from '@/components/tools/JwtDecoder.vue';
import MarketplaceFeeCalculator from '@/components/tools/MarketplaceFeeCalculator.vue';
import PaymentLandingDate from '@/components/tools/PaymentLandingDate.vue';
import ProfitCalculator from '@/components/tools/ProfitCalculator.vue';
import ScopeChangeNote from '@/components/tools/ScopeChangeNote.vue';
import QrCodeGenerator from '@/components/tools/QrCodeGenerator.vue';
import QuoteBuilder from '@/components/tools/QuoteBuilder.vue';
import RandomNumberGenerator from '@/components/tools/RandomNumberGenerator.vue';
import RegexTester from '@/components/tools/RegexTester.vue';
import RoasCalculator from '@/components/tools/RoasCalculator.vue';
import SerpSnippetPreview from '@/components/tools/SerpSnippetPreview.vue';
import TimesheetCalculator from '@/components/tools/TimesheetCalculator.vue';
import UnitConverter from '@/components/tools/UnitConverter.vue';
import UtmLinkBuilder from '@/components/tools/UtmLinkBuilder.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Link } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

const ImageResizer = defineAsyncComponent(() => import('@/components/tools/ImageResizer.vue'));
const ImageConverter = defineAsyncComponent(() => import('@/components/tools/ImageConverter.vue'));
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
    trial: { used: number; limit: number; remaining: number; window: string; plan: string };
    demoMode: boolean;
    premium: boolean;
    plan?: string;
    business?: boolean;
    cvThemes?: Array<{
        id: string;
        name: string;
        layout: 'sidebar_left' | 'sidebar_right' | 'header_band' | 'stacked' | 'photo_top';
        paper: string;
        ink: string;
        muted: string;
        accent: string;
        sidebar: string;
        sidebar_ink: string;
        heading: string;
        type: 'sans' | 'serif';
        name_size: 'md' | 'lg' | 'xl';
        photo: 'circle' | 'rounded' | 'hidden';
        header_align: 'left' | 'center';
        skills: 'chips' | 'list';
    }>;
}>();
</script>

<template>
    <MarketingLayout>
        <article :class="tool.slug === 'pdf-viewer-editor' ? 'wide' : 's1w0rxx4'">
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

            <p v-if="!locked && trial.window === 'lifetime' && tool.access_type === 'premium'" class="trial">
                Free Pro trial: {{ trial.remaining }} of {{ trial.limit }} uses left after this visit.
            </p>
            <div v-if="locked" class="s200pe">
                <EmptyState title="This tool is included with Pro" :message="trial.used >= trial.limit ? 'You have used the 5 free Pro trials on this browser or account. Subscribe to keep using PDF, image, timesheet, business card, CV, quotes, cover letters, cash runway, and email templates.' : 'Premium tools stay locked until a verified subscription is active on the account.'" />
                <Button as-child class="s200pa"><Link href="/pricing">View plans</Link></Button>
            </div>
            <div v-else class="s200pe">
                <ProfitCalculator v-if="tool.slug === 'profit-margin-calculator'" />
                <DescriptionGenerator v-else-if="tool.slug === 'product-description-generator'" :usage="usage" :demo-mode="demoMode" />
                <InvoiceGenerator v-else-if="tool.slug === 'invoice-generator'" :plan="plan" :business="business" />
                <DiscountCalculator v-else-if="tool.slug === 'discount-calculator'" />
                <FreelanceRateCalculator v-else-if="tool.slug === 'freelance-rate-calculator'" :premium="premium" />
                <QrCodeGenerator v-else-if="tool.slug === 'qr-code-generator'" :branding="premium" />
                <PdfEditor v-else-if="tool.slug === 'pdf-viewer-editor'" />
                <ImageResizer v-else-if="tool.slug === 'image-resizer'" />
                <ImageConverter v-else-if="tool.slug === 'image-converter'" />
                <BreakEvenCalculator v-else-if="tool.slug === 'break-even-calculator'" />
                <TimesheetCalculator v-else-if="tool.slug === 'timesheet-calculator'" />
                <SerpSnippetPreview v-else-if="tool.slug === 'serp-snippet-preview'" />
                <UtmLinkBuilder v-else-if="tool.slug === 'utm-link-builder'" />
                <MarketplaceFeeCalculator v-else-if="tool.slug === 'marketplace-fee-calculator'" />
                <RoasCalculator v-else-if="tool.slug === 'roas-calculator'" />
                <JwtDecoder v-else-if="tool.slug === 'jwt-decoder'" />
                <RegexTester v-else-if="tool.slug === 'regex-tester'" />
                <DateDifferenceCalculator v-else-if="tool.slug === 'date-difference-calculator'" />
                <UnitConverter v-else-if="tool.slug === 'unit-converter'" />
                <BusinessCardMaker v-else-if="tool.slug === 'business-card-maker'" />
                <RandomNumberGenerator v-else-if="tool.slug === 'random-number-generator'" />
                <HttpStatusLookup v-else-if="tool.slug === 'http-status-code-lookup'" />
                <CvBuilder v-else-if="tool.slug === 'cv-builder'" :usage="usage" :demo-mode="demoMode" :themes="cvThemes" />
                <EmailTemplates v-else-if="tool.slug === 'email-templates'" :usage="usage" :demo-mode="demoMode" />
                <QuoteBuilder v-else-if="tool.slug === 'quote-builder'" :plan="plan" :business="business" />
                <CoverLetterBuilder v-else-if="tool.slug === 'cover-letter'" />
                <CashRunwayCalculator v-else-if="tool.slug === 'cash-runway-calculator'" />
                <PaymentLandingDate v-else-if="tool.slug === 'payment-landing-date'" />
                <ScopeChangeNote v-else-if="tool.slug === 'scope-change-note'" />
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

.wide {
    @apply mx-auto max-w-[90rem] px-3 py-6 sm:px-4;
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

.trial {
    @apply mt-6 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-900;
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
