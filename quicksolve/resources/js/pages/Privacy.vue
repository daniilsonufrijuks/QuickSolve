<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Privacy policy" />
    <MarketingLayout>
        <article class="mx-auto max-w-3xl space-y-4 px-4 py-12 text-sm leading-7 text-slate-700">
            <h1 class="text-3xl font-semibold text-slate-950">Privacy policy</h1>
            <p>QuickSolve stores the account details you submit: name, email, and a hashed password. Profile changes and account deletion are available in settings.</p>
            <p>Payments are handled by Stripe. QuickSolve stores the purchase or subscription status it needs to grant a download or a plan. It does not store card numbers.</p>
            <p>Product description requests may be sent to the configured AI provider when a key is present. Usage events record the tool and the type of action. Guest events store a hash of the session, not the raw IP address, so daily limits can be enforced.</p>
            <p>Downloadable files and saved invoices stay on a private disk. They are not placed in the public web directory.</p>
            <p>Analytics calls that fail are ignored by the page. The tools still work if tracking does not.</p>
            <p>This is a practical summary for the application as built. Replace it with counsel-reviewed policy text before you collect data from the public in production.</p>
        </article>
    </MarketingLayout>
</template>
