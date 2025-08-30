<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('table.filter.all'))
                ->badge(User::count()),

            'inactive' => Tab::make()
                ->label(__('fields.status_inactive'))
                ->badge(User::where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', false)),

            'unverified' => Tab::make()
                ->label(__('table.filter.unverified'))
                ->badge(User::where('is_verified', false)->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_verified', false)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus'),
        ];
    }
}
