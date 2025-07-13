<?php

use App\Http\Controllers\PaddleBillingController;
use Illuminate\Support\Facades\Route;
use Laravel\Paddle\Http\Controllers\WebhookController;

// Paddle Billing routes (accessible only to authenticated users)
Route::middleware(['auth', 'verified'])->name('paddle.')->group(function () {
    // User billing routes
    Route::get('billing', [PaddleBillingController::class, 'index'])->name('billing.index');
    Route::get('billing/{plan}/subscribe/{billing_cycle}', [PaddleBillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('billing/cancel', [PaddleBillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('billing/resume', [PaddleBillingController::class, 'resume'])->name('billing.resume');
});

// Paddle webhook route - does not require auth
Route::post('paddle/webhook', WebhookController::class)->name('paddle.webhook');
