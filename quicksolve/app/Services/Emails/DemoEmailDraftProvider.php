<?php

namespace App\Services\Emails;

class DemoEmailDraftProvider implements EmailDraftProvider
{
    public function generate(array $input): array
    {
        $to = trim((string) $input['to_name']);
        $from = trim((string) $input['from_name']);
        $topic = trim((string) $input['topic']);
        $instruction = trim((string) $input['instruction']);
        $facts = trim((string) ($input['facts'] ?? ''));
        $company = trim((string) ($input['company'] ?? ''));
        $situation = (string) $input['situation'];
        $formal = ($input['tone'] ?? '') === 'formal';
        $hello = $formal ? "Dear {$to}," : "Hi {$to},";
        $close = $formal ? "Yours sincerely,\n{$from}" : $from;
        $forCompany = $company !== '' ? " ({$company})" : '';
        $factLine = $facts !== '' ? $facts."\n\n" : '';

        $lead = match ($situation) {
            'sales' => $formal ? "I am writing about {$topic}{$forCompany}." : "This is about {$topic}{$forCompany}.",
            'support' => $formal ? "Thank you for raising {$topic}{$forCompany}." : "Thanks for flagging {$topic}{$forCompany}.",
            'hiring' => $formal ? "I am writing in connection with {$topic}{$forCompany}." : "Writing about {$topic}{$forCompany}.",
            'operations' => $formal ? "Please find an update on {$topic}{$forCompany}." : "A quick ops update on {$topic}{$forCompany}.",
            'partnership' => $formal ? "I wished to discuss a possible collaboration on {$topic}{$forCompany}." : "Could we talk about collaborating on {$topic}{$forCompany}?",
            'billing' => $formal ? "This note concerns billing for {$topic}{$forCompany}." : "Billing note on {$topic}{$forCompany}.",
            default => $formal ? "I am writing regarding {$topic}{$forCompany}." : "About {$topic}{$forCompany}.",
        };

        $body = "{$hello}\n\n{$lead}\n\n{$factLine}{$instruction}\n\n{$close}";

        if (($input['length'] ?? 'short') === 'medium') {
            $body .= $formal
                ? "\n\nPlease reply if any point requires clarification. This draft is assembled from your settings on the server."
                : "\n\nReply if anything is off. This draft is assembled from your settings on the server.";
        }

        return [
            'mode' => 'demo',
            'subject' => ($formal ? 'Regarding ' : 'About ').$topic,
            'body' => $body,
            'notice' => 'Demo mode: no DeepSeek API key is configured. This email was assembled from your settings on the server. It is not a live DeepSeek response.',
        ];
    }
}
