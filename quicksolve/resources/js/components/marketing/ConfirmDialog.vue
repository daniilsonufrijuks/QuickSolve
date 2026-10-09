<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Modal from '@/components/marketing/Modal.vue';

defineProps<{ open: boolean; title: string; message: string }>();
defineEmits<{ confirm: []; cancel: [] }>();
</script>

<template>
    <Modal :open="open" :title="title" @close="$emit('cancel')">
        <p class="text-sm text-slate-600 dark:text-slate-300">{{ message }}</p>
        <div class="mt-6 flex justify-end gap-2">
            <Button variant="outline" type="button" @click="$emit('cancel')">Cancel</Button>
            <Button type="button" @click="$emit('confirm')">Confirm</Button>
        </div>
    </Modal>
</template>
