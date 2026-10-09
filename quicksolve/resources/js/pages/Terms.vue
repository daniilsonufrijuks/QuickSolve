<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Terms of service" />
    <MarketingLayout>
        <article class="mx-auto max-w-3xl space-y-4 px-4 py-12 text-sm leading-7 text-slate-700">
            <h1 class="text-3xl font-semibold text-slate-950">Terms of service</h1>
            <p>QuickSolve provides software tools and digital files. Calculators are estimates based on the numbers you enter. Invoices are documents you generate. They are not payment processing and they are not tax advice.</p>
            <p>Subscriptions renew monthly until you cancel. Cancellation takes effect at the end of the period already paid. A browser redirect from checkout is not proof of payment.</p>
            <p>Digital downloads are licensed to the account that paid for them. Do not share the file as if you were the publisher. Refunds, if you offer them, should be handled in Stripe and reflected by updating the purchase status.</p>
            <p>Generated descriptions can be wrong or incomplete. Review them before you publish them on a store.</p>
            <p>Replace this summary with the terms you actually intend to enforce before charging live customers.</p>
        </article>
    </MarketingLayout>
</template>
