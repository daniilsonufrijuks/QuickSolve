<script setup lang="ts">
import TemplateCard from '@/components/marketing/TemplateCard.vue';
import { Button } from '@/components/ui/button';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import type { SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    template: {
        id: number;
        name: string;
        slug: string;
        description: string;
        formatted_price: string;
        preview_image?: string | null;
        whats_included: string[];
        purchasable: boolean;
        category?: { name?: string | null };
    };
    related: Array<any>;
    owned: boolean;
}>();

const page = usePage<SharedData>();
const form = useForm({});
</script>

<template>
    <Head :title="template.name">
        <meta head-key="description" name="description" :content="template.description" />
    </Head>
    <MarketingLayout>
        <article class="sjip7j4">
            <div>
                <nav class="syp5vk7"><Link href="/">Home</Link> / <Link href="/templates">Templates</Link> / {{ template.name }}</nav>
                <p class="sujba3d">{{ template.category?.name }}</p>
                <h1 class="s13ddzcc">{{ template.name }}</h1>
                <img v-if="template.preview_image" :src="template.preview_image" :alt="`${template.name} preview`" class="s4iint9" />
                <div class="s59g4v9">{{ template.description }}</div>
                <h2 class="s2yk0t3">What's included</h2>
                <ul class="s1xa2mr0">
                    <li v-for="item in template.whats_included" :key="item">{{ item }}</li>
                </ul>
            </div>
            <aside class="s1jnewzh">
                <p class="s15bg7bs">{{ template.formatted_price }}</p>
                <p class="swr3obg">One-time purchase. This is separate from a Pro subscription. The file is delivered from private storage after payment is verified.</p>
                <Button v-if="owned" as-child class="sy1ioqd"><Link href="/dashboard/downloads">Go to downloads</Link></Button>
                <Button v-else-if="!page.props.auth.user" as-child class="sy1ioqd"><Link href="/login">Log in to purchase</Link></Button>
                <Button v-else class="sy1ioqd" :disabled="form.processing || !template.purchasable" @click="form.post(`/templates/${template.id}/checkout`)">Purchase</Button>
                <p v-if="!template.purchasable" class="s8aga0k">This product does not have a file yet, so checkout is disabled.</p>
            </aside>
            <section v-if="related.length" class="s8sj35n">
                <h2 class="sy1t0l">Related products</h2>
                <div class="s1bmozc8">
                    <TemplateCard v-for="item in related" :key="item.slug" :template="item" />
                </div>
            </section>
        </article>
    </MarketingLayout>
</template>

<style scoped>
.sjip7j4 {
    @apply mx-auto grid max-w-6xl gap-10 px-4 py-12 lg:grid-cols-[1.1fr_0.9fr];
}

.syp5vk7 {
    @apply text-sm text-slate-500;
}

.sujba3d {
    @apply mt-4 text-sm text-slate-500;
}

.s13ddzcc {
    @apply mt-2 text-3xl font-semibold;
}

.s4iint9 {
    @apply mt-6 w-full rounded-xl border;
}

.s59g4v9 {
    @apply mt-6 whitespace-pre-wrap text-sm leading-7 text-slate-700;
}

.s2yk0t3 {
    @apply mt-8 text-xl font-semibold;
}

.s1xa2mr0 {
    @apply mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700;
}

.s1jnewzh {
    @apply h-fit rounded-xl border bg-white p-6 shadow-sm;
}

.s15bg7bs {
    @apply text-3xl font-semibold;
}

.swr3obg {
    @apply mt-2 text-sm text-slate-600;
}

.sy1ioqd {
    @apply mt-6 w-full;
}

.s8aga0k {
    @apply mt-3 text-sm text-red-700;
}

.s8sj35n {
    @apply lg:col-span-2;
}

.sy1t0l {
    @apply text-xl font-semibold;
}

.s1bmozc8 {
    @apply mt-4 grid gap-4 md:grid-cols-3;
}
</style>
