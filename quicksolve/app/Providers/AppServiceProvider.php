<?php

namespace App\Providers;

use App\Listeners\FulfillTemplatePurchase;
use App\Listeners\SyncStripeSubscription;
use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('generator', function (Request $request) {
            return Limit::perMinute(8)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('analytics', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('pdf', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        Event::listen(WebhookReceived::class, FulfillTemplatePurchase::class);
        Event::listen(WebhookReceived::class, SyncStripeSubscription::class);

        $this->hydrateStripePriceIds();

        $this->app->booted(function () {
            if (! config('cashier.webhook.secret')) {
                return;
            }

            $route = Route::getRoutes()->getByName('cashier.webhook');

            if ($route) {
                $route->middleware(VerifyWebhookSignature::class);
            }
        });
    }

    private function hydrateStripePriceIds(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        foreach (['pro', 'business'] as $plan) {
            $current = config("quicksolve.plans.{$plan}.stripe_price_id");

            if (is_string($current) && $current !== '') {
                continue;
            }

            $stored = Setting::get("stripe.{$plan}_price_id");

            if (is_string($stored) && $stored !== '') {
                config(["quicksolve.plans.{$plan}.stripe_price_id" => $stored]);
            }
        }
    }
}
