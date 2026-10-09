<script setup lang="ts">
defineProps<{
    items?: Array<{ title: string; href: string; note: string }>;
}>();
</script>

<template>
    <aside v-if="items && items.length" class="rounded-xl border border-slate-200 p-4">
        <h2 class="text-sm font-semibold">Related resources</h2>
        <ul class="mt-3 space-y-2 text-sm">
            <li v-for="item in items" :key="item.href">
                <a :href="item.href" class="font-medium text-blue-700" rel="sponsored noopener">{{ item.title }}</a>
                <p class="text-slate-600">{{ item.note }}</p>
            </li>
        </ul>
    </aside>
</template>
