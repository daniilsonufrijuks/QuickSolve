<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Billing\SubscriptionSyncer;
use Illuminate\Console\Command;

class SyncStripeSubscriptions extends Command
{
    protected $signature = 'quicksolve:sync-subscriptions {--email= : Only sync this account}';

    protected $description = 'Import active Stripe subscriptions into local Cashier records';

    public function handle(SubscriptionSyncer $syncer): int
    {
        $query = User::query()
            ->when(
                $this->option('email'),
                fn ($builder) => $builder->where('email', $this->option('email')),
                fn ($builder) => $builder->whereNotNull('stripe_id'),
            );

        $count = 0;

        $query->each(function (User $user) use ($syncer, &$count) {
            $syncer->syncUser($user);
            $count++;
        });

        $imported = $syncer->syncKnownPlanPrices();

        $this->info('Checked '.$count.' account(s). Imported '.$imported.' matching plan subscription(s).');

        return self::SUCCESS;
    }
}
