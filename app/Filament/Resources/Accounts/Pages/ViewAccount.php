<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Accounts\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Filament\Resources\Pages\ViewRecord;

final class ViewAccount extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::editAction()
                /** @phpstan-ignore-next-line */
                ->action(fn (AccountService $service, Account $record, array $data): Account => $service->update($record, $data)),

            self::deleteAction(),
        ];
    }
}
