<?php

namespace App\Services\Analytics;

use App\Models\Tool;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\PlanResolver;
use Illuminate\Http\Request;
use Throwable;

class UsageRecorder
{
    public function __construct(private readonly PlanResolver $plans) {}

    public const EVENTS = [
        'tool_opened',
        'calculation_completed',
        'generator_used',
        'template_viewed',
        'checkout_started',
        'purchase_completed',
    ];

    public function record(string $type, string $slug, ?User $user, Request $request, array $metadata = []): void
    {
        if (! in_array($type, self::EVENTS, true)) {
            return;
        }

        try {
            UsageEvent::query()->create([
                'user_id' => $user?->id,
                'guest_hash' => $user ? null : $this->plans->guestHash($request),
                'tool_slug' => $slug,
                'usage_type' => $type,
                'metadata' => $metadata === [] ? null : $metadata,
            ]);

            if ($type === 'tool_opened') {
                Tool::query()->where('slug', $slug)->increment('popularity');
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
