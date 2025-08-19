<?php

declare(strict_types=1);

use Illuminate\Support\Sleep;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->beforeEach(function () {
        Http::preventStrayRequests();
        Sleep::fake();
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Str::createUlidsNormally();

        $this->freezeTime();
    })
    ->in('Feature', 'Unit');
