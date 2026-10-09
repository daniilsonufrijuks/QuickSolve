<?php

namespace App\Http\Controllers;

use App\Exceptions\BillingNotConfiguredException;
use App\Http\Requests\SubscriptionCheckoutRequest;
use App\Models\Template;
use App\Services\Analytics\UsageRecorder;
use App\Services\Billing\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function checkout(SubscriptionCheckoutRequest $request, CheckoutService $checkout, UsageRecorder $usage): RedirectResponse|JsonResponse
    {
        $usage->record('checkout_started', 'subscription-'.$request->string('plan'), $request->user(), $request);

        try {
            $url = $checkout->subscriptionCheckout($request->user(), $request->string('plan')->toString());
        } catch (BillingNotConfiguredException $exception) {
            return $this->failure($request, $exception->getMessage());
        }

        return $this->checkoutResponse($request, $url);
    }

    public function portal(Request $request, CheckoutService $checkout): RedirectResponse|JsonResponse
    {
        try {
            $checkout->assertConfigured();
            $redirect = $request->user()->redirectToBillingPortal(route('dashboard.subscription'));
        } catch (BillingNotConfiguredException $exception) {
            return $this->failure($request, $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);

            return $this->failure($request, 'The billing portal is unavailable. Check that this account has a Stripe customer.');
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => ['portal_url' => $redirect->getTargetUrl()],
            ]);
        }

        return $redirect;
    }

    public function cancel(Request $request, CheckoutService $checkout): RedirectResponse|JsonResponse
    {
        try {
            $checkout->assertConfigured();
            $subscription = $request->user()->subscription('default');

            if (! $subscription || ! $subscription->valid()) {
                return $this->failure($request, 'There is no active subscription to cancel.');
            }

            $subscription->cancel();
        } catch (BillingNotConfiguredException $exception) {
            return $this->failure($request, $exception->getMessage());
        }

        $message = 'Your subscription is set to cancel at the end of the current billing period.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function template(Request $request, Template $template, CheckoutService $checkout, UsageRecorder $usage): RedirectResponse|JsonResponse
    {
        $usage->record('checkout_started', $template->slug, $request->user(), $request);

        try {
            $url = $checkout->templateCheckout($request->user(), $template);
        } catch (BillingNotConfiguredException $exception) {
            return $this->failure($request, $exception->getMessage());
        }

        return $this->checkoutResponse($request, $url);
    }

    private function checkoutResponse(Request $request, string $url): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'data' => ['checkout_url' => $url],
            ]);
        }

        return redirect()->away($url);
    }

    private function failure(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], 422);
        }

        return back()->with('error', $message);
    }
}
