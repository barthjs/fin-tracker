<?php

declare(strict_types=1);

use App\Http\Controllers\Pwa\ManifestController;
use App\Http\Controllers\Pwa\ServiceWorkerController;
use Illuminate\Support\Facades\Route;

// Browsers fetch the manifest without cookies, so the session middleware would start a session for every request
Route::withoutMiddleware('web')->group(function (): void {
    Route::get('/site.webmanifest', ManifestController::class)->name('pwa.manifest');
    Route::get('/sw.js', ServiceWorkerController::class)->name('pwa.service-worker');
});
