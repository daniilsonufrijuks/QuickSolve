<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const seo = computed(() => (page.props.seo ?? null) as {
    title: string;
    description: string;
    canonical: string;
    robots: string;
    ogType: string;
    image: string;
    imageAlt: string;
} | null);
</script>

<template>
    <Head v-if="seo">
        <title>{{ seo.title }}</title>
        <meta head-key="description" name="description" :content="seo.description" />
        <meta head-key="robots" name="robots" :content="seo.robots" />
        <link head-key="canonical" rel="canonical" :href="seo.canonical" />
        <meta head-key="og:title" property="og:title" :content="seo.title" />
        <meta head-key="og:description" property="og:description" :content="seo.description" />
        <meta head-key="og:url" property="og:url" :content="seo.canonical" />
        <meta head-key="og:type" property="og:type" :content="seo.ogType" />
        <meta head-key="og:image" property="og:image" :content="seo.image" />
        <meta head-key="og:image:alt" property="og:image:alt" :content="seo.imageAlt" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="seo.title" />
        <meta head-key="twitter:description" name="twitter:description" :content="seo.description" />
        <meta head-key="twitter:image" name="twitter:image" :content="seo.image" />
    </Head>
</template>
