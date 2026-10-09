<?php

namespace App\Console\Commands;

use App\Models\UsageEvent;
use Illuminate\Console\Command;

class PruneUsageEvents extends Command
{
    protected $signature = 'quicksolve:prune-usage';

    protected $description = 'Delete usage events older than the configured retention window';

    public function handle(): int
    {
        $days = (int) config('quicksolve.usage_retention_days', 90);
        $deleted = UsageEvent::query()->where('created_at', '<', now()->subDays($days))->delete();
        $this->info("Deleted {$deleted} usage events older than {$days} days.");

        return self::SUCCESS;
    }
}
