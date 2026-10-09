<?php

namespace App\Services\Descriptions;

use App\Exceptions\GenerationLimitExceededException;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\PlanResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class ProductDescriptionGenerator
{
    public function __construct(
        private readonly PlanResolver $plans,
        private readonly DemoProductDescriptionProvider $demo,
        private readonly OpenAiProductDescriptionProvider $openAi,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input, ?User $user, Request $request): array
    {
        $this->enforceLimit($user, $request);

        if (($input['length'] ?? 'short') === 'long' && ! $this->plans->allowsLongDescriptions($user)) {
            throw new AuthorizationException('Long descriptions are included with Pro and Business.');
        }

        $provider = $this->provider();
        $result = $provider->generate($input);

        UsageEvent::query()->create([
            'user_id' => $user?->id,
            'guest_hash' => $user ? null : $this->plans->guestHash($request),
            'tool_slug' => 'product-description-generator',
            'usage_type' => 'generated',
            'metadata' => [
                'mode' => $result['mode'],
                'length' => $input['length'] ?? null,
            ],
        ]);

        $result['usage'] = $this->plans->generatorUsage($user, $request);

        return $result;
    }

    public function provider(): ProductDescriptionProvider
    {
        $key = config('quicksolve.ai.api_key');

        if (! is_string($key) || $key === '') {
            return $this->demo;
        }

        return $this->openAi;
    }

    public function enforceLimit(?User $user, Request $request): void
    {
        $usage = $this->plans->generatorUsage($user, $request);

        if ($usage['used'] >= $usage['limit']) {
            throw new GenerationLimitExceededException($usage['limit'], $usage['window']);
        }
    }
}
