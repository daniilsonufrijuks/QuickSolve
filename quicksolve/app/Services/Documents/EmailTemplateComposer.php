<?php

namespace App\Services\Documents;

class EmailTemplateComposer
{
    /**
     * @var list<string>
     */
    public const IDS = [
        'follow-up',
        'quote',
        'invoice-reminder',
        'intro',
        'proposal',
        'meeting',
        'thank-you',
        'delay',
        'after-meeting',
        'apology',
        'testimonial-request',
        'referral',
        'onboarding',
        'out-of-office',
        'price-increase',
        'contract-renewal',
        'feedback-request',
        'job-application',
        'partnership',
    ];

    /**
     * @param  array<string, mixed>  $input
     * @return array{subject: string, body: string}
     */
    public function compose(array $input): array
    {
        $to = $input['to_name'];
        $from = $input['from_name'];
        $role = trim((string) ($input['from_role'] ?? ''));
        $company = trim((string) ($input['company'] ?? ''));
        $topic = trim((string) ($input['topic'] ?? '')) ?: 'your request';
        $amount = trim((string) ($input['amount'] ?? ''));
        $due = trim((string) ($input['due_date'] ?? ''));
        $note = trim((string) ($input['extra'] ?? ''));
        $formal = ($input['tone'] ?? 'casual') === 'formal';
        $signOff = $role !== '' ? $from.', '.$role : $from;
        $forCompany = $company !== '' ? ' for '.$company : '';
        $noteBlock = $note !== '' ? $note."\n\n" : '';
        $hello = $formal ? "Dear {$to}," : "Hi {$to},";
        $close = $formal ? "Yours sincerely,\n{$signOff}" : $signOff;
        $amountAt = $amount !== '' ? " at {$amount}" : '';
        $amountFor = $amount !== '' ? " for {$amount}" : '';
        $dueLine = $due !== '' ? " is due {$due}" : ' is still open';

        return match ($input['template']) {
            'quote' => [
                'subject' => ($formal ? 'Quotation' : 'Quote').$forCompany.': '.$topic,
                'body' => $formal
                    ? "{$hello}\n\nPlease find a quotation{$forCompany} for {$topic}{$amountAt}.\n\n{$noteBlock}I would be grateful if you could confirm whether you wish to proceed, or advise any change to the scope.\n\n{$close}"
                    : "{$hello}\n\nHere is a quote{$forCompany} for {$topic}{$amountAt}.\n\n{$noteBlock}This is a draft for you to send from your own mailbox. Reply if you want to go ahead or change the scope.\n\n{$close}",
            ],
            'invoice-reminder' => [
                'subject' => ($formal ? 'Outstanding invoice' : 'Invoice reminder').$forCompany.($due !== '' ? ' due '.$due : ''),
                'body' => $formal
                    ? "{$hello}\n\nI am writing regarding an outstanding invoice{$forCompany}{$amountFor} which{$dueLine}.\n\n{$noteBlock}If payment has already been made, please disregard this message.\n\n{$close}"
                    : "{$hello}\n\nThis is a reminder that an invoice{$forCompany}{$amountFor} which{$dueLine}.\n\n{$noteBlock}If you have already paid, you can ignore this note.\n\n{$close}",
            ],
            'intro' => [
                'subject' => $formal ? 'Introduction: '.$from : 'Introduction from '.$from,
                'body' => $formal
                    ? "{$hello}\n\nAllow me to introduce myself. My name is {$from}".($role !== '' ? ', '.$role : '').($company !== '' ? ", representing {$company}" : '').". I am writing in connection with {$topic}.\n\n{$noteBlock}I would welcome the opportunity to outline a brief next step, should that be of interest.\n\n{$close}"
                    : "{$hello}\n\nI am {$from}".($role !== '' ? ', '.$role : '').($company !== '' ? " working with {$company}" : '').". I am writing about {$topic}.\n\n{$noteBlock}If this is useful, I can send a short next step. If not, no reply is needed.\n\n{$close}",
            ],
            'proposal' => [
                'subject' => ($formal ? 'Proposal of work' : 'Proposal').$forCompany.': '.$topic,
                'body' => $formal
                    ? "{$hello}\n\nPlease find a proposal{$forCompany} covering {$topic}".($amount !== '' ? ". The figure I have in mind is {$amount}" : '').".\n\n{$noteBlock}I remain at your disposal to adjust the scope or the commercial terms.\n\n{$close}"
                    : "{$hello}\n\nI have put together a proposal{$forCompany} covering {$topic}".($amount !== '' ? ". The figure I have in mind is {$amount}" : '').".\n\n{$noteBlock}This is a starting point. Tell me what to keep, drop, or price differently.\n\n{$close}",
            ],
            'meeting' => [
                'subject' => ($formal ? 'Request for a meeting: ' : 'Meeting request: ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nI should be grateful if we could arrange a short meeting regarding {$topic}{$forCompany}.\n\n{$noteBlock}Please let me know a time that would be convenient, or propose an alternative.\n\n{$close}"
                    : "{$hello}\n\nCould we schedule a short call about {$topic}{$forCompany}?\n\n{$noteBlock}Reply with a time that works, or suggest another day.\n\n{$close}",
            ],
            'thank-you' => [
                'subject' => $formal ? 'Thank you'.$forCompany : 'Thanks'.$forCompany,
                'body' => $formal
                    ? "{$hello}\n\nThank you for your time regarding {$topic}{$forCompany}.\n\n{$noteBlock}I appreciate your consideration and remain available should you require anything further.\n\n{$close}"
                    : "{$hello}\n\nThanks for taking the time on {$topic}{$forCompany}.\n\n{$noteBlock}Happy to follow up with whatever you need next.\n\n{$close}",
            ],
            'delay' => [
                'subject' => ($formal ? 'Update on timing: ' : 'Timing update: ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nI am writing to advise a change in timing for {$topic}{$forCompany}".($due !== '' ? ". The revised date I can currently offer is {$due}" : '').".\n\n{$noteBlock}Please accept my apologies for the inconvenience. I will confirm as soon as the schedule is settled.\n\n{$close}"
                    : "{$hello}\n\nA quick update on timing for {$topic}{$forCompany}".($due !== '' ? ". I can now offer {$due}" : '').".\n\n{$noteBlock}Sorry for the shift. I will confirm as soon as the date is firm.\n\n{$close}",
            ],
            'after-meeting' => [
                'subject' => ($formal ? 'Notes following our discussion: ' : 'After our chat: ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nThank you for the discussion regarding {$topic}{$forCompany}.\n\n{$noteBlock}I will proceed as agreed and write again if any point requires clarification.\n\n{$close}"
                    : "{$hello}\n\nThanks for the conversation about {$topic}{$forCompany}.\n\n{$noteBlock}I will take the next step we agreed and ping you if anything is unclear.\n\n{$close}",
            ],
            'apology' => [
                'subject' => ($formal ? 'Apology regarding ' : 'Sorry about ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nPlease accept my apology in relation to {$topic}{$forCompany}.\n\n{$noteBlock}I take responsibility for the inconvenience and will set out the next step as soon as I can.\n\n{$close}"
                    : "{$hello}\n\nSorry about {$topic}{$forCompany}.\n\n{$noteBlock}That is on me. I will send the next step as soon as I have it.\n\n{$close}",
            ],
            'testimonial-request' => [
                'subject' => $formal ? 'Request for a short testimonial' : 'Could you share a short testimonial?',
                'body' => $formal
                    ? "{$hello}\n\nIf you were satisfied with {$topic}{$forCompany}, I would be grateful for a short written comment we may quote.\n\n{$noteBlock}There is no obligation to reply.\n\n{$close}"
                    : "{$hello}\n\nIf {$topic}{$forCompany} was useful, would you mind a short testimonial I can quote?\n\n{$noteBlock}No pressure if you would rather not.\n\n{$close}",
            ],
            'referral' => [
                'subject' => $formal ? 'Introduction to a possible contact' : 'Anyone I should meet?',
                'body' => $formal
                    ? "{$hello}\n\nI am writing to ask whether you know anyone who may need help with {$topic}{$forCompany}.\n\n{$noteBlock}A name and permission to mention you would be enough. Thank you for considering this.\n\n{$close}"
                    : "{$hello}\n\nDo you know anyone who might need help with {$topic}{$forCompany}?\n\n{$noteBlock}A name and an OK to mention you is plenty. Thanks either way.\n\n{$close}",
            ],
            'onboarding' => [
                'subject' => ($formal ? 'Welcome and next steps: ' : 'Welcome — next steps for ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nWelcome. This note sets out the first steps for {$topic}{$forCompany}.\n\n{$noteBlock}Please reply with any information I should have before we begin.\n\n{$close}"
                    : "{$hello}\n\nWelcome. Here is how we start {$topic}{$forCompany}.\n\n{$noteBlock}Reply with anything I should know before we kick off.\n\n{$close}",
            ],
            'out-of-office' => [
                'subject' => $formal ? 'Absence notice' : 'Out of office',
                'body' => $formal
                    ? "{$hello}\n\nI will be unavailable".($due !== '' ? " until {$due}" : '')." and will not be able to attend to {$topic} until my return.\n\n{$noteBlock}Urgent matters may need to wait, or you may write again after that date.\n\n{$close}"
                    : "{$hello}\n\nI am away".($due !== '' ? " until {$due}" : '')." and cannot pick up {$topic} until I am back.\n\n{$noteBlock}If it can wait, I will reply after that. If not, send it again then.\n\n{$close}",
            ],
            'price-increase' => [
                'subject' => ($formal ? 'Notice of a fee change: ' : 'Fee update: ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nI am writing to advise a change in fees for {$topic}{$forCompany}".($amount !== '' ? " to {$amount}" : '').($due !== '' ? ", effective {$due}" : '').".\n\n{$noteBlock}Please let me know if you wish to discuss the change before it takes effect.\n\n{$close}"
                    : "{$hello}\n\nA heads-up: the fee for {$topic}{$forCompany}".($amount !== '' ? " moves to {$amount}" : ' is changing').($due !== '' ? " from {$due}" : '').".\n\n{$noteBlock}Happy to talk it through before it applies.\n\n{$close}",
            ],
            'contract-renewal' => [
                'subject' => ($formal ? 'Renewal of our arrangement: ' : 'Shall we renew? ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nOur current arrangement for {$topic}{$forCompany}".($due !== '' ? " is due for review on {$due}" : ' is approaching renewal').".\n\n{$noteBlock}Please confirm whether you wish to continue, or outline any change you require.\n\n{$close}"
                    : "{$hello}\n\nOur work on {$topic}{$forCompany}".($due !== '' ? " comes up for renewal on {$due}" : ' is coming up for renewal').".\n\n{$noteBlock}Want to continue as-is, or change something?\n\n{$close}",
            ],
            'feedback-request' => [
                'subject' => $formal ? 'Request for feedback on '.$topic : 'Feedback on '.$topic.'?',
                'body' => $formal
                    ? "{$hello}\n\nI would value a brief comment on {$topic}{$forCompany}, including anything that did not work as expected.\n\n{$noteBlock}A few sentences are sufficient.\n\n{$close}"
                    : "{$hello}\n\nCould you share a short note on {$topic}{$forCompany}? What worked, and what did not?\n\n{$noteBlock}A couple of sentences is plenty.\n\n{$close}",
            ],
            'job-application' => [
                'subject' => ($formal ? 'Application: ' : 'Applying for ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nI wish to apply for {$topic}".($company !== '' ? " at {$company}" : '').". My name is {$from}".($role !== '' ? ', currently '.$role : '').".\n\n{$noteBlock}I would welcome the opportunity to discuss the role at your convenience.\n\n{$close}"
                    : "{$hello}\n\nI am applying for {$topic}".($company !== '' ? " at {$company}" : '').". I am {$from}".($role !== '' ? ', '.$role : '').".\n\n{$noteBlock}Happy to talk whenever suits you.\n\n{$close}",
            ],
            'partnership' => [
                'subject' => ($formal ? 'Proposal for collaboration: ' : 'Collaboration idea: ').$topic,
                'body' => $formal
                    ? "{$hello}\n\nI am writing to propose a collaboration on {$topic}{$forCompany}.\n\n{$noteBlock}If this is of interest, I can send a short outline of roles and timing.\n\n{$close}"
                    : "{$hello}\n\nWould you be open to collaborating on {$topic}{$forCompany}?\n\n{$noteBlock}If yes, I can send a short outline of who does what.\n\n{$close}",
            ],
            default => [
                'subject' => $formal ? 'Further to '.$topic : 'Following up on '.$topic,
                'body' => $formal
                    ? "{$hello}\n\nI am writing further to {$topic}{$forCompany}.\n\n{$noteBlock}I would be grateful for a brief indication of whether this remains of interest, even if the timing is not convenient at present.\n\n{$close}"
                    : "{$hello}\n\nI wanted to check whether you had a chance to look at {$topic}{$forCompany}.\n\n{$noteBlock}If now is not the right time, a short reply is enough.\n\n{$close}",
            ],
        };
    }
}
