<?php

namespace App\Services\HomeChat;

use App\Exceptions\ProviderUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekHomeChatProvider implements HomeChatProvider
{
    public function reply(array $input): array
    {
        $baseUrl = rtrim((string) config('quicksolve.ai.base_url'), '/');
        $model = (string) config('quicksolve.ai.model');
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are QuickSolve’s on-site assistant for a small-business tools site. Help the visitor pick a tool or explain a plan. Known plans: Free €0, Pro €5/month, Business €10/month. Five Pro-tool uses are free, then Stripe. Checkout uses server prices. A success URL does not grant Pro. Do not invent testimonials, prices, or that an email was sent. Do not claim you are a live human. Return JSON with keys reply (string, under 120 words) and links (array of {label, href} using only these paths: /tools, /tools/invoice-generator, /tools/profit-margin-calculator, /tools/payment-landing-date, /tools/scope-change-note, /tools/product-description-generator, /tools/email-templates, /tools/cv-builder, /templates, /pricing, /contact, /register, /login).',
            ],
        ];

        foreach ((array) ($input['history'] ?? []) as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $messages[] = [
                'role' => $role,
                'content' => (string) ($turn['content'] ?? ''),
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => (string) $input['message'],
        ];

        try {
            $response = Http::withToken((string) config('quicksolve.ai.api_key'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout((int) config('quicksolve.ai.timeout', 20))
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.4,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => $messages,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Home chat provider request failed.', [
                'exception' => $exception::class,
            ]);

            throw new ProviderUnavailableException('The chat provider is unavailable. Nothing was generated.');
        }

        if (! $response->successful()) {
            Log::warning('Home chat provider returned an error status.', [
                'status' => $response->status(),
            ]);

            throw new ProviderUnavailableException('The chat provider is unavailable. Nothing was generated.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new ProviderUnavailableException('The chat provider returned an unreadable response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['reply']) || ! is_string($decoded['reply'])) {
            throw new ProviderUnavailableException('The chat provider returned an unexpected format.');
        }

        return [
            'mode' => 'live',
            'notice' => null,
            'reply' => mb_substr($decoded['reply'], 0, 1200),
            'links' => HomeChatLinks::sanitize($decoded['links'] ?? []),
        ];
    }
}
