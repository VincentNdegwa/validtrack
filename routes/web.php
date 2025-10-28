<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RequiredDocumentTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/acceptable-use', [HomeController::class, 'acceptableUse'])->name('acceptable-use');

Route::get('/legal', [HomeController::class, 'legal'])->name('legal');

Route::get('/security', [HomeController::class, 'security'])->name('security');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Subject routes
    Route::get('/subjects/bulk-import', [SubjectController::class, 'showBulkImport'])
        ->name('subjects.bulk-import');
    Route::post('/subjects/bulk-import', [SubjectController::class, 'bulkImport'])
        ->name('subjects.bulk-import.store');
    Route::resource('subjects', SubjectController::class);

    // Subject Type routes
    Route::get('/subject-types/bulk-import', [SubjectTypeController::class, 'showBulkImport'])
        ->name('subject-types.bulk-import');
    Route::post('/subject-types/bulk-import', [SubjectTypeController::class, 'bulkImport'])
        ->name('subject-types.bulk-import.store');
    Route::resource('subject-types', SubjectTypeController::class);

    Route::resource('documents', DocumentController::class);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');


    Route::resource('document-types', DocumentTypeController::class);
    Route::resource('required-documents', RequiredDocumentTypeController::class)->only(['store', 'destroy']);
    Route::resource('activity-logs', ActivityLogController::class)->only(['index', 'show']);

    // User management routes
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Company management routes for super admins
    Route::middleware('super-admin')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::post('companies/switch', [CompanyController::class, 'switchCompany'])->name('companies.switch');
        Route::post('impersonate', [CompanyController::class, 'impersonateUser'])->name('impersonate.start');
    });

    Route::post('impersonate/stop', [CompanyController::class, 'stopImpersonation'])->name('impersonate.stop');
    Route::get('test-slack', function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        $user->notify(new \App\Notifications\TestSlackNotification);

        return response()->json(['message' => 'Slack notification sent successfully.']);
    });
});

// Include additional route files
require __DIR__.'/document-uploads.php';
require __DIR__.'/settings.php';
require __DIR__.'/paddle-billing.php';
require __DIR__.'/billing.php';
require __DIR__.'/auth.php';
require __DIR__.'/slack.php';
