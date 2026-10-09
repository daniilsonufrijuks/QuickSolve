<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\GeneratedDocumentResource;
use App\Http\Resources\PurchaseResource;
use App\Models\GeneratedDocument;
use App\Models\Purchase;
use App\Services\Billing\PlanResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function user(Request $request, PlanResolver $plans): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'plan' => $plans->summary($user),
            ],
        ]);
    }

    public function dashboard(Request $request, PlanResolver $plans): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'plan' => $plans->summary($user),
                'usage' => $plans->generatorUsage($user, $request),
                'paid_purchases' => Purchase::query()->where('user_id', $user->id)->where('status', PurchaseStatus::Paid)->count(),
                'documents' => GeneratedDocument::query()->where('user_id', $user->id)->count(),
            ],
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        $purchases = Purchase::query()->where('user_id', $request->user()->id)->with('template')->latest()->paginate(12);

        return PurchaseResource::collection($purchases)->response();
    }

    public function subscription(Request $request, PlanResolver $plans): JsonResponse
    {
        $subscription = $request->user()->subscription('default');

        return response()->json([
            'data' => [
                'plan' => $plans->summary($request->user()),
                'usage' => $plans->generatorUsage($request->user(), $request),
                'subscription' => $subscription ? [
                    'stripe_status' => $subscription->stripe_status,
                    'on_grace_period' => $subscription->onGracePeriod(),
                    'ends_at' => $subscription->ends_at?->toIso8601String(),
                    'valid' => $subscription->valid(),
                ] : null,
            ],
        ]);
    }

    public function documents(Request $request): JsonResponse
    {
        $documents = GeneratedDocument::query()->where('user_id', $request->user()->id)->latest()->paginate(12);

        return GeneratedDocumentResource::collection($documents)->response();
    }
}
