<?php

namespace App\Console\Commands;

use App\Services\Billing\PurchaseSyncer;
use Illuminate\Console\Command;

class SyncStripePurchases extends Command
{
    protected $signature = 'quicksolve:sync-purchases';

    protected $description = 'Mark paid Stripe template checkouts as fulfilled when webhooks did not reach the app';

    public function handle(PurchaseSyncer $syncer): int
    {
        $checked = $syncer->syncPendingForUsersWithStripe();

        $this->info('Checked '.$checked.' account(s) with pending template purchases.');

        return self::SUCCESS;
    }
}
