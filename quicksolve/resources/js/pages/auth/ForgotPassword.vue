<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
        <Head title="Forgot password" />

        <div v-if="status" class="sh2og6m">
            {{ status }}
        </div>

        <div class="s1j8i8bf">
            <form @submit.prevent="submit">
                <div class="s1m7bk4h">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" autofocus placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="s3yk35v">
                    <Button class="s1l2zdph" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="s1nmg2sh" />
                        Email password reset link
                    </Button>
                </div>
            </form>

            <div class="s1jn6vs9">
                <span>Or, return to</span>
                <TextLink :href="route('login')">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.sh2og6m {
    @apply mb-4 text-center text-sm font-medium text-green-600;
}

.s1j8i8bf {
    @apply space-y-6;
}

.s1m7bk4h {
    @apply grid gap-2;
}

.s3yk35v {
    @apply my-6 flex items-center justify-start;
}

.s1l2zdph {
    @apply w-full;
}

.s1nmg2sh {
    @apply h-4 w-4 animate-spin;
}

.s1jn6vs9 {
    @apply space-x-1 text-center text-sm text-muted-foreground;
}
</style>
