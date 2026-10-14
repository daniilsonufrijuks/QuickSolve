<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FreelanceRateReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JsonResumeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentLandingController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ScopeChangeController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{slug}', [ToolController::class, 'show'])->name('tools.show');
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/{slug}', [TemplateController::class, 'show'])->name('templates.show');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::post('/tools/freelance-rate-calculator/report', FreelanceRateReportController::class)
    ->middleware(['auth', 'verified', 'throttle:pdf'])
    ->name('tools.freelance-report');
Route::post('/tools/invoice-generator/pdf', [InvoiceController::class, 'pdf'])
    ->middleware('throttle:pdf')
    ->name('invoices.pdf');
Route::post('/tools/cv-builder/pdf', [CvController::class, 'pdf'])
    ->middleware('throttle:pdf')
    ->name('tools.cv-pdf');
Route::post('/tools/cv-builder/json-resume/import', [JsonResumeController::class, 'import'])
    ->middleware('throttle:pdf')
    ->name('tools.cv-json-import');
Route::post('/tools/cv-builder/json-resume/export', [JsonResumeController::class, 'export'])
    ->middleware('throttle:pdf')
    ->name('tools.cv-json-export');
Route::post('/tools/cv-builder/json-resume/registry', [JsonResumeController::class, 'registry'])
    ->middleware('throttle:pdf')
    ->name('tools.cv-json-registry');
Route::post('/tools/email-templates/download', [EmailTemplateController::class, 'download'])
    ->middleware('throttle:pdf')
    ->name('tools.email-templates-download');
Route::post('/tools/quote-builder/pdf', [QuoteController::class, 'pdf'])
    ->middleware('throttle:pdf')
    ->name('tools.quote-pdf');
Route::post('/tools/cover-letter/pdf', [CoverLetterController::class, 'pdf'])
    ->middleware('throttle:pdf')
    ->name('tools.cover-letter-pdf');
Route::post('/tools/payment-landing-date/calculate', PaymentLandingController::class)
    ->middleware('throttle:pdf')
    ->name('tools.payment-landing-date');
Route::post('/tools/scope-change-note/pdf', [ScopeChangeController::class, 'pdf'])
    ->middleware('throttle:pdf')
    ->name('tools.scope-change-pdf');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/profile', [ProfileController::class, 'edit'])->name('dashboard.profile');
    Route::get('/dashboard/subscription', [DashboardController::class, 'subscription'])->name('dashboard.subscription');
    Route::post('/dashboard/subscription/sync', [DashboardController::class, 'syncSubscription'])->name('dashboard.subscription.sync');
    Route::get('/dashboard/purchases', [DashboardController::class, 'purchases'])->name('dashboard.purchases');
    Route::post('/dashboard/purchases/sync', [DashboardController::class, 'syncPurchases'])->name('dashboard.purchases.sync');
    Route::get('/dashboard/downloads', [DashboardController::class, 'downloads'])->name('dashboard.downloads');
    Route::get('/dashboard/documents', [DashboardController::class, 'documents'])->name('dashboard.documents');
    Route::get('/dashboard/documents/export', [DashboardController::class, 'exportDocuments'])->name('dashboard.documents.export');
    Route::get('/dashboard/usage/export', [DashboardController::class, 'exportUsage'])->name('dashboard.usage.export');

    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/file', [InvoiceController::class, 'file'])->name('invoices.file');

    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/templates/{template}/checkout', [BillingController::class, 'template'])->name('templates.checkout');
    Route::get('/downloads/{purchase}', DownloadController::class)->name('downloads.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/billing', [AdminController::class, 'billing'])->name('billing');
    Route::put('/billing', [AdminController::class, 'updateBilling'])->name('billing.update');
    Route::post('/billing/sync', [AdminController::class, 'syncBilling'])->name('billing.sync');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/contacts', [AdminController::class, 'contacts'])->name('contacts');
    Route::post('/contacts', [AdminController::class, 'storeContact'])->name('contacts.store');
    Route::put('/contacts/{message}', [AdminController::class, 'updateContact'])->name('contacts.update');
    Route::post('/contacts/{message}/read', [AdminController::class, 'markContactRead'])->name('contacts.read');
    Route::delete('/contacts/{message}', [AdminController::class, 'destroyContact'])->name('contacts.destroy');
    Route::get('/tools', [AdminController::class, 'tools'])->name('tools');
    Route::post('/tools', [AdminController::class, 'storeTool'])->name('tools.store');
    Route::put('/tools/{tool}', [AdminController::class, 'updateTool'])->name('tools.update');
    Route::post('/tools/{tool}/toggle', [AdminController::class, 'toggleTool'])->name('tools.toggle');
    Route::delete('/tools/{tool}', [AdminController::class, 'destroyTool'])->name('tools.destroy');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/templates', [AdminController::class, 'templates'])->name('templates');
    Route::post('/templates', [AdminController::class, 'storeTemplate'])->name('templates.store');
    Route::put('/templates/{template}', [AdminController::class, 'updateTemplate'])->name('templates.update');
    Route::post('/templates/{template}/toggle', [AdminController::class, 'toggleTemplate'])->name('templates.toggle');
    Route::delete('/templates/{template}', [AdminController::class, 'destroyTemplate'])->name('templates.destroy');
    Route::get('/purchases', [AdminController::class, 'purchases'])->name('purchases');
    Route::post('/purchases', [AdminController::class, 'storePurchase'])->name('purchases.store');
    Route::put('/purchases/{purchase}', [AdminController::class, 'updatePurchase'])->name('purchases.update');
    Route::post('/purchases/{purchase}/refund', [AdminController::class, 'refundPurchase'])->name('purchases.refund');
    Route::delete('/purchases/{purchase}', [AdminController::class, 'destroyPurchase'])->name('purchases.destroy');
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/subscriptions', [AdminController::class, 'storeSubscription'])->name('subscriptions.store');
    Route::put('/subscriptions/{subscription}', [AdminController::class, 'updateSubscription'])->name('subscriptions.update');
    Route::post('/subscriptions/{subscription}/cancel', [AdminController::class, 'cancelSubscription'])->name('subscriptions.cancel');
    Route::delete('/subscriptions/{subscription}', [AdminController::class, 'destroySubscription'])->name('subscriptions.destroy');
    Route::get('/usage', [AdminController::class, 'usage'])->name('usage');
    Route::delete('/usage/{event}', [AdminController::class, 'destroyUsageEvent'])->name('usage.destroy');
    Route::get('/documents', [AdminController::class, 'documents'])->name('documents');
    Route::put('/documents/{document}', [AdminController::class, 'updateDocument'])->name('documents.update');
    Route::delete('/documents/{document}', [AdminController::class, 'destroyDocument'])->name('documents.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'storeSetting'])->name('settings.store');
    Route::put('/settings/{setting}', [AdminController::class, 'updateSetting'])->name('settings.update');
    Route::delete('/settings/{setting}', [AdminController::class, 'destroySetting'])->name('settings.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
