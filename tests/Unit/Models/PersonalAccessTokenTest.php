<?php

declare(strict_types=1);

use App\Models\PersonalAccessToken;
use App\Models\User;

it('ignores writes to the last_used_at attribute', function (): void {
    $user = User::factory()->create();

    $token = PersonalAccessToken::query()->create([
        'tokenable_type' => $user->getMorphClass(),
        'tokenable_id' => $user->id,
        'name' => 'test-token',
        'token' => hash('sha256', 'plain-text'),
        'abilities' => ['*'],
        'last_used_at' => now(),
    ]);

    expect($token->last_used_at)->toBeNull()
        ->and($token->fresh()?->last_used_at)->toBeNull();
});
