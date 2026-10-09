<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ProductDescriptionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/tools', [CatalogController::class, 'tools']);
    Route::get('/tools/{slug}', [CatalogController::class, 'tool']);
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/templates', [CatalogController::class, 'templates']);
    Route::get('/templates/{slug}', [CatalogController::class, 'template']);

    Route::post('/tools/product-description-generator/generate', [ProductDescriptionController::class, 'store'])
        ->middleware('throttle:generator');

    Route::post('/analytics/events', [AnalyticsController::class, 'store'])
        ->middleware('throttle:analytics');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AccountController::class, 'user']);
        Route::get('/dashboard', [AccountController::class, 'dashboard']);
        Route::get('/purchases', [AccountController::class, 'purchases']);
        Route::get('/subscription', [AccountController::class, 'subscription']);

        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);

        Route::post('/billing/checkout', [BillingController::class, 'checkout']);
        Route::post('/billing/portal', [BillingController::class, 'portal']);
        Route::post('/billing/cancel', [BillingController::class, 'cancel']);
        Route::post('/templates/{template}/checkout', [BillingController::class, 'template']);
        Route::get('/downloads/{purchase}', DownloadController::class);
    });
});
