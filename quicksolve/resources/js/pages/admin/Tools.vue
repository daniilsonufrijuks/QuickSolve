<script setup lang="ts">
import AdminNav from '@/components/admin/AdminNav.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
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

function resetForm() {
    editingId.value = null;
    form.reset();
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
        <div class="page">
            <AdminNav />
            <div class="layout">
                <form class="form" @submit.prevent="save">
                    <h1 class="title">{{ editingId ? 'Update tool' : 'Create a tool' }}</h1>
                    <FormField label="Category"><select v-model="form.category_id" class="field"><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></FormField>
                    <FormField label="Name" :error="form.errors.name"><input v-model="form.name" class="field" /></FormField>
                    <FormField label="Slug" :error="form.errors.slug"><input v-model="form.slug" class="field" /></FormField>
                    <FormField label="Description" :error="form.errors.description"><textarea v-model="form.description" rows="3" class="field" /></FormField>
                    <FormField label="Long description"><textarea v-model="form.long_description" rows="4" class="field" /></FormField>
                    <FormField label="Access"><select v-model="form.access_type" class="field"><option value="free">Free</option><option value="premium">Premium</option></select></FormField>
                    <label class="check"><input v-model="form.is_published" type="checkbox" /> Published</label>
                    <label class="check"><input v-model="form.is_featured" type="checkbox" /> Featured</label>
                    <div class="actions">
                        <Button type="submit" :disabled="form.processing">Save tool</Button>
                        <Button v-if="editingId" type="button" variant="ghost" @click="resetForm">Cancel edit</Button>
                    </div>
                </form>
                <ul class="list">
                    <li v-for="tool in tools" :key="tool.id" class="row">
                        <span>{{ tool.name }} · {{ tool.is_published ? 'Published' : 'Draft' }} · {{ tool.access_type }}</span>
                        <div class="actions">
                            <Button type="button" variant="outline" @click="edit(tool)">Edit</Button>
                            <Button type="button" variant="outline" @click="router.post(`/admin/tools/${tool.id}/toggle`)">{{ tool.is_published ? 'Unpublish' : 'Publish' }}</Button>
                            <Button type="button" variant="ghost" @click="router.delete(`/admin/tools/${tool.id}`)">Delete</Button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page {
    @apply space-y-4 p-4;
}

.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.form,
.list {
    @apply space-y-3;
}

.title {
    @apply text-xl font-semibold;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.check {
    @apply flex gap-2 text-sm;
}

.row {
    @apply flex items-center justify-between gap-3 rounded-lg border p-3 text-sm;
}

.actions {
    @apply flex flex-wrap gap-2;
}
</style>
