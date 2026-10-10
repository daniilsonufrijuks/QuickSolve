<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

        <form @submit.prevent="submit">
            <div class="s1j8i8bf">
                <div class="s1m7bk4h">
                    <Label htmlFor="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        class="shtp1n1"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="sfjw2ix">
                    <Button class="s1l2zdph" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="s1nmg2sh" />
                        Confirm Password
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>

<style scoped>
.s1j8i8bf {
    @apply space-y-6;
}

.s1m7bk4h {
    @apply grid gap-2;
}

.shtp1n1 {
    @apply mt-1 block w-full;
}

.sfjw2ix {
    @apply flex items-center;
}

.s1l2zdph {
    @apply w-full;
}

.s1nmg2sh {
    @apply h-4 w-4 animate-spin;
}
</style>
