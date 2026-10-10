<?php

namespace App\Console\Commands;

use App\Services\Billing\PlanPriceResolver;
use Illuminate\Console\Command;

class SyncStripePrices extends Command
{
    protected $signature = 'quicksolve:sync-stripe-prices';

    protected $description = 'Create or reuse Stripe monthly prices for the Pro and Business plans';

    public function handle(PlanPriceResolver $prices): int
    {
        try {
            $ids = $prices->ensurePaidPlans();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Pro price: '.$ids['pro']);
        $this->info('Business price: '.$ids['business']);

        return self::SUCCESS;
    }
}
