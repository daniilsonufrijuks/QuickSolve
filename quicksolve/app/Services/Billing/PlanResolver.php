<?php

namespace App\Services\Billing;

use App\Models\UsageEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

class PlanResolver
{
    /**
     * @var list<string>
     */
    private const GENERATOR_SLUGS = [
        'product-description-generator',
        'email-templates',
        'home-chat',
        'cv-builder',
    ];

    public function __construct(private readonly PlanPriceResolver $prices) {}

    public function key(?User $user): string
    {
        $subscription = $this->currentSubscription($user);

        if (! $subscription instanceof Subscription) {
            return 'free';
        }

        return $this->keyForPrice($this->priceId($subscription)) ?? 'free';
    }

    public function currentSubscription(?User $user): ?Subscription
    {
        if (! $user) {
            return null;
        }

        $best = null;
        $bestRank = -1;

        foreach ($user->subscriptions as $subscription) {
            if (! $subscription instanceof Subscription || ! $subscription->valid()) {
                continue;
            }

            $plan = $this->keyForPrice($this->priceId($subscription));
            $rank = $this->rank($plan);

            if ($rank > $bestRank || ($rank === $bestRank && $best !== null && $subscription->id > $best->id)) {
                $best = $subscription;
                $bestRank = $rank;
            }
        }

        return $best;
    }

    public function keyForPrice(string $priceId): ?string
    {
        if ($priceId === '') {
            return null;
        }

        foreach (['business', 'pro'] as $plan) {
            if (in_array($priceId, $this->prices->knownIds($plan), true)) {
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
                ->whereIn('tool_slug', self::GENERATOR_SLUGS)
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
            ->whereIn('tool_slug', self::GENERATOR_SLUGS)
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

    public function isBusiness(?User $user): bool
    {
        return $this->key($user) === 'business';
    }

    public function allowsDocumentBranding(?User $user): bool
    {
        return $this->allowsLongDescriptions($user);
    }

    public function allowsUnbrandedDocuments(?User $user): bool
    {
        return $this->isBusiness($user);
    }

    public function allowsActivityExport(?User $user): bool
    {
        return $this->isBusiness($user);
    }

    /**
     * @return array{used: int, limit: int, remaining: int, window: string, plan: string}
     */
    public function premiumTrialUsage(?User $user, Request $request): array
    {
        $limit = (int) config('quicksolve.premium_trial_uses', 5);
        $plan = $this->key($user);

        if ($this->allowsLongDescriptions($user)) {
            return [
                'used' => 0,
                'limit' => $limit,
                'remaining' => $limit,
                'window' => 'subscription',
                'plan' => $plan,
            ];
        }

        $query = UsageEvent::query()->where('usage_type', 'premium_trial');

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereNull('user_id')->where('guest_hash', $this->visitorHash($request));
        }

        $used = $query->count();

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max($limit - $used, 0),
            'window' => 'lifetime',
            'plan' => $plan,
        ];
    }

    public function allowsPremiumTools(?User $user, ?Request $request = null): bool
    {
        if ($this->allowsLongDescriptions($user)) {
            return true;
        }

        return $request instanceof Request && $this->premiumTrialUsage($user, $request)['remaining'] > 0;
    }

    public function consumePremiumTrial(?User $user, Request $request, string $slug): void
    {
        if ($this->allowsLongDescriptions($user)) {
            return;
        }

        if ($this->premiumTrialUsage($user, $request)['remaining'] < 1) {
            return;
        }

        UsageEvent::query()->create([
            'user_id' => $user?->id,
            'guest_hash' => $user ? null : $this->visitorHash($request),
            'tool_slug' => $slug,
            'usage_type' => 'premium_trial',
            'metadata' => null,
        ]);
    }

    public function guestHash(Request $request): string
    {
        $seed = $request->hasSession() ? $request->session()->getId() : 'ip:'.$request->ip();

        return hash('sha256', $seed.'|'.config('app.key'));
    }

    public function visitorHash(Request $request): string
    {
        return hash('sha256', ($request->ip() ?: '0.0.0.0').'|'.(string) $request->userAgent().'|'.config('app.key'));
    }

    public function rank(?string $plan): int
    {
        return match ($plan) {
            'business' => 2,
            'pro' => 1,
            default => 0,
        };
    }

    public function priceIdFor(Subscription $subscription): string
    {
        return $this->priceId($subscription);
    }

    private function priceId(Subscription $subscription): string
    {
        $priceId = (string) ($subscription->stripe_price ?: '');

        if ($priceId !== '') {
            return $priceId;
        }

        $itemPrice = $subscription->items()->orderBy('id')->value('stripe_price');

        return is_string($itemPrice) ? $itemPrice : '';
    }
}
