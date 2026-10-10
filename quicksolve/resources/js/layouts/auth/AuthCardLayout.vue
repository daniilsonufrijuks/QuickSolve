<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="s17o62hq">
        <div class="s1xsg4n5">
            <Link :href="route('home')" class="szi1ob1">
                <div class="sa7982o">
                    <AppLogoIcon class="sf5ht7u" />
                </div>
            </Link>

            <div class="s1xuxg9e">
                <Card class="s1wp7dp0">
                    <CardHeader class="s413xxv">
                        <CardTitle class="s1bkxuac">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="s6b35zk">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>

<style scoped>
.s17o62hq {
    @apply flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10;
}

.s1xsg4n5 {
    @apply flex w-full max-w-md flex-col gap-6;
}

.szi1ob1 {
    @apply flex items-center gap-2 self-center font-medium;
}

.sa7982o {
    @apply flex h-9 w-9 items-center justify-center;
}

.sf5ht7u {
    @apply size-9 fill-current text-black dark:text-white;
}

.s1xuxg9e {
    @apply flex flex-col gap-6;
}

.s1wp7dp0 {
    @apply rounded-xl;
}

.s413xxv {
    @apply px-10 pb-0 pt-8 text-center;
}

.s1bkxuac {
    @apply text-xl;
}

.s6b35zk {
    @apply px-10 py-8;
}
</style>
