<?php

namespace App\Services\Emails;

use App\Exceptions\GenerationLimitExceededException;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\PlanResolver;
use Illuminate\Http\Request;

class EmailDraftGenerator
{
    public function __construct(
        private readonly PlanResolver $plans,
        private readonly DemoEmailDraftProvider $demo,
        private readonly DeepSeekEmailDraftProvider $deepSeek,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input, ?User $user, Request $request): array
    {
        $this->enforceLimit($user, $request);

        $result = $this->provider()->generate($input);

        UsageEvent::query()->create([
            'user_id' => $user?->id,
            'guest_hash' => $user ? null : $this->plans->guestHash($request),
            'tool_slug' => 'email-templates',
            'usage_type' => 'generated',
            'metadata' => [
                'mode' => $result['mode'],
                'situation' => $input['situation'] ?? null,
                'tone' => $input['tone'] ?? null,
            ],
        ]);

        $result['usage'] = $this->plans->generatorUsage($user, $request);

        return $result;
    }

    public function provider(): EmailDraftProvider
    {
        $key = config('quicksolve.ai.api_key');

        if (! is_string($key) || $key === '') {
            return $this->demo;
        }

        return $this->deepSeek;
    }

    public function enforceLimit(?User $user, Request $request): void
    {
        $usage = $this->plans->generatorUsage($user, $request);

        if ($usage['used'] >= $usage['limit']) {
            throw new GenerationLimitExceededException($usage['limit'], $usage['window']);
        }
    }
}
