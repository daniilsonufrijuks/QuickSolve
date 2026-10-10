<script setup lang="ts">
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { qrPayload, type QrInput } from '@/lib/qr';
import { Link } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps<{ branding: boolean }>();

const form = reactive<QrInput>({
    type: 'url',
    url: 'https://example.com',
    wifi_ssid: '',
    wifi_password: '',
    wifi_security: 'WPA',
    wifi_hidden: false,
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    organization: '',
    text: '',
});

const foreground = ref('#000000');
const background = ref('#ffffff');
const pngUrl = ref('');
const error = ref('');

const colors = computed(() => {
    if (!props.branding) {
        return { dark: '#000000', light: '#ffffff' };
    }

    return {
        dark: /^#[0-9a-fA-F]{6}$/.test(foreground.value) ? foreground.value : '#000000',
        light: /^#[0-9a-fA-F]{6}$/.test(background.value) ? background.value : '#ffffff',
    };
});

const payload = computed(() => qrPayload(form));

async function render() {
    if (typeof payload.value !== 'string') {
        pngUrl.value = '';
        error.value = payload.value.error;
        return;
    }

    error.value = '';

    try {
        pngUrl.value = await QRCode.toDataURL(payload.value, {
            errorCorrectionLevel: 'M',
            margin: 2,
            width: 512,
            color: colors.value,
        });
    } catch {
        pngUrl.value = '';
        error.value = 'That content is too long for a reliable QR code. Shorten it and try again.';
    }
}

watch([payload, colors], render, { immediate: true });

function downloadPng() {
    if (!pngUrl.value) {
        return;
    }

    const link = document.createElement('a');
    link.href = pngUrl.value;
    link.download = 'quicksolve-qr.png';
    link.click();
}

async function downloadSvg() {
    if (typeof payload.value !== 'string') {
        return;
    }

    const svg = await QRCode.toString(payload.value, {
        type: 'svg',
        errorCorrectionLevel: 'M',
        margin: 2,
        color: colors.value,
    });
    const blob = new Blob([svg], { type: 'image/svg+xml' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'quicksolve-qr.svg';
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>

<template>
    <div class="s16x0eqh">
        <form class="s1r0jak5" @submit.prevent>
            <FormField label="Content">
                <select v-model="form.type" class="s1wl1yqe">
                    <option value="url">URL</option>
                    <option value="wifi">Wi-Fi</option>
                    <option value="contact">Contact</option>
                    <option value="text">Plain text</option>
                </select>
            </FormField>
            <FormField v-if="form.type === 'url'" label="URL">
                <input v-model="form.url" type="url" class="s1wl1yqe" placeholder="https://example.com" />
            </FormField>
            <template v-else-if="form.type === 'wifi'">
                <FormField label="Network name">
                    <input v-model="form.wifi_ssid" class="s1wl1yqe" autocomplete="off" />
                </FormField>
                <FormField label="Security">
                    <select v-model="form.wifi_security" class="s1wl1yqe">
                        <option value="WPA">WPA/WPA2</option>
                        <option value="WEP">WEP</option>
                        <option value="NOPASS">No password</option>
                    </select>
                </FormField>
                <FormField v-if="form.wifi_security !== 'NOPASS'" label="Password" hint="The password stays in your browser. It is not sent to QuickSolve.">
                    <input v-model="form.wifi_password" type="password" class="s1wl1yqe" autocomplete="new-password" />
                </FormField>
                <label class="s1mng9fi"><input v-model="form.wifi_hidden" type="checkbox" /> Hidden network</label>
            </template>
            <template v-else-if="form.type === 'contact'">
                <div class="grid gap-4 sm:grid-cols-2">
                    <FormField label="First name"><input v-model="form.first_name" class="w-full rounded-lg border px-3 py-2" /></FormField>
                    <FormField label="Last name"><input v-model="form.last_name" class="w-full rounded-lg border px-3 py-2" /></FormField>
                    <FormField label="Phone"><input v-model="form.phone" class="w-full rounded-lg border px-3 py-2" /></FormField>
                    <FormField label="Email"><input v-model="form.email" type="email" class="w-full rounded-lg border px-3 py-2" /></FormField>
                </div>
                <FormField label="Organization"><input v-model="form.organization" class="w-full rounded-lg border px-3 py-2" /></FormField>
            </template>
            <FormField v-else label="Text" hint="Up to 500 characters.">
                <textarea v-model="form.text" rows="4" class="w-full rounded-lg border px-3 py-2" />
            </FormField>
            <div v-if="branding" class="grid gap-4 sm:grid-cols-2">
                <FormField label="Foreground" hint="Pro branding. The code itself is still generated in your browser.">
                    <input v-model="foreground" type="color" class="h-10 w-full" />
                </FormField>
                <FormField label="Background">
                    <input v-model="background" type="color" class="h-10 w-full" />
                </FormField>
            </div>
            <p v-else class="text-sm leading-6">
                Custom colors are included with Pro and Business. Free codes use black on white.
                <Link href="/pricing" class="font-medium text-blue-800 underline dark:text-blue-300">View plans</Link>
            </p>
            <div class="flex flex-wrap gap-2">
                <Button type="button" variant="outline" :disabled="!pngUrl" @click="downloadPng">Download PNG</Button>
                <Button type="button" variant="outline" :disabled="typeof payload !== 'string'" @click="downloadSvg">Download SVG</Button>
            </div>
        </form>
        <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" aria-live="polite">
            <h2 class="text-lg font-semibold">Preview</h2>
            <p v-if="error" class="mt-4 text-sm text-red-700 dark:text-red-300">{{ error }}</p>
            <img v-else-if="pngUrl" :src="pngUrl" alt="QR code preview" class="mt-4 w-full max-w-xs bg-white p-3" />
        </section>
    </div>
</template>

<style scoped>
.s16x0eqh {
    @apply grid gap-6 lg:grid-cols-[1.1fr_0.9fr];
}

.s1r0jak5 {
    @apply space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900;
}

.s1wl1yqe {
    @apply w-full rounded-lg border px-3 py-2;
}

.s1mng9fi {
    @apply flex items-center gap-2 text-sm;
}
</style>
