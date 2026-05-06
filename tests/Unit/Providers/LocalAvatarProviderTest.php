<?php

declare(strict_types=1);

use App\Models\User;
use App\Providers\Filament\LocalAvatarProvider;

it('renders initials from the first and last name', function (): void {
    $user = User::factory()->make(['first_name' => 'Jane', 'last_name' => 'Doe']);

    $avatar = (new LocalAvatarProvider)->get($user);

    expect($avatar)->toStartWith('data:image/svg+xml;base64,')
        ->and(base64_decode(mb_substr($avatar, mb_strlen('data:image/svg+xml;base64,'))))->toContain('JD');
});

it('falls back to the username initial when no name is set', function (): void {
    $user = User::factory()->make(['first_name' => null, 'last_name' => null, 'username' => 'zoe']);

    $avatar = (new LocalAvatarProvider)->get($user);

    expect($avatar)->toStartWith('data:image/svg+xml;base64,')
        ->and(base64_decode(mb_substr($avatar, mb_strlen('data:image/svg+xml;base64,'))))->toContain('Z');
});
