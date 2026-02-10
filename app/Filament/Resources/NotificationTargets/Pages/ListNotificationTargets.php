<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTargets\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\NotificationTargets\NotificationTargetResource;
use App\Models\NotificationTarget;
use App\Services\NotificationTargetService;
use Filament\Resources\Pages\ListRecords;

final class ListNotificationTargets extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = NotificationTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::createAction()
                /** @phpstan-ignore-next-line */
                ->action(fn (NotificationTargetService $service, array $data): NotificationTarget => $service->create($data)),
        ];
    }
}
