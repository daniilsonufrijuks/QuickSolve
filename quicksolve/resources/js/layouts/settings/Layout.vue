<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];

const currentPath = window.location.pathname;
</script>

<template>
    <div class="sf60e2r">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="s1msxd9v">
            <aside class="sz7gkg7">
                <nav class="swmsfbx">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="s1779fqa" />

            <div class="s1bmfrs7">
                <section class="s1hdaytl">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.sf60e2r {
    @apply px-4 py-6;
}

.s1msxd9v {
    @apply flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0;
}

.sz7gkg7 {
    @apply w-full max-w-xl lg:w-48;
}

.swmsfbx {
    @apply flex flex-col space-x-0 space-y-1;
}

.s1779fqa {
    @apply my-6 md:hidden;
}

.s1bmfrs7 {
    @apply flex-1 md:max-w-2xl;
}

.s1hdaytl {
    @apply max-w-xl space-y-12;
}
</style>
