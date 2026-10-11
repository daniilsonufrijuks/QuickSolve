<script setup lang="ts">
import ErrorState from '@/components/marketing/ErrorState.vue';
import FormField from '@/components/marketing/FormField.vue';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { computed, reactive, ref } from 'vue';

const props = defineProps<{
    usage: { used: number; limit: number; remaining: number; window: string; plan: string } | null;
    demoMode: boolean;
}>();

const templates = [
    { id: 'follow-up', name: 'Follow-up' },
    { id: 'quote', name: 'Quote' },
    { id: 'invoice-reminder', name: 'Invoice reminder' },
    { id: 'intro', name: 'Introduction' },
    { id: 'proposal', name: 'Proposal' },
    { id: 'meeting', name: 'Meeting request' },
    { id: 'thank-you', name: 'Thank you' },
    { id: 'delay', name: 'Delay notice' },
    { id: 'after-meeting', name: 'After meeting' },
    { id: 'apology', name: 'Apology' },
    { id: 'testimonial-request', name: 'Testimonial request' },
    { id: 'referral', name: 'Referral ask' },
    { id: 'onboarding', name: 'Onboarding' },
    { id: 'out-of-office', name: 'Out of office' },
    { id: 'price-increase', name: 'Price increase' },
    { id: 'contract-renewal', name: 'Contract renewal' },
    { id: 'feedback-request', name: 'Feedback request' },
    { id: 'job-application', name: 'Job application' },
    { id: 'partnership', name: 'Partnership' },
] as const;

const panel = ref<'ready' | 'deepseek'>('ready');
const form = reactive({
    template: 'follow-up' as (typeof templates)[number]['id'],
    tone: 'casual' as 'casual' | 'formal',
    from_name: 'Ada North',
    from_role: 'Studio lead',
    from_email: 'ada@example.com',
    to_name: 'Kai',
    company: 'North Studio',
    topic: 'the catalog redesign',
    amount: '€1,200',
    due_date: '22 Oct',
    extra: '',
});
const ai = reactive({
    situation: 'sales',
    tone: 'casual' as 'casual' | 'formal' | 'direct',
    length: 'short',
    facts: '',
    instruction: '',
});
const usageState = ref(props.usage);
const error = ref('');
const copied = ref(false);
const downloading = ref(false);
const pending = ref(false);
const aiDraft = ref<{ mode: string; notice: string | null; subject: string; body: string } | null>(null);
const chat = ref<Array<{ role: 'user' | 'assistant'; content: string }>>([]);

const readyDraft = computed(() => composeReady(form));
const shown = computed(() => (panel.value === 'deepseek' && aiDraft.value ? aiDraft.value : readyDraft.value));

function csrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    const cookie = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');

    return meta?.content || cookie;
}

async function copyDraft() {
    await navigator.clipboard.writeText(`${shown.value.subject}\n\n${shown.value.body}`);
    copied.value = true;
}

function saveBlob(text: string) {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
    link.download = 'email-draft.txt';
    link.click();
    URL.revokeObjectURL(link.href);
}

async function downloadDraft() {
    error.value = '';
    downloading.value = true;
    copied.value = false;

    if (panel.value === 'deepseek' && aiDraft.value) {
        saveBlob(`${aiDraft.value.subject}\n\n${aiDraft.value.body}`);
        downloading.value = false;
        return;
    }

    try {
        const response = await fetch('/tools/email-templates/download', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'text/plain',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-XSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(form),
        });

        if (response.status === 403) {
            error.value = 'The five free Pro uses are finished. Subscribe to keep downloading drafts.';
            return;
        }

        if (!response.ok) {
            error.value = 'The draft could not be exported. Check the required name fields and try again.';
            return;
        }

        saveBlob(await response.text());
    } finally {
        downloading.value = false;
    }
}

async function sendChat() {
    const instruction = ai.instruction.trim();

    if (instruction === '') {
        return;
    }

    pending.value = true;
    error.value = '';
    copied.value = false;

    const history = chat.value.slice(-8);
    const response = await postJson<{
        mode: string;
        notice: string | null;
        subject: string;
        body: string;
        usage: { used: number; limit: number; remaining: number; window: string; plan: string };
    }>('/api/v1/tools/email-templates/generate', {
        topic: form.topic,
        situation: ai.situation,
        tone: ai.tone,
        length: ai.length,
        instruction,
        from_name: form.from_name,
        to_name: form.to_name,
        company: form.company,
        facts: ai.facts,
        history,
    });

    pending.value = false;

    if (response.status === 403) {
        error.value = 'The five free Pro uses are finished. Subscribe to keep using DeepSeek drafts.';
        return;
    }

    if (!response.ok || !response.data) {
        error.value = response.message;
        return;
    }

    chat.value.push({ role: 'user', content: instruction }, { role: 'assistant', content: `${response.data.subject}\n\n${response.data.body}` });
    aiDraft.value = response.data;
    usageState.value = response.data.usage;
    ai.instruction = '';
}

type ReadyForm = {
    template: string;
    tone: 'casual' | 'formal';
    from_name: string;
    from_role: string;
    to_name: string;
    company: string;
    topic: string;
    amount: string;
    due_date: string;
    extra: string;
};

function composeReady(form: ReadyForm): { subject: string; body: string } {
    const to = form.to_name.trim() || 'there';
    const from = form.from_name.trim() || 'Sender';
    const role = form.from_role.trim();
    const company = form.company.trim();
    const topic = form.topic.trim() || 'your request';
    const amount = form.amount.trim();
    const due = form.due_date.trim();
    const note = form.extra.trim();
    const formal = form.tone === 'formal';
    const signOff = role !== '' ? `${from}, ${role}` : from;
    const forCompany = company !== '' ? ` for ${company}` : '';
    const noteBlock = note !== '' ? `${note}\n\n` : '';
    const hello = formal ? `Dear ${to},` : `Hi ${to},`;
    const close = formal ? `Yours sincerely,\n${signOff}` : signOff;
    const amountAt = amount !== '' ? ` at ${amount}` : '';
    const amountFor = amount !== '' ? ` for ${amount}` : '';
    const dueLine = due !== '' ? ` is due ${due}` : ' is still open';
    const figure = amount !== '' ? `. The figure I have in mind is ${amount}` : '';

    const lines: Record<string, { subject: string; body: string }> = {
        quote: {
            subject: `${formal ? 'Quotation' : 'Quote'}${forCompany}: ${topic}`,
            body: formal
                ? `${hello}\n\nPlease find a quotation${forCompany} for ${topic}${amountAt}.\n\n${noteBlock}I would be grateful if you could confirm whether you wish to proceed, or advise any change to the scope.\n\n${close}`
                : `${hello}\n\nHere is a quote${forCompany} for ${topic}${amountAt}.\n\n${noteBlock}This is a draft for you to send from your own mailbox. Reply if you want to go ahead or change the scope.\n\n${close}`,
        },
        apology: {
            subject: `${formal ? 'Apology regarding ' : 'Sorry about '}${topic}`,
            body: formal
                ? `${hello}\n\nPlease accept my apology in relation to ${topic}${forCompany}.\n\n${noteBlock}I take responsibility for the inconvenience and will set out the next step as soon as I can.\n\n${close}`
                : `${hello}\n\nSorry about ${topic}${forCompany}.\n\n${noteBlock}That is on me. I will send the next step as soon as I have it.\n\n${close}`,
        },
        'testimonial-request': {
            subject: formal ? 'Request for a short testimonial' : 'Could you share a short testimonial?',
            body: formal
                ? `${hello}\n\nIf you were satisfied with ${topic}${forCompany}, I would be grateful for a short written comment we may quote.\n\n${noteBlock}There is no obligation to reply.\n\n${close}`
                : `${hello}\n\nIf ${topic}${forCompany} was useful, would you mind a short testimonial I can quote?\n\n${noteBlock}No pressure if you would rather not.\n\n${close}`,
        },
        referral: {
            subject: formal ? 'Introduction to a possible contact' : 'Anyone I should meet?',
            body: formal
                ? `${hello}\n\nI am writing to ask whether you know anyone who may need help with ${topic}${forCompany}.\n\n${noteBlock}A name and permission to mention you would be enough. Thank you for considering this.\n\n${close}`
                : `${hello}\n\nDo you know anyone who might need help with ${topic}${forCompany}?\n\n${noteBlock}A name and an OK to mention you is plenty. Thanks either way.\n\n${close}`,
        },
        onboarding: {
            subject: `${formal ? 'Welcome and next steps: ' : 'Welcome — next steps for '}${topic}`,
            body: formal
                ? `${hello}\n\nWelcome. This note sets out the first steps for ${topic}${forCompany}.\n\n${noteBlock}Please reply with any information I should have before we begin.\n\n${close}`
                : `${hello}\n\nWelcome. Here is how we start ${topic}${forCompany}.\n\n${noteBlock}Reply with anything I should know before we kick off.\n\n${close}`,
        },
        'out-of-office': {
            subject: formal ? 'Absence notice' : 'Out of office',
            body: formal
                ? `${hello}\n\nI will be unavailable${due !== '' ? ` until ${due}` : ''} and will not be able to attend to ${topic} until my return.\n\n${noteBlock}Urgent matters may need to wait, or you may write again after that date.\n\n${close}`
                : `${hello}\n\nI am away${due !== '' ? ` until ${due}` : ''} and cannot pick up ${topic} until I am back.\n\n${noteBlock}If it can wait, I will reply after that. If not, send it again then.\n\n${close}`,
        },
        'price-increase': {
            subject: `${formal ? 'Notice of a fee change: ' : 'Fee update: '}${topic}`,
            body: formal
                ? `${hello}\n\nI am writing to advise a change in fees for ${topic}${forCompany}${amount !== '' ? ` to ${amount}` : ''}${due !== '' ? `, effective ${due}` : ''}.\n\n${noteBlock}Please let me know if you wish to discuss the change before it takes effect.\n\n${close}`
                : `${hello}\n\nA heads-up: the fee for ${topic}${forCompany}${amount !== '' ? ` moves to ${amount}` : ' is changing'}${due !== '' ? ` from ${due}` : ''}.\n\n${noteBlock}Happy to talk it through before it applies.\n\n${close}`,
        },
        'contract-renewal': {
            subject: `${formal ? 'Renewal of our arrangement: ' : 'Shall we renew? '}${topic}`,
            body: formal
                ? `${hello}\n\nOur current arrangement for ${topic}${forCompany}${due !== '' ? ` is due for review on ${due}` : ' is approaching renewal'}.\n\n${noteBlock}Please confirm whether you wish to continue, or outline any change you require.\n\n${close}`
                : `${hello}\n\nOur work on ${topic}${forCompany}${due !== '' ? ` comes up for renewal on ${due}` : ' is coming up for renewal'}.\n\n${noteBlock}Want to continue as-is, or change something?\n\n${close}`,
        },
        'feedback-request': {
            subject: formal ? `Request for feedback on ${topic}` : `Feedback on ${topic}?`,
            body: formal
                ? `${hello}\n\nI would value a brief comment on ${topic}${forCompany}, including anything that did not work as expected.\n\n${noteBlock}A few sentences are sufficient.\n\n${close}`
                : `${hello}\n\nCould you share a short note on ${topic}${forCompany}? What worked, and what did not?\n\n${noteBlock}A couple of sentences is plenty.\n\n${close}`,
        },
        'job-application': {
            subject: `${formal ? 'Application: ' : 'Applying for '}${topic}`,
            body: formal
                ? `${hello}\n\nI wish to apply for ${topic}${company !== '' ? ` at ${company}` : ''}. My name is ${from}${role !== '' ? `, currently ${role}` : ''}.\n\n${noteBlock}I would welcome the opportunity to discuss the role at your convenience.\n\n${close}`
                : `${hello}\n\nI am applying for ${topic}${company !== '' ? ` at ${company}` : ''}. I am ${from}${role !== '' ? `, ${role}` : ''}.\n\n${noteBlock}Happy to talk whenever suits you.\n\n${close}`,
        },
        partnership: {
            subject: `${formal ? 'Proposal for collaboration: ' : 'Collaboration idea: '}${topic}`,
            body: formal
                ? `${hello}\n\nI am writing to propose a collaboration on ${topic}${forCompany}.\n\n${noteBlock}If this is of interest, I can send a short outline of roles and timing.\n\n${close}`
                : `${hello}\n\nWould you be open to collaborating on ${topic}${forCompany}?\n\n${noteBlock}If yes, I can send a short outline of who does what.\n\n${close}`,
        },
    };

    if (form.template === 'invoice-reminder') {
        return {
            subject: `${formal ? 'Outstanding invoice' : 'Invoice reminder'}${forCompany}${due !== '' ? ` due ${due}` : ''}`,
            body: formal
                ? `${hello}\n\nI am writing regarding an outstanding invoice${forCompany}${amountFor} which${dueLine}.\n\n${noteBlock}If payment has already been made, please disregard this message.\n\n${close}`
                : `${hello}\n\nThis is a reminder that an invoice${forCompany}${amountFor} which${dueLine}.\n\n${noteBlock}If you have already paid, you can ignore this note.\n\n${close}`,
        };
    }

    if (form.template === 'intro') {
        return {
            subject: formal ? `Introduction: ${from}` : `Introduction from ${from}`,
            body: formal
                ? `${hello}\n\nAllow me to introduce myself. My name is ${from}${role !== '' ? `, ${role}` : ''}${company !== '' ? `, representing ${company}` : ''}. I am writing in connection with ${topic}.\n\n${noteBlock}I would welcome the opportunity to outline a brief next step, should that be of interest.\n\n${close}`
                : `${hello}\n\nI am ${from}${role !== '' ? `, ${role}` : ''}${company !== '' ? ` working with ${company}` : ''}. I am writing about ${topic}.\n\n${noteBlock}If this is useful, I can send a short next step. If not, no reply is needed.\n\n${close}`,
        };
    }

    if (form.template === 'proposal') {
        return {
            subject: `${formal ? 'Proposal of work' : 'Proposal'}${forCompany}: ${topic}`,
            body: formal
                ? `${hello}\n\nPlease find a proposal${forCompany} covering ${topic}${figure}.\n\n${noteBlock}I remain at your disposal to adjust the scope or the commercial terms.\n\n${close}`
                : `${hello}\n\nI have put together a proposal${forCompany} covering ${topic}${figure}.\n\n${noteBlock}This is a starting point. Tell me what to keep, drop, or price differently.\n\n${close}`,
        };
    }

    if (form.template === 'meeting') {
        return {
            subject: `${formal ? 'Request for a meeting: ' : 'Meeting request: '}${topic}`,
            body: formal
                ? `${hello}\n\nI should be grateful if we could arrange a short meeting regarding ${topic}${forCompany}.\n\n${noteBlock}Please let me know a time that would be convenient, or propose an alternative.\n\n${close}`
                : `${hello}\n\nCould we schedule a short call about ${topic}${forCompany}?\n\n${noteBlock}Reply with a time that works, or suggest another day.\n\n${close}`,
        };
    }

    if (form.template === 'thank-you') {
        return {
            subject: formal ? `Thank you${forCompany}` : `Thanks${forCompany}`,
            body: formal
                ? `${hello}\n\nThank you for your time regarding ${topic}${forCompany}.\n\n${noteBlock}I appreciate your consideration and remain available should you require anything further.\n\n${close}`
                : `${hello}\n\nThanks for taking the time on ${topic}${forCompany}.\n\n${noteBlock}Happy to follow up with whatever you need next.\n\n${close}`,
        };
    }

    if (form.template === 'delay') {
        return {
            subject: `${formal ? 'Update on timing: ' : 'Timing update: '}${topic}`,
            body: formal
                ? `${hello}\n\nI am writing to advise a change in timing for ${topic}${forCompany}${due !== '' ? `. The revised date I can currently offer is ${due}` : ''}.\n\n${noteBlock}Please accept my apologies for the inconvenience. I will confirm as soon as the schedule is settled.\n\n${close}`
                : `${hello}\n\nA quick update on timing for ${topic}${forCompany}${due !== '' ? `. I can now offer ${due}` : ''}.\n\n${noteBlock}Sorry for the shift. I will confirm as soon as the date is firm.\n\n${close}`,
        };
    }

    if (form.template === 'after-meeting') {
        return {
            subject: `${formal ? 'Notes following our discussion: ' : 'After our chat: '}${topic}`,
            body: formal
                ? `${hello}\n\nThank you for the discussion regarding ${topic}${forCompany}.\n\n${noteBlock}I will proceed as agreed and write again if any point requires clarification.\n\n${close}`
                : `${hello}\n\nThanks for the conversation about ${topic}${forCompany}.\n\n${noteBlock}I will take the next step we agreed and ping you if anything is unclear.\n\n${close}`,
        };
    }

    return (
        lines[form.template] ?? {
            subject: formal ? `Further to ${topic}` : `Following up on ${topic}`,
            body: formal
                ? `${hello}\n\nI am writing further to ${topic}${forCompany}.\n\n${noteBlock}I would be grateful for a brief indication of whether this remains of interest, even if the timing is not convenient at present.\n\n${close}`
                : `${hello}\n\nI wanted to check whether you had a chance to look at ${topic}${forCompany}.\n\n${noteBlock}If now is not the right time, a short reply is enough.\n\n${close}`,
        }
    );
}
</script>

<template>
    <div class="wrap">
        <div class="switch">
            <Button type="button" :variant="panel === 'ready' ? 'default' : 'outline'" @click="panel = 'ready'">Ready-made</Button>
            <Button type="button" :variant="panel === 'deepseek' ? 'default' : 'outline'" @click="panel = 'deepseek'">DeepSeek chat</Button>
        </div>
        <div class="layout">
            <form v-if="panel === 'ready'" class="panel" @submit.prevent="downloadDraft">
                <FormField label="Template">
                    <div class="templates">
                        <label v-for="item in templates" :key="item.id" class="choice">
                            <input v-model="form.template" type="radio" :value="item.id" />
                            {{ item.name }}
                        </label>
                    </div>
                </FormField>
                <FormField label="Tone">
                    <div class="templates">
                        <label class="choice"><input v-model="form.tone" type="radio" value="casual" /> Casual</label>
                        <label class="choice"><input v-model="form.tone" type="radio" value="formal" /> Formal</label>
                    </div>
                </FormField>
                <FormField label="Your name"><input v-model="form.from_name" class="field" /></FormField>
                <FormField label="Your role"><input v-model="form.from_role" class="field" /></FormField>
                <FormField label="Your email"><input v-model="form.from_email" class="field" type="email" /></FormField>
                <FormField label="Recipient name"><input v-model="form.to_name" class="field" /></FormField>
                <FormField label="Company"><input v-model="form.company" class="field" /></FormField>
                <FormField label="Topic"><input v-model="form.topic" class="field" /></FormField>
                <FormField label="Amount"><input v-model="form.amount" class="field" /></FormField>
                <FormField label="Due date"><input v-model="form.due_date" class="field" /></FormField>
                <FormField label="Extra note"><textarea v-model="form.extra" class="field" rows="3" /></FormField>
                <p v-if="error && panel === 'ready'" class="error">{{ error }}</p>
                <div class="actions">
                    <Button type="button" variant="outline" @click="copyDraft">{{ copied ? 'Copied' : 'Copy draft' }}</Button>
                    <Button type="submit" :disabled="downloading">{{ downloading ? 'Preparing…' : 'Download .txt' }}</Button>
                </div>
            </form>
            <div v-else class="panel">
                <p v-if="demoMode" class="demo">Demo mode is on because no DeepSeek API key is configured. Results are assembled from your settings and labeled as a demo.</p>
                <p v-else class="live">Requests are sent to DeepSeek on the server. A failed request stays an error and is not replaced with demo text.</p>
                <p v-if="usageState" class="meta">{{ usageState.remaining }} of {{ usageState.limit }} generations left this {{ usageState.window }}.</p>
                <FormField label="Topic"><input v-model="form.topic" class="field" /></FormField>
                <FormField label="Situation">
                    <select v-model="ai.situation" class="field">
                        <option value="sales">Sales</option>
                        <option value="support">Support</option>
                        <option value="hiring">Hiring</option>
                        <option value="operations">Operations</option>
                        <option value="partnership">Partnership</option>
                        <option value="billing">Billing</option>
                        <option value="custom">Custom</option>
                    </select>
                </FormField>
                <div class="row">
                    <FormField label="Tone">
                        <select v-model="ai.tone" class="field">
                            <option value="casual">Casual</option>
                            <option value="formal">Formal</option>
                            <option value="direct">Direct</option>
                        </select>
                    </FormField>
                    <FormField label="Length">
                        <select v-model="ai.length" class="field">
                            <option value="short">Short</option>
                            <option value="medium">Medium</option>
                        </select>
                    </FormField>
                </div>
                <FormField label="Your name"><input v-model="form.from_name" class="field" /></FormField>
                <FormField label="Recipient name"><input v-model="form.to_name" class="field" /></FormField>
                <FormField label="Company"><input v-model="form.company" class="field" /></FormField>
                <FormField label="Facts to include" hint="Only facts you want in the draft. The model should not invent the rest.">
                    <textarea v-model="ai.facts" class="field" rows="3" />
                </FormField>
                <div class="chat" aria-live="polite">
                    <p v-if="chat.length === 0" class="meta">Describe the email you need. Follow-up messages revise the same draft.</p>
                    <article v-for="(turn, index) in chat" :key="index" class="turn" :class="turn.role">
                        <p class="label">{{ turn.role === 'user' ? 'You' : 'Draft' }}</p>
                        <pre class="body">{{ turn.content }}</pre>
                    </article>
                </div>
                <FormField label="Message">
                    <textarea v-model="ai.instruction" class="field" rows="3" placeholder="Write a follow-up about the catalog redesign" />
                </FormField>
                <ErrorState v-if="error && panel === 'deepseek'" :message="error" />
                <div class="actions">
                    <Button type="button" :disabled="pending" @click="sendChat">{{ pending ? 'Working…' : 'Send to DeepSeek' }}</Button>
                    <Button type="button" variant="outline" :disabled="!aiDraft" @click="copyDraft">{{ copied ? 'Copied' : 'Copy draft' }}</Button>
                    <Button type="button" variant="outline" :disabled="!aiDraft || downloading" @click="downloadDraft">Download .txt</Button>
                </div>
            </div>
            <section class="preview" aria-label="Email preview">
                <p class="meta">QuickSolve does not send this message. Paste it into your own mail app.</p>
                <p v-if="panel === 'deepseek' && aiDraft?.mode === 'demo'" class="demo">{{ aiDraft.notice }}</p>
                <p v-else-if="panel === 'deepseek' && aiDraft?.mode === 'live'" class="ok">Live DeepSeek response.</p>
                <p class="subject">{{ shown.subject }}</p>
                <pre class="body">{{ shown.body }}</pre>
            </section>
        </div>
    </div>
</template>

<style scoped>
.wrap {
    @apply space-y-4;
}

.switch,
.actions,
.row {
    @apply flex flex-wrap gap-2;
}

.layout {
    @apply grid gap-6 lg:grid-cols-2;
}

.panel,
.preview {
    @apply space-y-3 rounded-xl border p-5;
}

.field {
    @apply w-full rounded-lg border px-3 py-2;
}

.templates {
    @apply flex flex-wrap gap-3 text-sm;
}

.choice {
    @apply flex items-center gap-2;
}

.error {
    @apply text-sm text-red-700;
}

.preview {
    @apply bg-white text-slate-900;
}

.meta {
    @apply text-sm text-slate-600;
}

.subject {
    @apply border-b pb-2 font-medium;
}

.body {
    @apply whitespace-pre-wrap font-sans text-sm leading-6;
}

.demo {
    @apply rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-950;
}

.live {
    @apply rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-950;
}

.ok {
    @apply rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-900;
}

.chat {
    @apply max-h-64 space-y-3 overflow-y-auto rounded-lg border p-3;
}

.turn .label {
    @apply text-xs font-semibold uppercase tracking-wide text-slate-500;
}

.turn.user {
    @apply rounded-md bg-slate-50 p-2;
}
</style>
