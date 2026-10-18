<?php

use App\Models\ContactMessage;
use App\Models\GeneratedDocument;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Template;
use App\Models\Tool;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\CheckoutService;
use App\Services\Billing\PlanPriceResolver;
use App\Services\Billing\PlanResolver;
use App\Services\Billing\PurchaseFulfiller;
use App\Services\Billing\PurchaseSyncer;
use App\Services\Billing\StripePlanCatalog;
use App\Services\Billing\SubscriptionSyncer;
use App\Services\Documents\DocumentBranding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Subscription;

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

test('the dashboard asks stripe when a signed-in account still looks free', function () {
    $user = User::factory()->create(['stripe_id' => null]);

    $this->mock(SubscriptionSyncer::class, function ($mock) use ($user) {
        $mock->shouldReceive('refreshPaidAccess')
            ->once()
            ->withArgs(fn ($passed) => $passed->is($user));
    });

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('plan.key', 'free'));
});

test('login asks stripe when the local plan is still free', function () {
    $user = User::factory()->create();

    $this->mock(SubscriptionSyncer::class, function ($mock) use ($user) {
        $mock->shouldReceive('refreshPaidAccess')
            ->once()
            ->withArgs(fn ($passed) => $passed->is($user));
    });

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));
});

test('a replaced catalog price still counts as the paid plan', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => null]);

    $prices = app(PlanPriceResolver::class);
    $prices->store('pro', 'price_old_pro');
    $prices->store('pro', 'price_new_pro');

    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_old_pro',
        'stripe_status' => 'active',
        'stripe_price' => 'price_old_pro',
        'quantity' => 1,
    ]);

    expect(app(PlanResolver::class)->key($user->fresh()))->toBe('pro')
        ->and(app(PlanResolver::class)->keyForPrice('price_new_pro'))->toBe('pro')
        ->and(app(PlanPriceResolver::class)->idFor('pro'))->toBe('price_new_pro');
});

test('a subscription webhook can find the account from checkout metadata', function () {
    config(['cashier.webhook.secret' => 'whsec_test']);
    $user = User::factory()->create();

    $this->mock(SubscriptionSyncer::class, function ($mock) use ($user) {
        $mock->shouldReceive('syncUser')
            ->once()
            ->withArgs(fn ($passed, $session) => $passed->is($user) && $session === 'cs_sub');
    });

    stripeWebhook($this, [
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_sub',
            'mode' => 'subscription',
            'customer' => 'cus_mike',
            'metadata' => ['user_id' => (string) $user->id],
        ]],
    ])->assertOk();
});

test('a checkout return does not change the plan without a stripe confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/subscription?status=processing')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('plan.key', 'free')->where('status', 'processing'));
});

test('a paid checkout session fulfills the matching pending purchase without a webhook', function () {
    Mail::fake();

    $purchase = Purchase::factory()->pending()->create(['amount' => 1900, 'currency' => 'EUR']);

    expect(app(PurchaseFulfiller::class)->fulfillFromStripeSession([
        'id' => 'cs_test_template',
        'mode' => 'payment',
        'payment_status' => 'paid',
        'amount_total' => 1900,
        'currency' => 'eur',
        'metadata' => ['purchase_id' => (string) $purchase->id],
    ]))->toBeTrue();

    expect($purchase->fresh()->status->value)->toBe('paid')
        ->and($purchase->fresh()->payment_reference)->toBe('cs_test_template');
});

test('a template checkout return asks stripe for the session id', function () {
    $user = User::factory()->create();

    $this->mock(PurchaseSyncer::class, function ($mock) use ($user) {
        $mock->shouldReceive('syncUser')
            ->once()
            ->withArgs(fn ($passed, $session) => $passed->is($user) && $session === 'cs_test_pay');
    });

    $this->actingAs($user)
        ->get('/dashboard/purchases?status=processing&session_id=cs_test_pay')
        ->assertOk();
});

test('a checkout return asks stripe for the session id without trusting the url', function () {
    $user = User::factory()->create();

    $this->mock(SubscriptionSyncer::class, function ($mock) use ($user) {
        $mock->shouldReceive('syncUser')
            ->once()
            ->withArgs(fn ($passed, $session) => $passed->is($user) && $session === 'cs_test_123');
    });

    $this->actingAs($user)
        ->get('/dashboard/subscription?status=processing&session_id=cs_test_123')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('plan.key', 'free'));
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
    $premiumTool = Tool::factory()->premium()->create();

    $this->actingAs($free);
    for ($uses = 0; $uses < 5; $uses++) {
        $this->get('/tools/'.$premiumTool->slug)->assertOk();
    }

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

test('an older active subscription still unlocks pro when a newer checkout is incomplete', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $tool = Tool::factory()->premium()->create();
    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_first_pro',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
        'created_at' => now()->subDay(),
    ]);
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_second_incomplete',
        'stripe_status' => 'incomplete',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
        'created_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/tools/'.$tool->slug)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('locked', false)
            ->where('plan', 'pro')
            ->where('trial.window', 'subscription'));
});

test('a pro account must pay through stripe checkout to start business', function () {
    config([
        'cashier.secret' => 'sk_test_fake',
        'quicksolve.plans.pro.stripe_price_id' => 'price_pro',
        'quicksolve.plans.business.stripe_price_id' => 'price_biz',
    ]);

    $user = User::factory()->create(['stripe_id' => 'cus_upgrade']);
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_pro_to_keep',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->postJson('/api/v1/billing/checkout', ['plan' => 'business'])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Checkout could not be started. Confirm Stripe test mode is configured and try again from Admin → Billing.');

    expect($user->fresh()->subscriptions)->toHaveCount(1)
        ->and($user->subscriptions()->first()->stripe_price)->toBe('price_pro')
        ->and(app(PlanResolver::class)->key($user->fresh()))->toBe('pro');
});

test('an overlapping pro subscription is cancelled when business is already active', function () {
    config([
        'cashier.secret' => 'sk_test_fake',
        'quicksolve.plans.pro.stripe_price_id' => 'price_pro',
        'quicksolve.plans.business.stripe_price_id' => 'price_biz',
    ]);

    $user = User::factory()->create(['stripe_id' => 'cus_both']);
    $pro = $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_leftover_pro',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_business_keep',
        'stripe_status' => 'active',
        'stripe_price' => 'price_biz',
        'quantity' => 1,
    ]);

    app(SubscriptionSyncer::class)->reconcileOverlappingPlans($user->fresh());

    expect($pro->fresh()->stripe_status)->toBe('canceled')
        ->and($pro->fresh()->ends_at)->not->toBeNull()
        ->and(app(PlanResolver::class)->key($user->fresh()))->toBe('business');
});

test('an active pro account cannot start a second pro checkout', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_already_pro',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->postJson('/api/v1/billing/checkout', ['plan' => 'pro'])
        ->assertStatus(422)
        ->assertJsonPath('message', 'This account already has an active paid subscription.');
});

test('premium tools stay available for five trial uses then lock', function () {
    $tool = Tool::factory()->premium()->create();

    for ($uses = 0; $uses < 5; $uses++) {
        $this->get('/tools/'.$tool->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('locked', false)->where('premium', true));
    }

    $this->get('/tools/'.$tool->slug)->assertOk()->assertInertia(fn ($page) => $page->where('locked', true));
});

test('pdf and image tools stay locked after trial uses until a verified plan is active', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $this->seed();

    for ($uses = 0; $uses < 5; $uses++) {
        $this->get('/tools/pdf-viewer-editor')->assertOk()->assertInertia(fn ($page) => $page->where('locked', false)->where('tool.slug', 'pdf-viewer-editor'));
    }

    $this->get('/tools/pdf-viewer-editor')->assertOk()->assertInertia(fn ($page) => $page->where('locked', true));
    $this->get('/tools/image-resizer')->assertOk()->assertInertia(fn ($page) => $page->where('locked', true)->where('tool.slug', 'image-resizer'));

    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_media_tools',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->get('/tools/pdf-viewer-editor')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('locked', false));
});

test('the image converter page names png ico jpeg svg and pdf in its copy', function () {
    $this->seed();

    $tool = Tool::query()->where('slug', 'image-converter')->firstOrFail();

    expect($tool->description)->toContain('PNG')
        ->and($tool->description)->toContain('ICO')
        ->and($tool->description)->toContain('JPEG')
        ->and($tool->description)->toContain('SVG')
        ->and($tool->long_description)->toContain('not a traced vector');

    $this->get('/tools/image-converter')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tool.slug', 'image-converter')
            ->where('locked', false)
            ->where('tool.description', $tool->description));
});

test('the pdf editor page names merge split and compress in its copy', function () {
    $this->seed();

    $tool = Tool::query()->where('slug', 'pdf-viewer-editor')->firstOrFail();

    expect($tool->description)->toContain('merge or split')
        ->and($tool->long_description)->toContain('Click a line to type');

    $this->get('/tools/pdf-viewer-editor')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tool.slug', 'pdf-viewer-editor')
            ->where('tool.description', $tool->description));
});

test('the new catalog tools and templates are published', function () {
    $this->seed();

    foreach ([
        'image-converter',
        'break-even-calculator',
        'timesheet-calculator',
        'serp-snippet-preview',
        'utm-link-builder',
        'marketplace-fee-calculator',
        'roas-calculator',
        'jwt-decoder',
        'regex-tester',
        'date-difference-calculator',
        'unit-converter',
        'random-number-generator',
        'http-status-code-lookup',
        'html-validator',
        'css-validator',
    ] as $slug) {
        $this->get('/tools/'.$slug)->assertOk()->assertInertia(fn ($page) => $page->where('tool.slug', $slug)->where('locked', false));
    }

    $this->get('/tools/business-card-maker')->assertOk()->assertInertia(fn ($page) => $page->where('tool.slug', 'business-card-maker')->where('locked', false));
    $this->get('/tools/cv-builder')->assertOk()->assertInertia(fn ($page) => $page->where('tool.slug', 'cv-builder')->where('locked', false));
    $this->get('/tools/email-templates')->assertOk()->assertInertia(fn ($page) => $page->where('tool.slug', 'email-templates')->where('locked', false));

    $this->get('/templates/freelancer-finance-spreadsheet')->assertOk();
    $this->get('/templates/business-launch-checklist')->assertOk();
});

test('missing stripe prices are created and stored for checkout', function () {
    config([
        'cashier.secret' => 'sk_test_fake',
        'quicksolve.plans.pro.stripe_price_id' => null,
        'quicksolve.plans.business.stripe_price_id' => null,
    ]);

    $this->mock(StripePlanCatalog::class, function ($mock) {
        $mock->shouldReceive('findOrCreatePrice')
            ->twice()
            ->andReturn('price_pro_live', 'price_biz_live');
    });

    $ids = app(PlanPriceResolver::class)->ensurePaidPlans();

    expect($ids['pro'])->toBe('price_pro_live')
        ->and(Setting::get('stripe.pro_price_id'))->toBe('price_pro_live')
        ->and(app(PlanResolver::class)->keyForPrice('price_pro_live'))->toBe('pro');
});

test('admins can save billing settings, manage users, and mark refunds', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $purchase = Purchase::factory()->create();
    $tool = Tool::factory()->create(['is_published' => true]);

    $this->actingAs($admin)
        ->put('/admin/billing', [
            'pro_price_id' => 'price_abc123',
            'business_price_id' => 'price_def456',
        ])
        ->assertRedirect();

    expect(app(PlanPriceResolver::class)->idFor('pro'))->toBe('price_abc123');

    $this->actingAs($admin)->get('/admin')->assertOk()->assertInertia(fn ($page) => $page
        ->has('stats.totals.users')
        ->has('stats.activity')
        ->has('stats.top_tools')
        ->has('stats.purchase_status')
        ->has('stats.revenue'));

    $this->actingAs($admin)->get('/admin/users')->assertOk();
    $this->actingAs($admin)->put('/admin/users/'.$member->id, [
        'name' => $member->name,
        'email' => $member->email,
        'is_admin' => true,
        'email_verified' => true,
    ])->assertRedirect();
    expect($member->fresh()->is_admin)->toBeTrue();

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Pat Member',
        'email' => 'pat@example.com',
        'password' => 'password',
        'is_admin' => false,
        'email_verified' => true,
    ])->assertRedirect();

    $created = User::query()->where('email', 'pat@example.com')->first();
    expect($created)->not->toBeNull();
    $this->actingAs($admin)->delete('/admin/users/'.$created->id)->assertRedirect();
    expect(User::query()->where('email', 'pat@example.com')->exists())->toBeFalse();

    $message = ContactMessage::query()->create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'subject' => 'Hello',
        'message' => 'Need a quote',
        'is_read' => false,
    ]);

    $this->actingAs($admin)->post('/admin/contacts/'.$message->id.'/read')->assertRedirect();
    expect($message->fresh()->is_read)->toBeTrue();

    $this->actingAs($admin)->post('/admin/tools/'.$tool->id.'/toggle')->assertRedirect();
    expect($tool->fresh()->is_published)->toBeFalse();

    $this->actingAs($admin)->post('/admin/purchases/'.$purchase->id.'/refund')->assertRedirect();
    expect($purchase->fresh()->status->value)->toBe('refunded');

    $this->actingAs($admin)->post('/admin/purchases', [
        'user_id' => $member->id,
        'template_id' => $purchase->template_id,
        'amount' => 1200,
        'currency' => 'EUR',
        'status' => 'paid',
    ])->assertRedirect();

    $this->actingAs($admin)->get('/admin/subscriptions')->assertOk();
    $this->actingAs($admin)->post('/admin/subscriptions', [
        'user_id' => $member->id,
        'type' => 'default',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ])->assertRedirect();

    $subscription = Subscription::query()->where('user_id', $member->id)->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->stripe_status)->toBe('active')
        ->and($subscription->items()->count())->toBe(1);

    $this->actingAs($admin)->put('/admin/subscriptions/'.$subscription->id, [
        'user_id' => $member->id,
        'type' => 'default',
        'stripe_id' => $subscription->stripe_id,
        'stripe_status' => 'active',
        'stripe_price' => 'price_business',
        'quantity' => 1,
    ])->assertRedirect();
    expect($subscription->fresh()->stripe_price)->toBe('price_business');

    $this->actingAs($admin)->post('/admin/subscriptions/'.$subscription->id.'/cancel')->assertRedirect();
    expect($subscription->fresh()->stripe_status)->toBe('canceled');

    $this->actingAs($admin)->delete('/admin/subscriptions/'.$subscription->id)->assertRedirect();
    expect(Subscription::query()->whereKey($subscription->id)->exists())->toBeFalse();

    $this->actingAs($member)->post('/admin/subscriptions', [
        'user_id' => $member->id,
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ])->assertForbidden();

    expect(Purchase::query()->where('user_id', $member->id)->where('amount', 1200)->exists())->toBeTrue();

    $this->actingAs($admin)->post('/admin/settings', [
        'key' => 'admin.note',
        'value' => 'keep this short',
    ])->assertRedirect();
    expect(Setting::get('admin.note'))->toBe('keep this short');

    $document = GeneratedDocument::factory()->create(['user_id' => $member->id]);
    $this->actingAs($admin)->put('/admin/documents/'.$document->id, ['title' => 'Renamed invoice'])->assertRedirect();
    expect($document->fresh()->title)->toBe('Renamed invoice');

    $event = UsageEvent::query()->create([
        'user_id' => $member->id,
        'tool_slug' => 'profit-margin-calculator',
        'usage_type' => 'tool_opened',
    ]);
    $this->actingAs($admin)->delete('/admin/usage/'.$event->id)->assertRedirect();
    expect(UsageEvent::query()->whereKey($event->id)->exists())->toBeFalse();

    $this->actingAs($admin)->post('/admin/contacts', [
        'name' => 'Bo',
        'email' => 'bo@example.com',
        'subject' => 'Quote',
        'message' => 'Can you help?',
        'is_read' => false,
    ])->assertRedirect();
    expect(ContactMessage::query()->where('email', 'bo@example.com')->exists())->toBeTrue();
});

test('cv pdf downloads during trial uses and lock after five visits', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $this->seed();

    $payload = [
        'template' => 'classic',
        'full_name' => 'Ada North',
        'headline' => 'Designer',
        'email' => 'ada@example.com',
        'skills' => ['Figma'],
        'experience' => [['role' => 'Designer', 'company' => 'Studio', 'dates' => '2024', 'details' => 'Shipped UI']],
        'education' => [['school' => 'Art school', 'credential' => 'BA', 'dates' => '2020']],
    ];

    $this->get('/tools/cv-builder')->assertOk()->assertInertia(fn ($page) => $page->where('locked', false));
    $this->post('/tools/cv-builder/pdf', $payload)->assertOk();

    for ($uses = 1; $uses < 5; $uses++) {
        $this->get('/tools/cv-builder')->assertOk();
    }

    $this->get('/tools/cv-builder')->assertOk()->assertInertia(fn ($page) => $page->where('locked', true));

    $pro = User::factory()->create();
    $pro->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_cv',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($pro)->post('/tools/cv-builder/pdf', $payload)->assertOk();

    foreach ([
        'executive', 'timeline', 'creative', 'split', 'banner', 'mosaic',
        'atlantic', 'portrait', 'mono', 'academic', 'sand', 'rose', 'slate', 'forest',
    ] as $template) {
        $this->actingAs($pro)->post('/tools/cv-builder/pdf', [...$payload, 'template' => $template])->assertOk();
    }

    $jpeg = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGcP//Z';
    $this->actingAs($pro)->post('/tools/cv-builder/pdf', [...$payload, 'template' => 'rose', 'photo' => $jpeg])->assertOk();
    $this->actingAs($pro)->post('/tools/cv-builder/pdf', [...$payload, 'template' => 'unknown'])->assertSessionHasErrors('template');
    $this->actingAs($pro)->post('/tools/cv-builder/pdf', [...$payload, 'photo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='])->assertSessionHasErrors('photo');
});

test('custom cv templates require hex colors and download a pdf', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $this->seed();

    $pro = User::factory()->create();
    $pro->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_cv_theme',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $theme = [
        'name' => 'Studio coral',
        'layout' => 'sidebar_left',
        'paper' => '#fff8f4',
        'ink' => '#3f2e2a',
        'muted' => '#8d6e68',
        'accent' => '#e07a5f',
        'sidebar' => '#e07a5f',
        'sidebar_ink' => '#fff7f3',
        'heading' => '#c45c40',
        'type' => 'sans',
        'name_size' => 'lg',
        'photo' => 'circle',
        'header_align' => 'left',
        'skills' => 'chips',
    ];
    $payload = [
        'template' => 'custom',
        'full_name' => 'Ada North',
        'headline' => 'Designer',
        'theme' => $theme,
    ];

    $this->get('/tools/cv-builder')->assertOk()->assertInertia(fn ($page) => $page
        ->has('cvThemes', 11)
        ->where('cvThemes.0.id', 'coral'));

    foreach (['coral', 'blush', 'sky', 'mustard', 'ember', 'sage', 'lilac', 'navygold', 'citrus', 'violet', 'harbor'] as $template) {
        $this->actingAs($pro)->post('/tools/cv-builder/pdf', [
            'template' => $template,
            'full_name' => 'Ada North',
        ])->assertOk();
    }

    $this->actingAs($pro)->post('/tools/cv-builder/pdf', $payload)->assertOk();
    $this->actingAs($pro)->post('/tools/cv-builder/pdf', [...$payload, 'theme' => null])->assertSessionHasErrors('theme');
    $this->actingAs($pro)->post('/tools/cv-builder/pdf', [
        ...$payload,
        'theme' => [...$theme, 'accent' => 'red;background:url(javascript:alert(1))'],
    ])->assertSessionHasErrors('theme.accent');
});

test('json resume import maps overlapping fields and skips unmapped sections', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $this->seed();

    $this->postJson('/tools/cv-builder/json-resume/import', [
        'template' => 'atlantic',
        'resume' => [
            'basics' => [
                'name' => 'Lorna Alvarado',
                'label' => 'Sales Representative',
                'email' => 'lorna@example.com',
                'phone' => '555-0100',
                'url' => 'https://lorna.example',
                'summary' => 'Sells catalog tools to small teams.',
                'location' => ['city' => 'Tallinn', 'countryCode' => 'EE'],
            ],
            'work' => [[
                'name' => 'North Studio',
                'position' => 'Sales lead',
                'startDate' => '2022-01',
                'highlights' => ['Closed retail accounts'],
            ]],
            'education' => [[
                'institution' => 'Art school',
                'studyType' => 'BA',
                'area' => 'Design',
                'startDate' => '2016',
                'endDate' => '2020',
            ]],
            'skills' => [['name' => 'Salesforce', 'keywords' => ['CRM']]],
            'awards' => [['title' => 'Should not appear']],
            'volunteer' => [['organization' => 'Skipped']],
        ],
    ])->assertOk()
        ->assertJsonPath('data.full_name', 'Lorna Alvarado')
        ->assertJsonPath('data.headline', 'Sales Representative')
        ->assertJsonPath('data.location', 'Tallinn, EE')
        ->assertJsonPath('data.experience.0.company', 'North Studio')
        ->assertJsonPath('data.education.0.school', 'Art school')
        ->assertJsonMissing(['Should not appear'])
        ->assertJsonMissing(['Skipped']);

    $this->post('/tools/cv-builder/json-resume/export', [
        'template' => 'classic',
        'full_name' => 'Ada North',
        'headline' => 'Designer',
        'email' => 'ada@example.com',
        'skills' => ['Figma'],
        'experience' => [['role' => 'Designer', 'company' => 'Studio', 'dates' => '2022 — 2024', 'details' => 'Shipped UI']],
        'education' => [['school' => 'Art school', 'credential' => 'BA', 'dates' => '2016 — 2020']],
    ])->assertOk()
        ->assertHeader('content-type', 'application/json; charset=UTF-8')
        ->assertSee('$schema', false)
        ->assertSee('Ada North', false)
        ->assertSee('2022', false);
});

test('json resume registry loads only the official host', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    Http::preventStrayRequests();
    Http::fake([
        'https://registry.jsonresume.org/thomasdavis.json' => Http::response([
            'basics' => ['name' => 'Thomas Davis', 'label' => 'Web developer'],
        ], 200),
        'https://registry.jsonresume.org/missing-user.json' => Http::response(['error' => 'not found'], 404),
    ]);
    $this->seed();

    $this->postJson('/tools/cv-builder/json-resume/registry', [
        'username' => 'thomasdavis',
        'template' => 'classic',
    ])->assertOk()->assertJsonPath('data.full_name', 'Thomas Davis');

    $this->postJson('/tools/cv-builder/json-resume/registry', [
        'username' => 'missing-user',
    ])->assertNotFound();

    $this->postJson('/tools/cv-builder/json-resume/registry', [
        'username' => 'https://evil.example/steal',
    ])->assertUnprocessable()->assertJsonValidationErrors('username');
});

test('cv deepseek chat stays in demo mode without a provider key', function () {
    config(['quicksolve.ai.api_key' => null]);
    $this->seed();

    $this->postJson('/api/v1/tools/cv-builder/generate', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['instruction', 'cv']);

    $response = $this->postJson('/api/v1/tools/cv-builder/generate', [
        'instruction' => 'Tighten the summary',
        'cv' => [
            'full_name' => 'Ada North',
            'summary' => 'Designer who ships clear interfaces for small teams. Comfortable from research through handoff and workshops.',
            'skills' => ['Figma'],
            'experience' => [['role' => 'Designer', 'company' => 'Studio']],
        ],
    ]);

    $response->assertOk()->assertJsonPath('data.mode', 'demo');
    expect($response->json('data.patch.summary'))->toBeString()->not->toBeEmpty();

    expect($response->json('data.notice'))->toContain('Demo mode');
    expect($response->json('data.reply'))->toContain('shorter summary');
    expect(UsageEvent::query()->where('tool_slug', 'cv-builder')->where('usage_type', 'generated')->count())->toBe(1);
});

test('a failed cv chat provider does not return demo copy', function () {
    config(['quicksolve.ai.api_key' => 'test-key']);
    Http::preventStrayRequests();
    Http::fake(['api.deepseek.com/*' => Http::response(['error' => 'unavailable'], 503)]);
    $this->seed();

    $this->postJson('/api/v1/tools/cv-builder/generate', [
        'instruction' => 'Tighten the summary',
        'cv' => [
            'full_name' => 'Ada',
            'summary' => 'Designer who ships interfaces.',
        ],
    ])->assertStatus(503);

    expect(UsageEvent::query()->where('tool_slug', 'cv-builder')->where('usage_type', 'generated')->count())->toBe(0);
});

test('email template drafts download during trial uses', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $this->seed();

    $payload = [
        'template' => 'quote',
        'from_name' => 'Ada North',
        'from_role' => 'Studio lead',
        'to_name' => 'Kai',
        'company' => 'North Studio',
        'topic' => 'catalog redesign',
        'amount' => '1200 EUR',
    ];

    $this->get('/tools/email-templates')->assertOk()->assertInertia(fn ($page) => $page->where('locked', false));
    $this->post('/tools/email-templates/download', $payload)
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8')
        ->assertSee('Quote for North Studio')
        ->assertSee('1200 EUR');

    $pro = User::factory()->create();
    $pro->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_email',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($pro)->post('/tools/email-templates/download', [...$payload, 'template' => 'follow-up'])
        ->assertOk()
        ->assertSee('Following up on catalog redesign');

    $this->actingAs($pro)->post('/tools/email-templates/download', [...$payload, 'tone' => 'formal'])
        ->assertOk()
        ->assertSee('Dear Kai')
        ->assertSee('Quotation for North Studio')
        ->assertSee('Yours sincerely');

    $this->actingAs($pro)->post('/tools/email-templates/download', [...$payload, 'template' => 'thank-you', 'tone' => 'formal'])
        ->assertOk()
        ->assertSee('Thank you for North Studio');

    $this->actingAs($pro)->post('/tools/email-templates/download', [...$payload, 'template' => 'apology'])
        ->assertOk()
        ->assertSee('Sorry about catalog redesign');

    $this->actingAs($pro)->post('/tools/email-templates/download', [...$payload, 'template' => 'partnership', 'tone' => 'formal'])
        ->assertOk()
        ->assertSee('Proposal for collaboration');
});

test('email deepseek drafts stay in demo mode without a provider key', function () {
    config(['quicksolve.ai.api_key' => null]);
    $this->seed();

    $this->postJson('/api/v1/tools/email-templates/generate', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['topic', 'instruction', 'from_name', 'to_name', 'situation', 'tone']);

    $response = $this->postJson('/api/v1/tools/email-templates/generate', [
        'topic' => 'catalog redesign',
        'situation' => 'sales',
        'tone' => 'formal',
        'length' => 'short',
        'instruction' => 'Ask whether they want to go ahead',
        'from_name' => 'Ada North',
        'to_name' => 'Kai',
        'company' => 'North Studio',
        'facts' => 'Quote sent last week',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.mode', 'demo')
        ->assertJsonPath('data.subject', 'Regarding catalog redesign');

    expect($response->json('data.notice'))->toContain('Demo mode');
    expect($response->json('data.body'))->toContain('Dear Kai');
    expect(UsageEvent::query()->where('tool_slug', 'email-templates')->where('usage_type', 'generated')->count())->toBe(1);
});

test('a failed email provider does not return demo copy', function () {
    config(['quicksolve.ai.api_key' => 'test-key']);
    Http::fake(['*' => Http::response(['error' => 'unavailable'], 503)]);
    $this->seed();

    $this->postJson('/api/v1/tools/email-templates/generate', [
        'topic' => 'catalog redesign',
        'situation' => 'sales',
        'tone' => 'casual',
        'length' => 'short',
        'instruction' => 'Write a follow-up',
        'from_name' => 'Ada',
        'to_name' => 'Kai',
    ])->assertStatus(503);

    expect(UsageEvent::query()->where('tool_slug', 'email-templates')->where('usage_type', 'generated')->count())->toBe(0);
});

test('admins can update a template without uploading a new file', function () {
    $admin = User::factory()->admin()->create();
    $template = Template::factory()->create([
        'name' => 'Old pack',
        'price' => 1900,
        'currency' => 'EUR',
        'is_published' => true,
        'private_file_path' => 'templates/old-pack.csv',
    ]);

    $this->actingAs($admin)
        ->post('/admin/templates/'.$template->id, [
            '_method' => 'put',
            'category_id' => $template->category_id,
            'name' => 'Starter pack',
            'slug' => $template->slug,
            'description' => 'Updated description for the pack.',
            'whats_included' => "Sheet\nNotes",
            'price' => 2500,
            'currency' => 'EUR',
            'is_published' => '1',
        ])
        ->assertRedirect();

    expect($template->fresh()->name)->toBe('Starter pack')
        ->and($template->fresh()->price)->toBe(2500)
        ->and($template->fresh()->whats_included)->toBe(['Sheet', 'Notes'])
        ->and($template->fresh()->private_file_path)->toBe('templates/old-pack.csv');
});

test('payment landing date and scope change note are published premium tools', function () {
    $this->seed();

    foreach (['payment-landing-date', 'scope-change-note'] as $slug) {
        $this->get('/tools/'.$slug)->assertOk()->assertInertia(fn ($page) => $page->where('tool.slug', $slug)->where('locked', false)->where('premium', true));
    }

    $this->postJson('/tools/payment-landing-date/calculate', [
        'issue_date' => '2026-10-01',
        'terms' => 'net_30',
        'pay_run' => 'weekly',
        'weekday' => 5,
        'skip_weekends' => true,
        'settlement_days' => 2,
        'holiday_dates' => [],
    ])->assertOk()
        ->assertJsonPath('data.contractual_due', '2026-10-31')
        ->assertJsonPath('data.likely_pay_date', '2026-11-06')
        ->assertJsonPath('data.likely_land_date', '2026-11-09');

    $this->postJson('/tools/payment-landing-date/calculate', [
        'issue_date' => '2026-10-01',
        'terms' => 'net_7',
        'pay_run' => 'any',
        'skip_weekends' => true,
        'settlement_days' => 0,
        'holiday_dates' => ['2026-10-08'],
    ])->assertOk()
        ->assertJsonPath('data.contractual_due', '2026-10-08')
        ->assertJsonPath('data.likely_land_date', '2026-10-09');

    $this->post('/tools/scope-change-note/pdf', [
        'from_name' => 'Ada North',
        'client_name' => 'Kai West',
        'project_name' => 'Catalog site',
        'agreed_work' => 'Five product templates.',
        'extra_request' => 'Also migrate the old shop.',
        'extra_amount' => '450.00',
        'currency' => 'EUR',
        'hold_until_confirmed' => '1',
    ])->assertOk()->assertHeader('content-type', 'application/pdf');
});

test('quote builder cover letter and cash runway are published premium tools', function () {
    config(['quicksolve.plans.pro.stripe_price_id' => 'price_pro']);
    $this->seed();

    foreach (['quote-builder', 'cover-letter', 'cash-runway-calculator'] as $slug) {
        $this->get('/tools/'.$slug)->assertOk()->assertInertia(fn ($page) => $page->where('tool.slug', $slug)->where('locked', false)->where('premium', true));
    }

    $quote = [
        'business_name' => 'North Studio',
        'customer_name' => 'Kai West',
        'quote_number' => 'Q-9',
        'issue_date' => '2026-10-01',
        'valid_until' => '2026-10-15',
        'currency' => 'EUR',
        'tax_rate' => 0,
        'items' => [['description' => 'Catalog', 'quantity' => 1, 'unit_price' => 120]],
    ];

    $this->post('/tools/quote-builder/pdf', $quote)->assertOk();
    $this->post('/tools/cover-letter/pdf', [
        'full_name' => 'Ada North',
        'recipient_name' => 'Kai West',
        'company' => 'North Studio',
        'role' => 'Designer',
        'body' => 'I shipped the catalog redesign last quarter.',
    ])->assertOk();

    $pro = User::factory()->create();
    $pro->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_quote',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);

    $this->actingAs($pro)->post('/quotes', $quote)->assertRedirect(route('dashboard.documents'));
    expect(GeneratedDocument::query()->where('user_id', $pro->id)->where('document_type', 'quote')->count())->toBe(1);
});

test('document branding and csv export follow the plan', function () {
    config([
        'quicksolve.plans.pro.stripe_price_id' => 'price_pro',
        'quicksolve.plans.business.stripe_price_id' => 'price_biz',
    ]);

    $free = User::factory()->create();
    $pro = User::factory()->create();
    $business = User::factory()->create();

    $pro->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_brand_pro',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro',
        'quantity' => 1,
    ]);
    $business->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_brand_biz',
        'stripe_status' => 'active',
        'stripe_price' => 'price_biz',
        'quantity' => 1,
    ]);

    $svc = app(DocumentBranding::class);

    expect($svc->forUser($free, '#ff0000', 'Secret footer', 'invoice'))
        ->toMatchArray(['accent' => '#0f172a', 'branded' => true, 'custom_footer' => ''])
        ->and($svc->forUser($pro, '#1d4ed8', 'Secret footer', 'invoice'))
        ->toMatchArray(['accent' => '#1d4ed8', 'branded' => true, 'custom_footer' => ''])
        ->and($svc->forUser($business, '#1d4ed8', 'North Studio confidential', 'quote'))
        ->toMatchArray(['accent' => '#1d4ed8', 'branded' => false, 'custom_footer' => 'North Studio confidential']);

    $this->actingAs($pro)->get('/dashboard/documents/export')->assertForbidden();

    $documents = $this->actingAs($business)->get('/dashboard/documents/export');
    $documents->assertOk();
    expect($documents->streamedContent())->toContain('id,type,title,created_at');

    $usage = $this->actingAs($business)->get('/dashboard/usage/export');
    $usage->assertOk();
    expect($usage->streamedContent())->toContain('id,tool,usage_type,created_at');
});

test('home chat stays in demo mode without a provider key', function () {
    config(['quicksolve.ai.api_key' => null]);

    $this->postJson('/api/v1/home/chat', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);

    $response = $this->postJson('/api/v1/home/chat', [
        'message' => 'How do I make an invoice?',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.mode', 'demo')
        ->assertJsonPath('data.links.0.href', '/tools/invoice-generator');

    expect($response->json('data.notice'))->toContain('Demo mode')
        ->and($response->json('data.reply'))->toContain('invoice generator')
        ->and(UsageEvent::query()->where('tool_slug', 'home-chat')->where('usage_type', 'generated')->count())->toBe(1);
});

test('a failed home chat provider does not return demo copy', function () {
    config(['quicksolve.ai.api_key' => 'test-key']);
    Http::fake(['*' => Http::response(['error' => 'unavailable'], 503)]);

    $this->postJson('/api/v1/home/chat', [
        'message' => 'How do I make an invoice?',
    ])->assertStatus(503);

    expect(UsageEvent::query()->where('tool_slug', 'home-chat')->count())->toBe(0);
});

test('home chat drops links that are not QuickSolve paths', function () {
    config(['quicksolve.ai.api_key' => 'test-key']);
    Http::fake([
        '*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'reply' => 'Open the invoice generator.',
                        'links' => [
                            ['label' => 'Invoice', 'href' => '/tools/invoice-generator'],
                            ['label' => 'Phish', 'href' => 'https://evil.example/steal'],
                        ],
                    ]),
                ],
            ]],
        ]),
    ]);

    $this->postJson('/api/v1/home/chat', [
        'message' => 'Where is the invoice tool?',
    ])->assertOk()
        ->assertJsonPath('data.mode', 'live')
        ->assertJsonPath('data.links.0.href', '/tools/invoice-generator')
        ->assertJsonMissing(['href' => 'https://evil.example/steal']);
});

test('public pages send unique titles, open graph tags, and json-ld', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('QuickSolve — Small tools. Smarter business.', false)
        ->assertSee('twitter:card', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('FAQPage', false)
        ->assertSee('name="robots" content="index,follow"', false);
});

test('tool search results are noindex and canonical to the catalog', function () {
    $this->get('/tools?q=invoice')
        ->assertOk()
        ->assertSee('noindex,follow', false)
        ->assertSee('rel="canonical" href="'.url('/tools').'"', false);
});

test('robots.txt blocks private areas and points to the sitemap', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /dashboard', false)
        ->assertSee('Disallow: /admin', false)
        ->assertSee('Sitemap: '.url('/sitemap.xml'), false);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(url('/tools'), false)
        ->assertSee(url('/pricing'), false);
});

test('signed-in dashboard pages ask crawlers not to index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('noindex,nofollow', false);
});

test('the tools catalog is grouped by category', function () {
    $this->seed();

    $this->get('/tools')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('sections')
            ->where('sections.0.slug', 'calculators')
            ->where('toolMenu', function ($menu) {
                $developer = collect($menu)->firstWhere('slug', 'developer-tools');

                expect($developer)->not->toBeNull();

                $slugs = collect($developer['tools'])->pluck('slug');

                return $slugs->contains('html-validator')
                    && $slugs->contains('css-validator')
                    && $slugs->contains('regex-tester');
            }));

    $this->get('/tools?category=developer-tools')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.category', 'developer-tools')
            ->has('sections', 1)
            ->where('sections.0.slug', 'developer-tools'));
});

test('the html validator reports w3c messages and ignores the fragment wrapper', function () {
    Http::preventStrayRequests();
    Http::fake([
        'validator.w3.org/*' => Http::response([
            'messages' => [
                [
                    'type' => 'error',
                    'message' => 'Wrapper problem',
                    'lastLine' => 2,
                    'lastColumn' => 1,
                ],
                [
                    'type' => 'error',
                    'message' => 'Element <code>bogus</code> not allowed.',
                    'lastLine' => 8,
                    'lastColumn' => 2,
                    'extract' => '<bogus>',
                ],
                [
                    'type' => 'info',
                    'subType' => 'warning',
                    'message' => 'Consider adding a heading.',
                    'lastLine' => 8,
                    'lastColumn' => 8,
                ],
            ],
        ]),
    ]);

    $this->postJson('/tools/html-validator/check', [
        'source' => '<bogus></bogus>',
        'mode' => 'fragment',
    ])->assertOk()
        ->assertJsonPath('data.valid', false)
        ->assertJsonPath('data.checked_by', 'W3C Nu Html Checker')
        ->assertJsonCount(2, 'data.messages')
        ->assertJsonPath('data.messages.0.severity', 'error')
        ->assertJsonPath('data.messages.0.line', 1)
        ->assertJsonPath('data.messages.0.message', 'Element bogus not allowed.')
        ->assertJsonPath('data.messages.1.severity', 'warning');
});

test('the css validator reports w3c errors and warnings', function () {
    Http::preventStrayRequests();
    Http::fake([
        'jigsaw.w3.org/*' => Http::response([
            'cssvalidation' => [
                'validity' => false,
                'errors' => [
                    'line' => 2,
                    'message' => 'Property <code>foo</code> does not exist',
                ],
                'warnings' => [
                    ['line' => 1, 'message' => 'Same color for background and text'],
                ],
            ],
        ]),
    ]);

    $this->postJson('/tools/css-validator/check', [
        'source' => 'body { foo: 1; }',
        'profile' => 'css3svg',
    ])->assertOk()
        ->assertJsonPath('data.valid', false)
        ->assertJsonPath('data.checked_by', 'W3C CSS Validator')
        ->assertJsonPath('data.messages.0.severity', 'error')
        ->assertJsonPath('data.messages.0.line', 2)
        ->assertJsonPath('data.messages.0.message', 'Property foo does not exist')
        ->assertJsonPath('data.messages.1.severity', 'warning');
});

test('a long style sheet is still sent to the w3c css validator', function () {
    Http::preventStrayRequests();
    Http::fake([
        'jigsaw.w3.org/*' => Http::response([
            'cssvalidation' => [
                'validity' => true,
                'errors' => [],
                'warnings' => [],
            ],
        ]),
    ]);

    $source = str_repeat("/* section */\n.rule { color: #111; }\n", 800);

    $this->postJson('/tools/css-validator/check', [
        'source' => $source,
        'profile' => 'css3svg',
    ])->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonMissing(['message' => 'This style sheet is too long to send to the W3C CSS Validator. Check a smaller section.']);

    Http::assertSent(function ($request) use ($source) {
        $body = $request->body();

        return $request->method() === 'POST'
            && str_contains($request->url(), 'jigsaw.w3.org/css-validator/validator')
            && strlen($body) > strlen($source)
            && str_contains($body, '/* section */')
            && str_contains($body, '.rule { color: #111; }');
    });
});

test('markup checks fail closed when w3c is unavailable', function () {
    Http::preventStrayRequests();
    Http::fake([
        'validator.w3.org/*' => Http::response('unavailable', 503),
        'jigsaw.w3.org/*' => Http::response('<html></html>', 200),
    ]);

    $this->postJson('/tools/html-validator/check', [
        'source' => '<p>Hi</p>',
        'mode' => 'document',
    ])->assertStatus(503);

    $this->postJson('/tools/css-validator/check', [
        'source' => 'body { color: red; }',
    ])->assertStatus(503);
});

test('markup source is required', function () {
    $this->postJson('/tools/html-validator/check', [
        'source' => '',
    ])->assertUnprocessable()->assertJsonValidationErrors(['source']);
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
