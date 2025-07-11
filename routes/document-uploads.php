<?php

use App\Http\Controllers\DocumentUploadRequestController;
use Illuminate\Support\Facades\Route;

// Public document upload routes (no auth required)
Route::get('/document-upload/{token}', [DocumentUploadRequestController::class, 'showUploadForm'])
    ->name('public.document-upload');

Route::post('/document-upload/{token}', [DocumentUploadRequestController::class, 'processUpload'])
    ->name('public.document-upload.store');

// Authenticated routes for managing upload requests
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/document-upload-requests', [DocumentUploadRequestController::class, 'store'])
        ->name('document-upload-requests.store')
        ->middleware('permission:documents-create');
        
    Route::post('/document-upload-requests/{uploadRequest}/cancel', [DocumentUploadRequestController::class, 'cancel'])
        ->name('document-upload-requests.cancel')
        ->middleware('permission:documents-delete');
});
