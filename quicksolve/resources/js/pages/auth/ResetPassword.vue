<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <form @submit.prevent="submit">
            <div class="s1m7bk4l">
                <div class="s1m7bk4h">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="shtp1n1" readonly />
                    <InputError :message="form.errors.email" class="s200p8" />
                </div>

                <div class="s1m7bk4h">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        v-model="form.password"
                        class="shtp1n1"
                        autofocus
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="s1m7bk4h">
                    <Label for="password_confirmation"> Confirm Password </Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        class="shtp1n1"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="s1bfdsl3" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="s1nmg2sh" />
                    Reset password
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>

<style scoped>
.s1m7bk4l {
    @apply grid gap-6;
}

.s1m7bk4h {
    @apply grid gap-2;
}

.shtp1n1 {
    @apply mt-1 block w-full;
}

.s200p8 {
    @apply mt-2;
}

.s1bfdsl3 {
    @apply mt-4 w-full;
}

.s1nmg2sh {
    @apply h-4 w-4 animate-spin;
}
</style>
