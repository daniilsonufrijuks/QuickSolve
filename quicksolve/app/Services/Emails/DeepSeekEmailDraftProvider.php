<?php

namespace App\Services\Emails;

use App\Exceptions\ProviderUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekEmailDraftProvider implements EmailDraftProvider
{
    public function generate(array $input): array
    {
        $baseUrl = rtrim((string) config('quicksolve.ai.base_url'), '/');
        $model = (string) config('quicksolve.ai.model');
        $messages = [
            [
                'role' => 'system',
                'content' => 'You draft business emails the user will paste into their own mailbox. Return JSON with keys subject and body. Do not claim the email was sent. Do not invent prices, dates, reviews, certifications, or facts the user did not supply. Keep the requested tone and length.',
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'topic' => $input['topic'],
                    'situation' => $input['situation'],
                    'tone' => $input['tone'],
                    'length' => $input['length'],
                    'from_name' => $input['from_name'],
                    'to_name' => $input['to_name'],
                    'company' => $input['company'] ?? null,
                    'facts' => $input['facts'] ?? null,
                    'instruction' => $input['instruction'],
                ], JSON_THROW_ON_ERROR),
            ],
        ];

        foreach ((array) ($input['history'] ?? []) as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $messages[] = [
                'role' => $role,
                'content' => (string) ($turn['content'] ?? ''),
            ];
        }

        try {
            $response = Http::withToken((string) config('quicksolve.ai.api_key'))
                ->acceptJson()
                ->timeout((int) config('quicksolve.ai.timeout', 20))
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.6,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => $messages,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Email draft provider request failed.', [
                'exception' => $exception::class,
            ]);

            throw new ProviderUnavailableException('The email provider is unavailable. Nothing was generated.');
        }

        if (! $response->successful()) {
            Log::warning('Email draft provider returned an error status.', [
                'status' => $response->status(),
            ]);

            throw new ProviderUnavailableException('The email provider is unavailable. Nothing was generated.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new ProviderUnavailableException('The email provider returned an unreadable response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['subject'], $decoded['body'])) {
            throw new ProviderUnavailableException('The email provider returned an unexpected format.');
        }

        return [
            'mode' => 'live',
            'subject' => (string) $decoded['subject'],
            'body' => (string) $decoded['body'],
            'notice' => null,
        ];
    }
}
