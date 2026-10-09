<?php

namespace App\Services\Descriptions;

use App\Exceptions\ProviderUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProductDescriptionProvider implements ProductDescriptionProvider
{
    public function generate(array $input): array
    {
        $baseUrl = rtrim((string) config('quicksolve.ai.base_url'), '/');
        $model = (string) config('quicksolve.ai.model');

        try {
            $response = Http::withToken((string) config('quicksolve.ai.api_key'))
                ->acceptJson()
                ->timeout((int) config('quicksolve.ai.timeout', 20))
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.7,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You write product copy for independent sellers. Return JSON with keys title, short_description, full_description, bullets (array of strings), and call_to_action (string or null). Do not invent certifications, reviews, or guarantees.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'product_name' => $input['product_name'],
                                'product_category' => $input['product_category'],
                                'product_features' => $input['product_features'],
                                'target_audience' => $input['target_audience'],
                                'tone' => $input['tone'],
                                'length' => $input['length'],
                                'format' => $input['format'],
                                'include_call_to_action' => (bool) ($input['include_call_to_action'] ?? false),
                            ], JSON_THROW_ON_ERROR),
                        ],
                    ],
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Product description provider request failed.', [
                'exception' => $exception::class,
            ]);

            throw new ProviderUnavailableException;
        }

        if (! $response->successful()) {
            Log::warning('Product description provider returned an error status.', [
                'status' => $response->status(),
            ]);

            throw new ProviderUnavailableException;
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            throw new ProviderUnavailableException('The description provider returned an unreadable response.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['title'], $decoded['short_description'], $decoded['full_description'], $decoded['bullets'])) {
            throw new ProviderUnavailableException('The description provider returned an unexpected format.');
        }

        return [
            'mode' => 'live',
            'title' => (string) $decoded['title'],
            'short_description' => (string) $decoded['short_description'],
            'full_description' => (string) $decoded['full_description'],
            'bullets' => array_values(array_map('strval', (array) $decoded['bullets'])),
            'call_to_action' => isset($decoded['call_to_action']) ? (string) $decoded['call_to_action'] : null,
            'notice' => null,
        ];
    }
}
