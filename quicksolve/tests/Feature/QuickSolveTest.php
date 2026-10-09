<?php

use App\Models\GeneratedDocument;
use App\Models\Purchase;
use App\Models\Template;
use App\Models\Tool;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\CheckoutService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('guests can list published tools and cannot see drafts', function () {
    $published = Tool::factory()->create(['name' => 'Visible tool', 'is_published' => true]);
    Tool::factory()->unpublished()->create(['name' => 'Hidden tool']);

    $this->getJson('/api/v1/tools')
        ->assertOk()
        ->assertJsonFragment(['slug' => $published->slug])
        ->assertJsonMissing(['name' => 'Hidden tool']);
});

test('product description requests are validated', function () {
    $this->postJson('/api/v1/tools/product-description-generator/generate', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['product_name', 'product_features', 'tone']);
});

test('the generator stays in demo mode when no provider key is configured', function () {
    config(['quicksolve.ai.api_key' => null]);

    $response = $this->postJson('/api/v1/tools/product-description-generator/generate', [
        'product_name' => 'Linen tote',
        'product_category' => 'Bags',
        'product_features' => "Heavy cotton\nInner pocket",
        'target_audience' => 'weekend market sellers',
        'tone' => 'direct',
        'length' => 'short',
        'format' => 'listing',
        'include_call_to_action' => true,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.mode', 'demo')
        ->assertJsonPath('data.title', 'Linen tote');

    expect($response->json('data.notice'))->toContain('Demo mode');
    expect(UsageEvent::query()->where('usage_type', 'generated')->count())->toBe(1);
});

test('free accounts cannot request long descriptions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/v1/tools/product-description-generator/generate', [
        'product_name' => 'Linen tote',
        'product_category' => 'Bags',
        'product_features' => 'Heavy cotton',
        'target_audience' => 'sellers',
        'tone' => 'direct',
        'length' => 'long',
        'format' => 'listing',
    ])->assertForbidden();
});

test('guests hit the daily generation limit', function () {
    config(['quicksolve.guest_generator_daily_limit' => 1, 'quicksolve.ai.api_key' => null]);

    $payload = [
        'product_name' => 'Mug',
        'product_category' => 'Home',
        'product_features' => 'Stoneware',
        'target_audience' => 'gift buyers',
        'tone' => 'friendly',
        'length' => 'short',
        'format' => 'plain',
    ];

    $this->postJson('/api/v1/tools/product-description-generator/generate', $payload)->assertOk();
    $this->postJson('/api/v1/tools/product-description-generator/generate', $payload)->assertStatus(429);
});

test('pro subscribers can request long descriptions', function () {
    config([
        'quicksolve.plans.pro.stripe_price_id' => 'price_pro',
        'quicksolve.ai.api_key' => null,
    ]);

    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test_pro',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($user)->postJson('/api/v1/tools/product-description-generator/generate', [
        'product_name' => 'Linen tote',
        'product_category' => 'Bags',
        'product_features' => 'Heavy cotton',
        'target_audience' => 'sellers',
        'tone' => 'direct',
        'length' => 'long',
        'format' => 'listing',
    ])->assertOk()->assertJsonPath('data.mode', 'demo');
});

test('invoice documents are private to the owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $document = GeneratedDocument::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->getJson('/api/v1/invoices/'.$document->id)->assertForbidden();
    $this->actingAs($owner)->getJson('/api/v1/invoices/'.$document->id)->assertOk();
});

test('authenticated users can save an invoice and guests cannot', function () {
    $payload = [
        'business_name' => 'North Studio',
        'customer_name' => 'Ada Client',
        'invoice_number' => 'INV-9',
        'issue_date' => '2026-10-01',
        'due_date' => '2026-10-15',
        'currency' => 'EUR',
        'tax_rate' => 10,
        'items' => [
            ['description' => 'Consulting', 'quantity' => 2, 'unit_price' => 50],
        ],
    ];

    $this->postJson('/api/v1/invoices', $payload)->assertUnauthorized();

    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/v1/invoices', $payload)
        ->assertCreated()
        ->assertJsonPath('data.title', 'Invoice INV-9');

    expect(GeneratedDocument::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('downloads require a paid purchase and the stored file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('templates/kit.txt', 'real file');

    $owner = User::factory()->create();
    $template = Template::factory()->create(['private_file_path' => 'templates/kit.txt']);
    $pending = Purchase::factory()->pending()->create([
        'user_id' => $owner->id,
        'template_id' => $template->id,
    ]);
    $paid = Purchase::factory()->create([
        'user_id' => $owner->id,
        'template_id' => $template->id,
    ]);
    $stranger = User::factory()->create();

    $this->actingAs($owner)->get('/downloads/'.$pending->id)->assertForbidden();
    $this->actingAs($stranger)->get('/downloads/'.$paid->id)->assertForbidden();
    $this->actingAs($owner)->get('/downloads/'.$paid->id)->assertOk();
});

test('subscription checkout uses the server plan and ignores a client price', function () {
    $user = User::factory()->create();

    $this->mock(CheckoutService::class, function ($mock) use ($user) {
        $mock->shouldReceive('subscriptionCheckout')
            ->once()
            ->withArgs(function ($passedUser, $plan) use ($user) {
                return $passedUser->is($user) && $plan === 'pro';
            })
            ->andReturn('https://checkout.stripe.test/session');
    });

    $this->actingAs($user)->postJson('/api/v1/billing/checkout', [
        'plan' => 'pro',
        'price' => '1',
    ])->assertOk()->assertJsonPath('data.checkout_url', 'https://checkout.stripe.test/session');
});

test('a verified webhook marks the matching purchase paid and ignores a mismatched amount', function () {
    Mail::fake();
    config(['cashier.webhook.secret' => 'whsec_test']);

    $purchase = Purchase::factory()->pending()->create(['amount' => 1900, 'currency' => 'EUR']);

    stripeWebhook($this, [
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_test_paid',
            'mode' => 'payment',
            'payment_status' => 'paid',
            'amount_total' => 100,
            'currency' => 'eur',
            'metadata' => ['purchase_id' => (string) $purchase->id],
        ]],
    ])->assertOk();

    expect($purchase->fresh()->status->value)->toBe('pending');

    stripeWebhook($this, [
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_test_paid',
            'mode' => 'payment',
            'payment_status' => 'paid',
            'amount_total' => 1900,
            'currency' => 'eur',
            'metadata' => ['purchase_id' => (string) $purchase->id],
        ]],
    ])->assertOk();

    expect($purchase->fresh()->status->value)->toBe('paid')
        ->and($purchase->fresh()->payment_reference)->toBe('cs_test_paid');
});

test('unsigned webhook calls are rejected when a secret is configured', function () {
    config(['cashier.webhook.secret' => 'whsec_test']);

    $this->postJson('/stripe/webhook', ['type' => 'checkout.session.completed'])
        ->assertForbidden();
});

test('a failed provider does not return demo copy', function () {
    config(['quicksolve.ai.api_key' => 'test-key']);
    Http::fake(['*' => Http::response(['error' => 'unavailable'], 503)]);

    $this->postJson('/api/v1/tools/product-description-generator/generate', [
        'product_name' => 'Mug',
        'product_category' => 'Home',
        'product_features' => 'Stoneware',
        'target_audience' => 'gift buyers',
        'tone' => 'friendly',
        'length' => 'short',
        'format' => 'plain',
    ])->assertStatus(503);

    expect(UsageEvent::query()->count())->toBe(0);
});

test('a checkout return does not change the plan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/subscription?status=processing')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('plan.key', 'free')->where('status', 'processing'));
});

test('non-admins cannot open the admin dashboard', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();
});

test('freelance rate reports require a pro or business plan', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);

    $payload = [
        'income_target' => '60000.00',
        'annual_expenses' => '6000.00',
        'hours_per_day' => '5',
        'days_per_week' => 5,
        'weeks_per_year' => 48,
        'unpaid_leave_days' => 10,
        'currency' => 'EUR',
    ];

    $this->post('/tools/freelance-rate-calculator/report', $payload)->assertRedirect(route('login'));

    $free = User::factory()->create();
    $this->actingAs($free)->post('/tools/freelance-rate-calculator/report', $payload)->assertForbidden();

    $pro = User::factory()->create();
    $pro->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_rate_report',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($pro)
        ->post('/tools/freelance-rate-calculator/report', $payload)
        ->assertOk()
        ->assertSee('Hourly rate: 57.39');
});

test('premium tools are locked without a qualifying subscription', function () {
    $tool = Tool::factory()->premium()->create();

    $this->get('/tools/'.$tool->slug)->assertOk()->assertInertia(fn ($page) => $page->where('locked', true));
});

function stripeWebhook(object $testCase, array $payload)
{
    $body = json_encode($payload);
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

    return $testCase->call('POST', '/stripe/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
    ], $body);
}
