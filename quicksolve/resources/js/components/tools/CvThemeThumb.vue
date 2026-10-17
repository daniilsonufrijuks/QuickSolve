<script setup lang="ts">
import { sanitizeTheme, themeVars, type CvTheme } from '@/lib/cvTheme';
import { computed } from 'vue';

const props = defineProps<{ theme: CvTheme }>();

const theme = computed(() => sanitizeTheme(props.theme));
const style = computed(() => themeVars(theme.value));
</script>

<template>
    <span class="shot" :class="theme.layout" :style="style" aria-hidden="true">
        <span class="s" />
        <span class="p" />
        <span class="h" />
        <span class="l" />
        <span class="l2" />
        <span class="l3" />
    </span>
    <span class="name">{{ theme.name }}</span>
</template>

<style scoped>
.shot {
    position: relative;
    display: block;
    height: 88px;
    overflow: hidden;
    border-radius: 8px;
    background: var(--cv-paper);
    box-shadow: inset 0 0 0 1px #e2e8f0;
}

.s,
.p,
.h,
.l,
.l2,
.l3 {
    position: absolute;
}

.p {
    background: var(--cv-muted);
}

.h {
    background: var(--cv-heading);
}

.l,
.l2,
.l3 {
    height: 4px;
    border-radius: 2px;
    background: var(--cv-accent);
    opacity: 0.45;
}

.sidebar_left .s,
.sidebar_right .s {
    top: 0;
    bottom: 0;
    width: 34%;
    background: var(--cv-sidebar);
}

.sidebar_left .s {
    left: 0;
}

.sidebar_right .s {
    right: 0;
}

.sidebar_left .p,
.sidebar_right .p {
    top: 8px;
    width: 16px;
    height: 16px;
    border-radius: 999px;
}

.sidebar_left .p {
    left: 9%;
}

.sidebar_right .p {
    right: 9%;
}

.sidebar_left .h {
    top: 12px;
    right: 10px;
    left: 42%;
    height: 8px;
}

.sidebar_right .h {
    top: 12px;
    right: 42%;
    left: 10px;
    height: 8px;
}

.sidebar_left .l {
    top: 34px;
    right: 12px;
    left: 42%;
}

.sidebar_left .l2 {
    top: 52px;
    right: 8px;
    left: 42%;
}

.sidebar_left .l3 {
    top: 68px;
    right: 20px;
    left: 42%;
}

.sidebar_right .l {
    top: 34px;
    right: 42%;
    left: 10px;
}

.sidebar_right .l2 {
    top: 52px;
    right: 42%;
    left: 10px;
}

.sidebar_right .l3 {
    top: 68px;
    right: 42%;
    left: 10px;
}

.header_band .h {
    top: 0;
    right: 0;
    left: 0;
    height: 30px;
    background: var(--cv-sidebar);
}

.header_band .p {
    top: 7px;
    left: 10px;
    width: 16px;
    height: 16px;
    border-radius: 999px;
}

.header_band .l {
    top: 42px;
    left: 10px;
    width: 34%;
    height: auto;
    bottom: 10px;
}

.header_band .l2 {
    top: 42px;
    right: 10px;
    left: 50%;
}

.header_band .l3 {
    top: 60px;
    right: 10px;
    left: 50%;
}

.stacked .h {
    top: 14px;
    right: 22px;
    left: 22px;
    height: 8px;
}

.stacked .s {
    top: 28px;
    right: 32%;
    left: 32%;
    height: 2px;
    background: var(--cv-accent);
}

.stacked .l {
    top: 42px;
    right: 14px;
    left: 14px;
}

.stacked .l2 {
    top: 58px;
    right: 18px;
    left: 14px;
}

.stacked .l3 {
    top: 72px;
    right: 24px;
    left: 14px;
}

.photo_top .p {
    top: 10px;
    left: 10px;
    width: 22px;
    height: 22px;
    border-radius: 8px;
}

.photo_top .h {
    top: 12px;
    right: 12px;
    left: 40px;
    height: 8px;
}

.photo_top .s {
    top: 28px;
    right: 30%;
    left: 40px;
    height: 2px;
    background: var(--cv-accent);
}

.photo_top .l {
    top: 42px;
    right: 12px;
    left: 12px;
}

.photo_top .l2 {
    top: 58px;
    right: 18px;
    left: 12px;
}

.photo_top .l3 {
    top: 72px;
    right: 28px;
    left: 12px;
}

.name {
    @apply mt-1.5 block text-xs font-medium text-slate-700 dark:text-slate-200;
}
</style>
