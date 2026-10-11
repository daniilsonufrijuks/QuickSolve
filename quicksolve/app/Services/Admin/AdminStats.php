<?php

namespace App\Services\Admin;

use App\Enums\PurchaseStatus;
use App\Models\ContactMessage;
use App\Models\Purchase;
use App\Models\Template;
use App\Models\Tool;
use App\Models\UsageEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Cashier\Subscription;

class AdminStats
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $start = now()->subDays(13)->startOfDay();
        $days = $this->dateKeys($start, 14);

        return [
            'totals' => [
                'users' => User::query()->count(),
                'published_tools' => Tool::query()->published()->count(),
                'published_templates' => Template::query()->published()->count(),
                'paid_purchases' => Purchase::query()->where('status', PurchaseStatus::Paid)->count(),
                'active_subscriptions' => Subscription::query()->where('stripe_status', 'active')->count(),
                'events_30_days' => UsageEvent::query()->where('created_at', '>=', now()->subDays(30))->count(),
                'unread_messages' => ContactMessage::query()->where('is_read', false)->count(),
                'admins' => User::query()->where('is_admin', true)->count(),
                'revenue_paid' => (int) Purchase::query()->where('status', PurchaseStatus::Paid)->sum('amount'),
            ],
            'activity' => $this->series($days, [
                'signups' => $this->countsByDay(User::query(), 'created_at', $start),
                'events' => $this->countsByDay(UsageEvent::query(), 'created_at', $start),
                'purchases' => $this->countsByDay(Purchase::query(), 'created_at', $start),
            ]),
            'purchase_status' => Purchase::query()
                ->select('status')
                ->selectRaw('count(*) as total')
                ->groupBy('status')
                ->get()
                ->map(fn ($row) => [
                    'label' => is_object($row->status) ? $row->status->value : (string) $row->status,
                    'value' => (int) $row->total,
                ])
                ->values()
                ->all(),
            'top_tools' => UsageEvent::query()
                ->select('tool_slug')
                ->selectRaw('count(*) as total')
                ->where('created_at', '>=', now()->subDays(30))
                ->where('usage_type', 'tool_opened')
                ->groupBy('tool_slug')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'label' => (string) $row->tool_slug,
                    'value' => (int) $row->total,
                ])
                ->all(),
            'revenue' => $this->series($days, [
                'paid' => $this->sumsByDay(
                    Purchase::query()->where('status', PurchaseStatus::Paid),
                    'paid_at',
                    'amount',
                    $start,
                ),
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    private function dateKeys(Carbon $start, int $days): array
    {
        return collect(range(0, $days - 1))
            ->map(fn (int $offset) => $start->copy()->addDays($offset)->toDateString())
            ->all();
    }

    /**
     * @param  array<string, Collection<string, int>>  $series
     * @param  list<string>  $days
     * @return list<array<string, mixed>>
     */
    private function series(array $days, array $series): array
    {
        return array_map(function (string $day) use ($series) {
            $point = ['date' => $day, 'label' => Carbon::parse($day)->format('j M')];

            foreach ($series as $key => $values) {
                $point[$key] = (int) $values->get($day, 0);
            }

            return $point;
        }, $days);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Collection<string, int>
     */
    private function countsByDay($query, string $column, Carbon $start): Collection
    {
        return $query
            ->where($column, '>=', $start)
            ->selectRaw('date('.$column.') as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($total) => (int) $total);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Collection<string, int>
     */
    private function sumsByDay($query, string $column, string $amount, Carbon $start): Collection
    {
        return $query
            ->where($column, '>=', $start)
            ->selectRaw('date('.$column.') as day, coalesce(sum('.$amount.'), 0) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($total) => (int) $total);
    }
}
