<?php

namespace App\Services\Cv;

use App\Exceptions\ProviderUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekCvChatProvider implements CvChatProvider
{
    public function reply(array $input): array
    {
        $baseUrl = rtrim((string) config('quicksolve.ai.base_url'), '/');
        $model = (string) config('quicksolve.ai.model');
        $messages = [
            [
                'role' => 'system',
                'content' => 'You help edit a résumé the user already typed. Return JSON with keys reply (string, under 160 words) and patch (object or null). Patch may include headline, summary, skills (array of strings), experience (array of {role, company, dates, details}), and education (array of {school, credential, dates}). Use only facts in the provided CV JSON. Do not invent employers, dates, schools, skills, awards, or metrics. Do not claim the PDF applies for jobs, that you are ATS-certified, or that a JSON Resume was published to a registry. If the user asks for missing facts, say they need to type them. Set patch to null when you are only answering.',
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'cv' => $input['cv'] ?? [],
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
                ->connectTimeout(5)
                ->timeout((int) config('quicksolve.ai.timeout', 20))
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.4,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => $messages,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('CV chat provider request failed.', [
                'exception' => $exception::class,
            ]);

            throw new ProviderUnavailableException('The chat provider is unavailable. Nothing was generated.');
        }

        if (! $response->successful()) {
            Log::warning('CV chat provider returned an error status.', [
                'status' => $response->status(),
            ]);

            throw new ProviderUnavailableException('The chat provider is unavailable. Nothing was generated.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new ProviderUnavailableException('The chat provider returned an unreadable response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['reply'])) {
            throw new ProviderUnavailableException('The chat provider returned an unexpected format.');
        }

        return [
            'mode' => 'live',
            'notice' => null,
            'reply' => (string) $decoded['reply'],
            'patch' => is_array($decoded['patch'] ?? null) ? $decoded['patch'] : null,
        ];
    }
}
