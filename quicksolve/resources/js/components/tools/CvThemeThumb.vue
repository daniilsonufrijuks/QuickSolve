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

.corner_frame .s {
    top: 0;
    left: 0;
    border-top: 22px solid var(--cv-accent);
    border-right: 28px solid transparent;
    width: 0;
    height: 0;
}

.corner_frame .h {
    top: 0;
    right: 0;
    left: auto;
    border-top: 22px solid var(--cv-accent);
    border-left: 28px solid transparent;
    width: 0;
    height: 0;
    background: transparent;
}

.corner_frame .p {
    top: 14px;
    right: 12px;
    width: 18px;
    height: 18px;
    border-radius: 4px;
}

.corner_frame .l {
    top: 18px;
    right: 38px;
    left: 14px;
    height: 7px;
    background: var(--cv-heading);
}

.corner_frame .l2 {
    top: 42px;
    right: 14px;
    left: 14px;
}

.corner_frame .l3 {
    bottom: 0;
    left: 0;
    top: auto;
    width: 0;
    height: 0;
    border-bottom: 20px solid var(--cv-sidebar);
    border-right: 26px solid transparent;
    background: transparent;
}

.split_panel .s {
    top: 0;
    right: 0;
    bottom: 0;
    width: 58%;
    background: var(--cv-sidebar);
}

.split_panel .p {
    top: 28px;
    left: 10px;
    width: 18px;
    height: 18px;
    border-radius: 999px;
}

.split_panel .h {
    top: 10px;
    left: 8px;
    right: auto;
    width: 28%;
    height: 10px;
}

.split_panel .l {
    top: 16px;
    right: 10px;
    left: 48%;
    background: var(--cv-sidebar-ink);
    opacity: 0.5;
}

.split_panel .l2 {
    top: 36px;
    right: 10px;
    left: 48%;
    background: var(--cv-sidebar-ink);
    opacity: 0.5;
}

.split_panel .l3 {
    top: 56px;
    right: 18px;
    left: 48%;
    background: var(--cv-sidebar-ink);
    opacity: 0.5;
}

.centered_portrait .p {
    top: 8px;
    left: 50%;
    width: 20px;
    height: 20px;
    margin-left: -10px;
    border-radius: 999px;
}

.centered_portrait .h {
    top: 34px;
    right: 22px;
    left: 22px;
    height: 7px;
}

.centered_portrait .s {
    top: 46px;
    right: 30%;
    left: 30%;
    height: 2px;
    background: var(--cv-accent);
}

.centered_portrait .l {
    top: 56px;
    right: 12px;
    left: 12px;
}

.centered_portrait .l2 {
    top: 68px;
    right: 16px;
    left: 16px;
}

.centered_portrait .l3 {
    top: 78px;
    right: 22px;
    left: 22px;
}

.name {
    @apply mt-1.5 block text-xs font-medium text-slate-700 dark:text-slate-200;
}
</style>
