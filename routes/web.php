<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('CompliTrack');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Core application routes
    Route::resource('subjects', SubjectController::class);
    Route::resource('subject-types', SubjectTypeController::class);
    Route::resource('documents', DocumentController::class);
    Route::resource('document-types', DocumentTypeController::class);
    
    // User management routes
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    
    // Company management routes for super admins
    Route::middleware('super-admin')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('companies/switch', [CompanyController::class, 'switchCompany'])->name('companies.switch');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
