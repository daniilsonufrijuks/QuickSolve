<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { Link } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const props = defineProps<{
    demoMode: boolean;
    chatUsage: { used: number; limit: number; remaining: number; window: string; plan: string };
}>();

type LinkItem = { label: string; href: string };
type Turn = { role: 'user' | 'assistant'; content: string; links?: LinkItem[]; notice?: string | null; mode?: string };

const open = ref(false);
const pending = ref(false);
const message = ref('');
const error = ref('');
const usage = ref(props.chatUsage);
const log = ref<Turn[]>([
    {
        role: 'assistant',
        content: props.demoMode
            ? 'Ask about invoices, cash landing, unpaid extras, templates, or plans. Demo mode is on because no DeepSeek key is configured — replies are assembled on the server and labeled as a demo.'
            : 'Ask about invoices, cash landing, unpaid extras, templates, or plans. Replies go to DeepSeek on the server. A failed request stays an error and is not replaced with demo text.',
        links: [
            { label: 'Tools', href: '/tools' },
            { label: 'Pricing', href: '/pricing' },
        ],
    },
]);
const thread = ref<HTMLElement | null>(null);

const starters = [
    'How do I make an invoice?',
    'When will a client actually pay?',
    'What does Pro cost?',
];

function toggle() {
    open.value = ! open.value;
}

async function send(text?: string) {
    const body = (text ?? message.value).trim();

    if (body === '' || pending.value) {
        return;
    }

    pending.value = true;
    error.value = '';
    message.value = '';
    log.value.push({ role: 'user', content: body });
    await scrollDown();

    const history = log.value
        .slice(0, -1)
        .filter((turn) => turn.role === 'user' || turn.role === 'assistant')
        .slice(-6)
        .map((turn) => ({ role: turn.role, content: turn.content }));

    const response = await postJson<{
        mode: string;
        notice: string | null;
        reply: string;
        links: LinkItem[];
        usage: { used: number; limit: number; remaining: number; window: string; plan: string };
    }>('/api/v1/home/chat', { message: body, history });

    pending.value = false;

    if (! response.ok || ! response.data) {
        error.value = response.message;
        await scrollDown();
        return;
    }

    usage.value = response.data.usage;
    log.value.push({
        role: 'assistant',
        content: response.data.reply,
        links: response.data.links,
        notice: response.data.notice,
        mode: response.data.mode,
    });
    await scrollDown();
}

async function scrollDown() {
    await nextTick();
    thread.value?.scrollTo({ top: thread.value.scrollHeight, behavior: 'smooth' });
}
</script>

<template>
    <div class="dock">
        <div v-if="open" class="panel" role="dialog" aria-label="QuickSolve assistant">
            <header class="head">
                <div>
                    <p class="kicker">{{ demoMode ? 'Demo assistant' : 'Live DeepSeek assistant' }}</p>
                    <h2 class="title">Ask QuickSolve</h2>
                </div>
                <button type="button" class="icon" aria-label="Close chat" @click="toggle">×</button>
            </header>
            <p class="usage">{{ usage.remaining }} of {{ usage.limit }} replies left this {{ usage.window }}</p>
            <div ref="thread" class="thread">
                <article v-for="(turn, i) in log" :key="i" class="bubble" :class="turn.role">
                    <p>{{ turn.content }}</p>
                    <p v-if="turn.notice" class="notice">{{ turn.notice }}</p>
                    <p v-else-if="turn.mode === 'live'" class="live">Live DeepSeek response.</p>
                    <nav v-if="turn.links?.length" class="links">
                        <Link v-for="item in turn.links" :key="item.href" :href="item.href" class="chip">{{ item.label }}</Link>
                    </nav>
                </article>
                <p v-if="pending" class="typing">Writing a reply…</p>
                <p v-if="error" class="fail">{{ error }}</p>
            </div>
            <div class="starters">
                <button v-for="hint in starters" :key="hint" type="button" class="hint" :disabled="pending" @click="send(hint)">{{ hint }}</button>
            </div>
            <form class="composer" @submit.prevent="send()">
                <label class="sr" for="home-chat-input">Your question</label>
                <input id="home-chat-input" v-model="message" maxlength="500" placeholder="Ask about a tool or plan" :disabled="pending" />
                <Button type="submit" :disabled="pending || message.trim() === ''">{{ pending ? '…' : 'Send' }}</Button>
            </form>
        </div>
        <button v-if="!open" type="button" class="launcher" aria-expanded="false" aria-controls="home-chat" @click="toggle">
            Ask QuickSolve
        </button>
    </div>
</template>

<style scoped>
.dock {
    @apply fixed bottom-4 right-4 z-40 flex flex-col items-end gap-3;
}

.launcher {
    @apply rounded-full bg-blue-600 px-4 py-3 text-sm font-medium text-white shadow-lg shadow-blue-900/30;
    animation: pop 0.5s ease-out both;
}

.panel {
    @apply flex h-[min(32rem,70vh)] w-[min(24rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900;
    animation: rise 0.35s ease-out both;
}

.head {
    @apply flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-800;
}

.kicker {
    @apply text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-300;
}

.title {
    @apply text-base font-semibold;
}

.icon {
    @apply text-2xl leading-none text-slate-500;
}

.usage {
    @apply px-4 pt-2 text-xs text-slate-500;
}

.thread {
    @apply flex-1 space-y-3 overflow-y-auto px-4 py-3;
}

.bubble {
    @apply max-w-[90%] rounded-2xl px-3 py-2 text-sm leading-6;
}

.bubble.user {
    @apply ml-auto bg-blue-600 text-white;
}

.bubble.assistant {
    @apply bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100;
}

.notice,
.live,
.fail {
    @apply mt-2 text-xs leading-5;
}

.notice {
    @apply text-amber-800 dark:text-amber-200;
}

.live {
    @apply text-emerald-700 dark:text-emerald-300;
}

.fail {
    @apply text-red-600;
}

.links {
    @apply mt-2 flex flex-wrap gap-2;
}

.chip {
    @apply rounded-full bg-white px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-100;
}

.typing {
    @apply text-xs text-slate-500;
    animation: pulse 1.4s ease-in-out infinite;
}

.starters {
    @apply flex flex-wrap gap-2 px-4 pb-2;
}

.hint {
    @apply rounded-full border border-slate-200 px-2 py-1 text-xs text-slate-600 dark:border-slate-700 dark:text-slate-300;
}

.composer {
    @apply flex gap-2 border-t border-slate-200 p-3 dark:border-slate-800;
}

.composer input {
    @apply min-w-0 flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950;
}

.sr {
    @apply sr-only;
}

@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pop {
    from {
        opacity: 0;
        transform: scale(0.92);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.5;
    }
    50% {
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .launcher,
    .panel,
    .typing {
        animation: none;
    }
}
</style>
