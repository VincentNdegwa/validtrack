<?php

use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

// Billing routes (accessible only to authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('super-admin')->prefix('billing')->name('billing.')->group(function () {
        // Plan management
        Route::get('/plans', [BillingController::class, 'indexPlans'])->name('plans.index');
        Route::get('/plans/create', [BillingController::class, 'createPlan'])->name('plans.create');
        Route::post('/plans', [BillingController::class, 'storePlan'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [BillingController::class, 'editPlan'])->name('plans.edit');
        Route::put('/plans/{plan}', [BillingController::class, 'updatePlan'])->name('plans.update');
        Route::delete('/plans/{plan}', [BillingController::class, 'destroyPlan'])->name('plans.destroy');

        // Feature management
        Route::get('/features', [BillingController::class, 'indexFeatures'])->name('features.index');
        Route::get('/features/create', [BillingController::class, 'createFeature'])->name('features.create');
        Route::post('/features', [BillingController::class, 'storeFeature'])->name('features.store');
        Route::get('/features/{feature}/edit', [BillingController::class, 'editFeature'])->name('features.edit');
        Route::put('/features/{feature}', [BillingController::class, 'updateFeature'])->name('features.update');
        Route::delete('/features/{feature}', [BillingController::class, 'destroyFeature'])->name('features.destroy');
    });
});
