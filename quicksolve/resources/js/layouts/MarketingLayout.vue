<script setup lang="ts">
import AppFooter from '@/components/marketing/AppFooter.vue';
import AppHeader from '@/components/marketing/AppHeader.vue';
import SeoHead from '@/components/marketing/SeoHead.vue';
import ToastNotification from '@/components/marketing/ToastNotification.vue';
</script>

<template>
    <div class="suvqehr">
        <SeoHead />
        <AppHeader />
        <main>
            <slot />
        </main>
        <AppFooter />
        <ToastNotification />
    </div>
</template>

<style scoped>
.suvqehr {
    @apply min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100;
}
</style>
