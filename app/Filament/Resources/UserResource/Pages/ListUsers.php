<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('resources.users.navigation_label');
    }

    public function getHeading(): string
    {
        return __('resources.users.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('resources.users.create_label'))
                ->modalHeading(__('resources.users.create_heading')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('resources.users.filter.all'))
                ->badge(User::all()->count()),
            'admins' => Tab::make()
                ->label(__('resources.users.filter.admins'))
                ->badge(User::whereIsAdmin(true)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_admin', true)),
            'users' => Tab::make()
                ->label(__('resources.users.filter.users'))
                ->badge(User::whereIsAdmin(false)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_admin', false)),
            'active' => Tab::make()
                ->label(__('tables.status_active'))
                ->badge(User::whereActive(true)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make()
                ->label(__('tables.status_inactive'))
                ->badge(User::whereActive(false)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', false))
        ];
    }
}
