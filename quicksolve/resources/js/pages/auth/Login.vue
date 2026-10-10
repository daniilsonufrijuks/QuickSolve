<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="sh2og6m">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="s1xuxg9e">
            <div class="s1m7bk4l">
                <div class="s1m7bk4h">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="s1m7bk4h">
                    <div class="sxc8ak4">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="s1bkxu62" tabindex="5"> Forgot password? </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="sxc8ak4" tabindex="3">
                    <Label for="remember" class="s1vwppvk">
                        <Checkbox id="remember" v-model:checked="form.remember" tabindex="4" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button type="submit" class="s1bfdsl3" tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="s1nmg2sh" />
                    Log in
                </Button>
            </div>

            <div class="s2s8p2s">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
.sh2og6m {
    @apply mb-4 text-center text-sm font-medium text-green-600;
}

.s1xuxg9e {
    @apply flex flex-col gap-6;
}

.s1m7bk4l {
    @apply grid gap-6;
}

.s1m7bk4h {
    @apply grid gap-2;
}

.sxc8ak4 {
    @apply flex items-center justify-between;
}

.s1bkxu62 {
    @apply text-sm;
}

.s1vwppvk {
    @apply flex items-center space-x-3;
}

.s1bfdsl3 {
    @apply mt-4 w-full;
}

.s1nmg2sh {
    @apply h-4 w-4 animate-spin;
}

.s2s8p2s {
    @apply text-center text-sm text-muted-foreground;
}
</style>
