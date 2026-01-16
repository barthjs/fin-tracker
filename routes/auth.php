<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\OidcController;

Route::prefix('auth')->group(function (): void {
    Route::get('/{provider}/redirect', [OidcController::class, 'redirect'])->name('auth.oidc.redirect');
    Route::get('/{provider}/callback', [OidcController::class, 'callback'])->name('auth.oidc.callback');
});
