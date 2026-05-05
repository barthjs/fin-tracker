<?php

declare(strict_types=1);

use App\Enums\Currency;

it('returns a valid currency', function (): void {
    expect(Currency::getCurrency('EUR'))->toBe('EUR')
        ->and(Currency::getCurrency())->toBe('EUR');

    config()->set('app.currency', 'NON-EXISTING');
    expect(Currency::getCurrency())->toBe('EUR');
});
