<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->laravel()->ignoring([
    'App\Providers\Filament',
    'App\Http\Controllers',
]);
arch()->preset()->security();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->not->toBeUsed();

arch('strict mode')
    ->expect('App')
    ->toUseStrictEquality()
    ->toUseStrictTypes()
    ->classes()->toBeFinal();
