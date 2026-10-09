<?php

namespace Database\Factories;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'template_id' => Template::factory(),
            'amount' => 1900,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'payment_reference' => 'cs_test_'.fake()->unique()->bothify('##########'),
            'status' => PurchaseStatus::Paid,
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => PurchaseStatus::Pending,
            'payment_reference' => null,
            'paid_at' => null,
        ]);
    }
}
