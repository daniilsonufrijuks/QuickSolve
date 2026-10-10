<?php

namespace App\Services\Billing;

use App\Exceptions\BillingNotConfiguredException;
use App\Models\Setting;

class PlanPriceResolver
{
    public function __construct(private readonly StripePlanCatalog $catalog) {}

    public function idFor(string $plan): ?string
    {
        if (! in_array($plan, ['pro', 'business'], true)) {
            return null;
        }

        $configured = config("quicksolve.plans.{$plan}.stripe_price_id");

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return Setting::get("stripe.{$plan}_price_id");
    }

    public function store(string $plan, string $priceId): void
    {
        Setting::put("stripe.{$plan}_price_id", $priceId);
        config(["quicksolve.plans.{$plan}.stripe_price_id" => $priceId]);
    }

    public function ensure(string $plan): string
    {
        $existing = $this->idFor($plan);

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $secret = config('cashier.secret');

        if (! is_string($secret) || $secret === '') {
            throw new BillingNotConfiguredException;
        }

        $amount = (int) config("quicksolve.plans.{$plan}.monthly_price");
        $name = 'QuickSolve '.(string) config("quicksolve.plans.{$plan}.name");
        $currency = (string) config('cashier.currency', 'eur');
        $priceId = $this->catalog->findOrCreatePrice($plan, $amount, $currency, $name);
        $this->store($plan, $priceId);

        return $priceId;
    }

    /**
     * @return array{pro: string, business: string}
     */
    public function ensurePaidPlans(): array
    {
        return [
            'pro' => $this->ensure('pro'),
            'business' => $this->ensure('business'),
        ];
    }

    /**
     * @return array{configured: bool, pro_price_id: ?string, business_price_id: ?string, currency: string}
     */
    public function summary(): array
    {
        $secret = config('cashier.secret');

        return [
            'configured' => is_string($secret) && $secret !== '',
            'pro_price_id' => $this->idFor('pro'),
            'business_price_id' => $this->idFor('business'),
            'currency' => strtoupper((string) config('cashier.currency', 'eur')),
            'pro_amount' => (int) config('quicksolve.plans.pro.monthly_price'),
            'business_amount' => (int) config('quicksolve.plans.business.monthly_price'),
        ];
    }
}
