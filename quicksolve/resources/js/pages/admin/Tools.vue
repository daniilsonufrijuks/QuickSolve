<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    tools: Array<any>;
    categories: Array<{ id: number; name: string }>;
}>();

const editingId = ref<number | null>(null);

const form = useForm({
    category_id: props.categories[0]?.id ?? '',
    name: '',
    slug: '',
    description: '',
    long_description: '',
    icon: 'calculator',
    access_type: 'free',
    is_featured: false,
    is_published: false,
    popularity: 0,
});

function edit(tool: any) {
    editingId.value = tool.id;
    form.category_id = tool.category_id;
    form.name = tool.name;
    form.slug = tool.slug;
    form.description = tool.description;
    form.long_description = tool.long_description ?? '';
    form.icon = tool.icon ?? '';
    form.access_type = tool.access_type;
    form.is_featured = tool.is_featured;
    form.is_published = tool.is_published;
    form.popularity = tool.popularity;
    form.clearErrors();
}

function save() {
    if (editingId.value) {
        form.put(`/admin/tools/${editingId.value}`);
        return;
    }

    form.post('/admin/tools');
}
</script>

<template>
    <Head title="Manage tools" />
    <AppLayout :breadcrumbs="[{ title: 'Admin tools', href: '/admin/tools' }]">
        <div class="grid gap-6 p-4 lg:grid-cols-2">
            <form class="space-y-3" @submit.prevent="save">
                <h1 class="text-xl font-semibold">Create or update a tool</h1>
                <FormField label="Category"><select v-model="form.category_id" class="w-full rounded-lg border px-3 py-2"><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></FormField>
                <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Slug" :error="form.errors.slug"><input v-model="form.slug" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Description" :error="form.errors.description"><textarea v-model="form.description" rows="3" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Long description"><textarea v-model="form.long_description" rows="4" class="w-full rounded-lg border px-3 py-2" /></FormField>
                <FormField label="Access"><select v-model="form.access_type" class="w-full rounded-lg border px-3 py-2"><option value="free">Free</option><option value="premium">Premium</option></select></FormField>
                <label class="flex gap-2 text-sm"><input v-model="form.is_published" type="checkbox" /> Published</label>
                <label class="flex gap-2 text-sm"><input v-model="form.is_featured" type="checkbox" /> Featured</label>
                <Button type="submit" :disabled="form.processing">Save tool</Button>
            </form>
            <ul class="space-y-2 text-sm">
                <li v-for="tool in tools" :key="tool.id" class="flex items-center justify-between rounded-lg border p-3">
                    <span>{{ tool.name }} · {{ tool.is_published ? 'Published' : 'Draft' }} · {{ tool.access_type }}</span>
                    <Button type="button" variant="outline" @click="edit(tool)">Edit</Button>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
