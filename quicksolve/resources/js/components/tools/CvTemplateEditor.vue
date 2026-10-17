<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import type { CvTheme } from '@/lib/cvTheme';

defineProps<{
    canDelete: boolean;
    busy?: boolean;
}>();

const theme = defineModel<CvTheme>('theme', { required: true });

const emit = defineEmits<{
    save: [];
    apply: [];
    remove: [];
}>();
</script>

<template>
    <div class="editor">
        <p class="meta">
            Color, type, and layout apply to the preview on the right. Saving keeps the template in this browser only.
            These are original QuickSolve layouts in a colorful résumé style, not Canva files.
        </p>
        <FormField label="Template name">
            <input v-model="theme.name" class="field" maxlength="40" />
        </FormField>
        <FormField label="Structure">
            <select v-model="theme.layout" class="field">
                <option value="sidebar_left">Photo column on the left</option>
                <option value="sidebar_right">Photo column on the right</option>
                <option value="header_band">Color band at the top</option>
                <option value="photo_top">Photo beside the name</option>
                <option value="corner_frame">Corner color frame</option>
                <option value="split_panel">Equal color split</option>
                <option value="centered_portrait">Centered photo</option>
                <option value="stacked">Centered single column</option>
            </select>
        </FormField>
        <FormField label="Type">
            <select v-model="theme.type" class="field">
                <option value="sans">Sans</option>
                <option value="serif">Serif</option>
            </select>
        </FormField>
        <FormField label="Name size">
            <select v-model="theme.name_size" class="field">
                <option value="md">Medium</option>
                <option value="lg">Large</option>
                <option value="xl">Extra large</option>
            </select>
        </FormField>
        <FormField label="Photo">
            <select v-model="theme.photo" class="field">
                <option value="circle">Circle</option>
                <option value="rounded">Rounded square</option>
                <option value="hidden">Hidden</option>
            </select>
        </FormField>
        <FormField label="Header alignment">
            <select v-model="theme.header_align" class="field">
                <option value="left">Left</option>
                <option value="center">Center</option>
            </select>
        </FormField>
        <FormField label="Skills">
            <select v-model="theme.skills" class="field">
                <option value="chips">Chips</option>
                <option value="list">Comma list</option>
            </select>
        </FormField>
        <div class="swatches">
            <label v-for="item in [
                ['paper', 'Paper'],
                ['ink', 'Text'],
                ['muted', 'Muted text'],
                ['accent', 'Accent'],
                ['heading', 'Headings'],
                ['sidebar', 'Column / band'],
                ['sidebar_ink', 'Column text'],
            ] as const" :key="item[0]" class="swatch">
                <span>{{ item[1] }}</span>
                <input v-model="theme[item[0]]" type="color" />
            </label>
        </div>
        <div class="row">
            <Button type="button" :disabled="busy" @click="emit('save')">Save in this browser</Button>
            <Button type="button" variant="outline" :disabled="busy" @click="emit('apply')">Use without saving</Button>
            <Button v-if="canDelete" type="button" variant="ghost" :disabled="busy" @click="emit('remove')">Delete</Button>
        </div>
    </div>
</template>

<style scoped>
.editor {
    @apply space-y-3 rounded-xl border p-5;
}

.meta {
    @apply text-sm text-slate-600 dark:text-slate-300;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.swatches {
    @apply grid grid-cols-2 gap-3 sm:grid-cols-3;
}

.swatch {
    @apply flex items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm;
}

.swatch input {
    @apply h-8 w-10 cursor-pointer rounded border-0 bg-transparent;
}

.row {
    @apply flex flex-wrap gap-2;
}
</style>
