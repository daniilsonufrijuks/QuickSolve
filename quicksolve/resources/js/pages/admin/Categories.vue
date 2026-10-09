<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{ categories: Array<any> }>();
const form = useForm({ name: '', slug: '', type: 'tool', description: '' });

function edit(category: any) {
    form.name = category.name;
    form.slug = category.slug;
    form.type = category.type;
    form.description = category.description ?? '';
}

function save() {
    const existing = props.categories.find((category) => category.slug === form.slug);
    if (existing) {
        form.put(`/admin/categories/${existing.id}`);
        return;
    }
    form.post('/admin/categories');
}
</script>

<template>
    <Head title="Categories" />
    <AppLayout :breadcrumbs="[{ title: 'Categories', href: '/admin/categories' }]">
        <div class="grid gap-6 p-4 lg:grid-cols-2">
            <form class="space-y-3" @submit.prevent="save">
                <FormField label="Name"><input v-model="form.name" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Slug" :error="form.errors.slug"><input v-model="form.slug" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Type"><select v-model="form.type" class="w-full rounded-lg border px-3 py-2"><option value="tool">Tool</option><option value="template">Template</option></select></FormField>
                <FormField label="Description"><textarea v-model="form.description" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <Button type="submit">Save category</Button>
            </form>
            <ul class="space-y-2 text-sm">
                <li v-for="category in categories" :key="category.id" class="flex justify-between rounded-lg border p-3">
                    <span>{{ category.name }} · {{ category.type }}</span>
                    <Button type="button" variant="outline" @click="edit(category)">Edit</Button>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
