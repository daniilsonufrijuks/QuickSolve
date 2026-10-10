<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = page.props.name;
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="srt01wn">
        <div class="s6o1dc7">
            <div class="s1rjkrkf" />
            <Link :href="route('home')" class="s1s718zg">
                <AppLogoIcon class="s1olr8zg" />
                {{ name }}
            </Link>
            <div v-if="quote" class="slamo8q">
                <blockquote class="s1j8i8bb">
                    <p class="s1bkxtzv">&ldquo;{{ quote.message }}&rdquo;</p>
                    <footer class="sz34oj7">{{ quote.author }}</footer>
                </blockquote>
            </div>
        </div>
        <div class="s1gqld3w">
            <div class="s167tgry">
                <div class="s1l8hmyn">
                    <h1 class="s14nyf1d" v-if="title">{{ title }}</h1>
                    <p class="sg9go2n" v-if="description">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.srt01wn {
    @apply relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0;
}

.s6o1dc7 {
    @apply relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex;
}

.s1rjkrkf {
    @apply absolute inset-0 bg-zinc-900;
}

.s1s718zg {
    @apply relative z-20 flex items-center text-lg font-medium;
}

.s1olr8zg {
    @apply mr-2 size-8 fill-current text-white;
}

.slamo8q {
    @apply relative z-20 mt-auto;
}

.s1j8i8bb {
    @apply space-y-2;
}

.s1bkxtzv {
    @apply text-lg;
}

.sz34oj7 {
    @apply text-sm text-neutral-300;
}

.s1gqld3w {
    @apply lg:p-8;
}

.s167tgry {
    @apply mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px];
}

.s1l8hmyn {
    @apply flex flex-col space-y-2 text-center;
}

.s14nyf1d {
    @apply text-xl font-medium tracking-tight;
}

.sg9go2n {
    @apply text-sm text-muted-foreground;
}
</style>
