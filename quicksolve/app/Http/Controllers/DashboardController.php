<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Http\Resources\GeneratedDocumentResource;
use App\Http\Resources\PurchaseResource;
use App\Models\GeneratedDocument;
use App\Models\Purchase;
use App\Services\Billing\PlanResolver;
use App\Services\Billing\SubscriptionSyncer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PlanResolver $plans, SubscriptionSyncer $syncer): Response
    {
        $user = $request->user();

        if ($user->stripe_id && ! $user->subscription('default')?->valid()) {
            $syncer->syncUser($user);
            $user->refresh();
            $user->unsetRelation('subscriptions');
        }
        $purchases = Purchase::query()->where('user_id', $user->id)->with('template')->latest()->limit(5)->get();
        $documents = GeneratedDocument::query()->where('user_id', $user->id)->latest()->limit(5)->get();

        return Inertia::render('Dashboard', [
            'plan' => $plans->summary($user),
            'usage' => $plans->generatorUsage($user, $request),
            'purchases' => PurchaseResource::collection($purchases)->resolve(),
            'documents' => GeneratedDocumentResource::collection($documents)->resolve(),
            'counts' => [
                'purchases' => Purchase::query()->where('user_id', $user->id)->where('status', PurchaseStatus::Paid)->count(),
                'documents' => GeneratedDocument::query()->where('user_id', $user->id)->count(),
            ],
        ]);
    }

    public function subscription(Request $request, PlanResolver $plans, SubscriptionSyncer $syncer): Response
    {
        $sessionId = $request->string('session_id')->toString();
        $syncer->syncUser($request->user(), $sessionId !== '' ? $sessionId : null);
        $request->user()->refresh();
        $request->user()->unsetRelation('subscriptions');

        $subscription = $request->user()->subscription('default');

        return Inertia::render('dashboard/Subscription', [
            'plan' => $plans->summary($request->user()),
            'usage' => $plans->generatorUsage($request->user(), $request),
            'subscription' => $subscription ? [
                'stripe_status' => $subscription->stripe_status,
                'stripe_price' => $subscription->stripe_price,
                'on_grace_period' => $subscription->onGracePeriod(),
                'ends_at' => $subscription->ends_at?->toDateString(),
                'valid' => $subscription->valid(),
            ] : null,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function syncSubscription(Request $request, SubscriptionSyncer $syncer): RedirectResponse
    {
        $syncer->syncUser($request->user());

        return back()->with('success', 'Checked Stripe for your latest subscription.');
    }

    public function purchases(Request $request): Response
    {
        $purchases = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->with('template')
            ->latest()
            ->paginate(12);

        return Inertia::render('dashboard/Purchases', [
            'purchases' => [
                'data' => PurchaseResource::collection($purchases->items())->resolve(),
                'meta' => [
                    'current_page' => $purchases->currentPage(),
                    'last_page' => $purchases->lastPage(),
                    'total' => $purchases->total(),
                ],
            ],
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function downloads(Request $request): Response
    {
        $purchases = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->where('status', PurchaseStatus::Paid)
            ->with('template')
            ->latest()
            ->get();

        return Inertia::render('dashboard/Downloads', [
            'purchases' => PurchaseResource::collection($purchases)->resolve(),
        ]);
    }

    public function documents(Request $request): Response
    {
        $documents = GeneratedDocument::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return Inertia::render('dashboard/Documents', [
            'documents' => [
                'data' => GeneratedDocumentResource::collection($documents->items())->resolve(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'total' => $documents->total(),
                ],
            ],
        ]);
    }
}
