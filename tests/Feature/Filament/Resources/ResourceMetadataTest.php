<?php

declare(strict_types=1);

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\CategoryStatistics\CategoryStatisticResource;
use App\Filament\Resources\NotificationTargets\NotificationTargetResource;
use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Filament\Resources\Securities\SecurityResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Trades\TradeResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Users\UserResource;

beforeEach(fn () => asUser());

it('exposes a model label and plural label', function (string $resource): void {
    expect($resource::getModelLabel())->toBeString()->not->toBeEmpty()
        ->and($resource::getPluralModelLabel())->toBeString()->not->toBeEmpty();
})->with([
    AccountResource::class,
    CategoryResource::class,
    CategoryStatisticResource::class,
    NotificationTargetResource::class,
    PortfolioResource::class,
    SecurityResource::class,
    SubscriptionResource::class,
    TradeResource::class,
    TransactionResource::class,
    UserResource::class,
]);
