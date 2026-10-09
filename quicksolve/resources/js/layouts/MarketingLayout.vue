<script setup lang="ts">
import AppFooter from '@/components/marketing/AppFooter.vue';
import AppHeader from '@/components/marketing/AppHeader.vue';
import ToastNotification from '@/components/marketing/ToastNotification.vue';
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <AppHeader />
        <main>
            <slot />
        </main>
        <AppFooter />
        <ToastNotification />
    </div>
</template>
