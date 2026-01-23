<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Middleware\CheckAbilities;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/up', [SystemController::class, 'up'])->name('api.up');

Route::middleware(['auth:sanctum', EnsureUserIsActive::class])->name('api.')->group(function (): void {
    Route::get('/health', [SystemController::class, 'health'])->name('health');
    Route::get('/version', [SystemController::class, 'version'])->name('version');

    Route::apiResource('accounts', AccountController::class)
        ->middleware(CheckAbilities::class.':'.ApiAbility::ACCOUNT->value);
});
