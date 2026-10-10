<script setup lang="ts">
defineProps<{
    items?: Array<{ title: string; href: string; note: string }>;
}>();
</script>

<template>
    <aside v-if="items && items.length" class="s1h5ej98">
        <h2 class="s1w3egwb">Related resources</h2>
        <ul class="s1v3lnry">
            <li v-for="item in items" :key="item.href">
                <a :href="item.href" class="s1hivl2v" rel="sponsored noopener">{{ item.title }}</a>
                <p class="s1frj4rm">{{ item.note }}</p>
            </li>
        </ul>
    </aside>
</template>

<style scoped>
.s1h5ej98 {
    @apply rounded-xl border border-slate-200 p-4;
}

.s1w3egwb {
    @apply text-sm font-semibold;
}

.s1v3lnry {
    @apply mt-3 space-y-2 text-sm;
}

.s1hivl2v {
    @apply font-medium text-blue-700;
}

.s1frj4rm {
    @apply text-slate-600;
}
</style>
