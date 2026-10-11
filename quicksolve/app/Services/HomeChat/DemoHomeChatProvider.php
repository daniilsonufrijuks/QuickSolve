<?php

namespace App\Services\HomeChat;

class DemoHomeChatProvider implements HomeChatProvider
{
    public function reply(array $input): array
    {
        $message = mb_strtolower((string) ($input['message'] ?? ''));

        [$reply, $hrefs] = $this->match($message);

        return [
            'mode' => 'demo',
            'notice' => 'Demo mode: no DeepSeek API key is configured. This reply was assembled on the server from your question. It is not a live DeepSeek response.',
            'reply' => $reply,
            'links' => HomeChatLinks::sanitize(array_map(
                fn (string $href) => ['href' => $href, 'label' => HomeChatLinks::ALLOWED[$href]],
                $hrefs,
            )),
        ];
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private function match(string $message): array
    {
        if (str_contains($message, 'invoice')) {
            return [
                'The invoice generator is free. You can export a PDF without an account. Saving a copy needs a login.',
                ['/tools/invoice-generator'],
            ];
        }

        if (str_contains($message, 'profit') || str_contains($message, 'margin') || str_contains($message, 'fee')) {
            return [
                'The profit margin calculator splits percentage fees and fixed fees so a card charge is not a guess.',
                ['/tools/profit-margin-calculator'],
            ];
        }

        if (str_contains($message, 'pay') || str_contains($message, 'cash') || str_contains($message, 'landing') || str_contains($message, 'due')) {
            return [
                'Payment landing date turns a due date into when cash is likely to land. It is a Pro tool. Guests get five Pro-tool uses, then a Stripe subscription is required.',
                ['/tools/payment-landing-date', '/pricing'],
            ];
        }

        if (str_contains($message, 'scope') || str_contains($message, 'extra') || str_contains($message, 'unpaid')) {
            return [
                'The scope change note writes the original job, the extra request, and the extra cost as a PDF. It is a Pro tool with five trial uses.',
                ['/tools/scope-change-note'],
            ];
        }

        if (str_contains($message, 'template') || str_contains($message, 'download') || str_contains($message, 'spreadsheet')) {
            return [
                'Templates are real files. They stay private until Stripe confirms payment. There is no sample ZIP.',
                ['/templates'],
            ];
        }

        if (str_contains($message, 'email') || str_contains($message, 'cv') || str_contains($message, 'description') || str_contains($message, 'deepseek')) {
            return [
                'Product descriptions, email drafts, and the CV builder use DeepSeek only when a server key is present. Without a key, the page stays in demo mode and says so. A failed live request is an error, not demo text.',
                ['/tools/product-description-generator', '/tools/email-templates', '/tools/cv-builder'],
            ];
        }

        if (str_contains($message, 'price') || str_contains($message, 'pro') || str_contains($message, 'plan') || str_contains($message, 'cost') || str_contains($message, 'stripe')) {
            return [
                'Free is €0. Pro is €5 per month. Business is €10 per month. Five Pro-tool uses are free. Checkout uses the server price. Returning from a success URL does not turn Pro on.',
                ['/pricing'],
            ];
        }

        return [
            'QuickSolve is a set of small business tools: calculators, invoices, descriptions, and downloadable templates. Ask about a job (invoices, cash, extras, plans) and I will point to the matching page. This demo chat does not invent prices or send messages.',
            ['/tools', '/pricing'],
        ];
    }
}
