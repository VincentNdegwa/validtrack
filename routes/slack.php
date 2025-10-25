<?php

use App\Http\Controllers\SlackController;
use Illuminate\Support\Facades\Route;

Route::prefix('slack')->group(function () {
    Route::get('/redirect', [SlackController::class, 'redirect'])->name('slack.redirect');
    Route::get('/callback', [SlackController::class, 'callback'])->name('slack.callback');
    Route::delete('/disconnect', [SlackController::class, 'disconnect'])->name('slack.disconnect');
});