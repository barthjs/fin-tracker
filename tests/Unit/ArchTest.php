<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->laravel()->ignoring('App\Providers\Filament');
arch()->preset()->security()->ignoring(['md5', 'mt_rand', 'sha1']);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('strict mode')
    ->expect('App')
    ->toUseStrictEquality()
    ->toUseStrictTypes();
