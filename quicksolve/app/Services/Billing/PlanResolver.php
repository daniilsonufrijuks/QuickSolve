<?php

namespace App\Services\Billing;

use App\Models\UsageEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

class PlanResolver
{
    public function key(?User $user): string
    {
        if (! $user) {
            return 'free';
        }

        $subscription = $user->subscription('default');

        if (! $subscription instanceof Subscription || ! $subscription->valid()) {
            return 'free';
        }

        return $this->keyForPrice((string) $subscription->stripe_price) ?? 'free';
    }

    public function keyForPrice(string $priceId): ?string
    {
        foreach (['pro', 'business'] as $plan) {
            if ($priceId !== '' && $priceId === config("quicksolve.plans.{$plan}.stripe_price_id")) {
                return $plan;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(?User $user): array
    {
        $key = $this->key($user);
        $plan = config("quicksolve.plans.{$key}");

        return [
            'key' => $key,
            'name' => $plan['name'],
            'generator_limit' => $plan['generator_limit'],
            'window' => 'month',
        ];
    }

    /**
     * @return array{used: int, limit: int, remaining: int, window: string, plan: string}
     */
    public function generatorUsage(?User $user, Request $request): array
    {
        if (! $user) {
            $limit = (int) config('quicksolve.guest_generator_daily_limit');
            $hash = $this->guestHash($request);
            $used = UsageEvent::query()
                ->whereNull('user_id')
                ->where('guest_hash', $hash)
                ->where('tool_slug', 'product-description-generator')
                ->where('usage_type', 'generated')
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            return [
                'used' => $used,
                'limit' => $limit,
                'remaining' => max($limit - $used, 0),
                'window' => 'day',
                'plan' => 'guest',
            ];
        }

        $plan = $this->summary($user);
        $used = UsageEvent::query()
            ->where('user_id', $user->id)
            ->where('tool_slug', 'product-description-generator')
            ->where('usage_type', 'generated')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return [
            'used' => $used,
            'limit' => (int) $plan['generator_limit'],
            'remaining' => max((int) $plan['generator_limit'] - $used, 0),
            'window' => 'month',
            'plan' => $plan['key'],
        ];
    }

    public function allowsLongDescriptions(?User $user): bool
    {
        return in_array($this->key($user), ['pro', 'business'], true);
    }

    public function allowsPremiumTools(?User $user): bool
    {
        return $this->allowsLongDescriptions($user);
    }

    public function guestHash(Request $request): string
    {
        $seed = $request->hasSession() ? $request->session()->getId() : 'ip:'.$request->ip();

        return hash('sha256', $seed.'|'.config('app.key'));
    }
}
