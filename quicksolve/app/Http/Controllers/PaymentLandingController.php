<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalculatePaymentLandingRequest;
use App\Services\Billing\PlanResolver;
use App\Services\Tools\PaymentLandingCalculator;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class PaymentLandingController extends Controller
{
    public function __invoke(CalculatePaymentLandingRequest $request, PaymentLandingCalculator $calculator, PlanResolver $plans): JsonResponse
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'payment-landing-date');
        }

        try {
            return response()->json(['data' => $calculator->calculate($request->validated())]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
