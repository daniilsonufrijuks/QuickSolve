<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="sv5nqum">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="s1j8i8bf">
                    <div class="s1m7bk4h">
                        <Label for="name">Name</Label>
                        <Input id="name" class="shtp1n1" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="s200p8" :message="form.errors.name" />
                    </div>

                    <div class="s1m7bk4h">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="shtp1n1"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="s200p8" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="sbmr2fg">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="sgtsarf"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="s9bs6kz">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="s2ca09y">
                        <Button :disabled="form.processing">Save</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="sz34qra">Saved.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.sv5nqum {
    @apply flex flex-col space-y-6;
}

.s1j8i8bf {
    @apply space-y-6;
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

.sbmr2fg {
    @apply mt-2 text-sm text-neutral-800;
}

.sgtsarf {
    @apply rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.s9bs6kz {
    @apply mt-2 text-sm font-medium text-green-600;
}

.s2ca09y {
    @apply flex items-center gap-4;
}

.sz34qra {
    @apply text-sm text-neutral-600;
}
</style>
