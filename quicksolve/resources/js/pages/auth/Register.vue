<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="s1xuxg9e">
            <div class="s1m7bk4l">
                <div class="s1m7bk4h">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" required autofocus tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="s1m7bk4h">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="s1m7bk4h">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="s1m7bk4h">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="s1ot8wft" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="s1nmg2sh" />
                    Create account
                </Button>
            </div>

            <div class="s2s8p2s">
                Already have an account?
                <TextLink :href="route('login')" class="susjinj" tabindex="6">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
.s1xuxg9e {
    @apply flex flex-col gap-6;
}

.s1m7bk4l {
    @apply grid gap-6;
}

.s1m7bk4h {
    @apply grid gap-2;
}

.s1ot8wft {
    @apply mt-2 w-full;
}

.s1nmg2sh {
    @apply h-4 w-4 animate-spin;
}

.s2s8p2s {
    @apply text-center text-sm text-muted-foreground;
}

.susjinj {
    @apply underline underline-offset-4;
}
</style>
