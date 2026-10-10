<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FreelanceRateReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
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

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/profile', [ProfileController::class, 'edit'])->name('dashboard.profile');
    Route::get('/dashboard/subscription', [DashboardController::class, 'subscription'])->name('dashboard.subscription');
    Route::post('/dashboard/subscription/sync', [DashboardController::class, 'syncSubscription'])->name('dashboard.subscription.sync');
    Route::get('/dashboard/purchases', [DashboardController::class, 'purchases'])->name('dashboard.purchases');
    Route::get('/dashboard/downloads', [DashboardController::class, 'downloads'])->name('dashboard.downloads');
    Route::get('/dashboard/documents', [DashboardController::class, 'documents'])->name('dashboard.documents');

    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
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
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::get('/contacts', [AdminController::class, 'contacts'])->name('contacts');
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
    Route::post('/purchases/{purchase}/refund', [AdminController::class, 'refundPurchase'])->name('purchases.refund');
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/usage', [AdminController::class, 'usage'])->name('usage');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
