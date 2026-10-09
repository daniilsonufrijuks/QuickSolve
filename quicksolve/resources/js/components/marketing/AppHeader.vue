<script setup lang="ts">
import MainNavigation from '@/components/marketing/MainNavigation.vue';
import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';

const open = ref(false);
const page = usePage<SharedData>();
</script>

<template>
    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4">
            <Link href="/" class="text-lg font-semibold tracking-tight text-slate-950 dark:text-white">QuickSolve</Link>
            <div class="hidden md:block">
                <MainNavigation />
            </div>
            <div class="hidden items-center gap-2 md:flex">
                <Button v-if="page.props.auth.user" as-child variant="outline">
                    <Link href="/dashboard">Dashboard</Link>
                </Button>
                <template v-else>
                    <Button as-child variant="ghost">
                        <Link href="/login">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link href="/register">Create account</Link>
                    </Button>
                </template>
            </div>
            <button class="rounded-md border border-slate-200 p-2 md:hidden" type="button" :aria-expanded="open" aria-label="Open menu" @click="open = !open">
                <X v-if="open" class="size-5" />
                <Menu v-else class="size-5" />
            </button>
        </div>
        <div v-if="open" class="space-y-4 border-t border-slate-200 px-4 py-4 md:hidden dark:border-slate-800">
            <MainNavigation />
            <div class="flex gap-2">
                <Button v-if="page.props.auth.user" as-child>
                    <Link href="/dashboard">Dashboard</Link>
                </Button>
                <template v-else>
                    <Button as-child variant="outline">
                        <Link href="/login">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link href="/register">Create account</Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>
