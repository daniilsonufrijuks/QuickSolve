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
        <article class="mx-auto grid max-w-6xl gap-10 px-4 py-12 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <nav class="text-sm text-slate-500"><Link href="/">Home</Link> / <Link href="/templates">Templates</Link> / {{ template.name }}</nav>
                <p class="mt-4 text-sm text-slate-500">{{ template.category?.name }}</p>
                <h1 class="mt-2 text-3xl font-semibold">{{ template.name }}</h1>
                <img v-if="template.preview_image" :src="template.preview_image" :alt="`${template.name} preview`" class="mt-6 w-full rounded-xl border" />
                <div class="mt-6 whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ template.description }}</div>
                <h2 class="mt-8 text-xl font-semibold">What's included</h2>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700">
                    <li v-for="item in template.whats_included" :key="item">{{ item }}</li>
                </ul>
            </div>
            <aside class="h-fit rounded-xl border bg-white p-6 shadow-sm">
                <p class="text-3xl font-semibold">{{ template.formatted_price }}</p>
                <p class="mt-2 text-sm text-slate-600">One-time purchase. This is separate from a Pro subscription. The file is delivered from private storage after payment is verified.</p>
                <Button v-if="owned" as-child class="mt-6 w-full"><Link href="/dashboard/downloads">Go to downloads</Link></Button>
                <Button v-else-if="!page.props.auth.user" as-child class="mt-6 w-full"><Link href="/login">Log in to purchase</Link></Button>
                <Button v-else class="mt-6 w-full" :disabled="form.processing || !template.purchasable" @click="form.post(`/templates/${template.id}/checkout`)">Purchase</Button>
                <p v-if="!template.purchasable" class="mt-3 text-sm text-red-700">This product does not have a file yet, so checkout is disabled.</p>
            </aside>
            <section v-if="related.length" class="lg:col-span-2">
                <h2 class="text-xl font-semibold">Related products</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <TemplateCard v-for="item in related" :key="item.slug" :template="item" />
                </div>
            </section>
        </article>
    </MarketingLayout>
</template>
