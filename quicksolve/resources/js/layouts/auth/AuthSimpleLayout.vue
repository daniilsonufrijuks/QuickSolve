<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="sggc34b">
        <div class="s10c7nbo">
            <div class="s1xuxg9g">
                <div class="s9ffzcc">
                    <Link :href="route('home')" class="s1b6kb3x">
                        <div class="s1m6m1ow">
                            <AppLogoIcon class="s7hvmqm" />
                        </div>
                        <span class="s11wu7oa">{{ title }}</span>
                    </Link>
                    <div class="s125ojcc">
                        <h1 class="s6ouyw7">{{ title }}</h1>
                        <p class="s2s8p2s">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.sggc34b {
    @apply flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10;
}

.s10c7nbo {
    @apply w-full max-w-sm;
}

.s1xuxg9g {
    @apply flex flex-col gap-8;
}

.s9ffzcc {
    @apply flex flex-col items-center gap-4;
}

.s1b6kb3x {
    @apply flex flex-col items-center gap-2 font-medium;
}

.s1m6m1ow {
    @apply mb-1 flex h-9 w-9 items-center justify-center rounded-md;
}

.s7hvmqm {
    @apply size-9 fill-current text-[var(--foreground)] dark:text-white;
}

.s11wu7oa {
    @apply sr-only;
}

.s125ojcc {
    @apply space-y-2 text-center;
}

.s6ouyw7 {
    @apply text-xl font-medium;
}

.s2s8p2s {
    @apply text-center text-sm text-muted-foreground;
}
</style>
