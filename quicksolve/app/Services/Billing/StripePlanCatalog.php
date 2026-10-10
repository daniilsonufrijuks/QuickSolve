<?php

namespace App\Services\Billing;

use App\Exceptions\BillingNotConfiguredException;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;
use Stripe\Product;

class StripePlanCatalog
{
    public function findOrCreatePrice(string $plan, int $amount, string $currency, string $name): string
    {
        try {
            $stripe = Cashier::stripe();
            $product = $this->findProduct($plan) ?? $stripe->products->create([
                'name' => $name,
                'metadata' => [
                    'quicksolve_plan' => $plan,
                ],
            ]);

            $price = $this->findRecurringPrice($product->id, $amount, $currency);

            if ($price === null) {
                $price = $stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $amount,
                    'currency' => strtolower($currency),
                    'recurring' => ['interval' => 'month'],
                    'metadata' => [
                        'quicksolve_plan' => $plan,
                    ],
                ]);
            }

            return $price->id;
        } catch (ApiErrorException $exception) {
            throw new BillingNotConfiguredException('Stripe could not create the '.$name.' price. Check the secret key and try again from Admin → Billing.');
        }
    }

    private function findProduct(string $plan): ?Product
    {
        $products = Cashier::stripe()->products->all(['limit' => 100, 'active' => true]);

        foreach ($products->data as $product) {
            if (($product->metadata['quicksolve_plan'] ?? null) === $plan) {
                return $product;
            }
        }

        return null;
    }

    private function findRecurringPrice(string $productId, int $amount, string $currency): ?\Stripe\Price
    {
        $prices = Cashier::stripe()->prices->all([
            'product' => $productId,
            'active' => true,
            'limit' => 100,
        ]);

        foreach ($prices->data as $price) {
            if (
                $price->type === 'recurring'
                && (int) $price->unit_amount === $amount
                && strtolower((string) $price->currency) === strtolower($currency)
            ) {
                return $price;
            }
        }

        return null;
    }
}
