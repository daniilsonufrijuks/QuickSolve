<?php

namespace App\Services\HomeChat;

use App\Exceptions\GenerationLimitExceededException;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\PlanResolver;
use Illuminate\Http\Request;

class HomeChatGenerator
{
    public function __construct(
        private readonly PlanResolver $plans,
        private readonly DemoHomeChatProvider $demo,
        private readonly DeepSeekHomeChatProvider $deepSeek,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function reply(array $input, ?User $user, Request $request): array
    {
        $this->enforceLimit($user, $request);

        $result = $this->provider()->reply($input);

        UsageEvent::query()->create([
            'user_id' => $user?->id,
            'guest_hash' => $user ? null : $this->plans->guestHash($request),
            'tool_slug' => 'home-chat',
            'usage_type' => 'generated',
            'metadata' => [
                'mode' => $result['mode'],
            ],
        ]);

        $result['usage'] = $this->plans->generatorUsage($user, $request);

        return $result;
    }

    public function provider(): HomeChatProvider
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
