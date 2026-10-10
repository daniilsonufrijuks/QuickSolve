<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <div v-if="status === 'verification-link-sent'" class="sh2og6m">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="siqxyi8">
            <Button :disabled="form.processing" variant="secondary">
                <LoaderCircle v-if="form.processing" class="s1nmg2sh" />
                Resend verification email
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="s3sxl3s"> Log out </TextLink>
        </form>
    </AuthLayout>
</template>

<style scoped>
.sh2og6m {
    @apply mb-4 text-center text-sm font-medium text-green-600;
}

.siqxyi8 {
    @apply space-y-6 text-center;
}

.s1nmg2sh {
    @apply h-4 w-4 animate-spin;
}

.s3sxl3s {
    @apply mx-auto block text-sm;
}
</style>
