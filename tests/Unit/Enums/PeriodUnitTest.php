<?php

declare(strict_types=1);

use App\Enums\PeriodUnit;

it('has a color for every case', function (PeriodUnit $unit): void {
    expect($unit->getColor())->toBeString()->not->toBeEmpty();
})->with(PeriodUnit::cases());

it('has a label for every case', function (PeriodUnit $unit): void {
    expect($unit->getLabel())->toBeString()->not->toBeEmpty();
})->with(PeriodUnit::cases());

it('builds a singular interval label', function (): void {
    expect(PeriodUnit::Month->getLabelByFrequency(1))->toBeString()->not->toBeEmpty();
});

it('builds a plural interval label', function (): void {
    expect(PeriodUnit::Month->getLabelByFrequency(3))->toBeString()->not->toBeEmpty();
});
