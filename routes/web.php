<?php

use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectTypeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('CompliTrack');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('subjects', SubjectController::class);
    Route::resource('subject-types', SubjectTypeController::class);
    Route::resource('documents', DocumentController::class);
    Route::resource('document-types', DocumentTypeController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
