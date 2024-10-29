<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('user.navigation_label');
    }

    public function getHeading(): string
    {
        return __('user.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('user.buttons.create_button_label'))
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('table.filter.all'))
                ->badge(User::all()->count()),
            'verified' => Tab::make()
                ->label(__('user.filter.verified'))
                ->badge(User::whereVerified(true)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('verified', true)),
            'admins' => Tab::make()
                ->label(__('user.filter.admins'))
                ->badge(User::whereIsAdmin(true)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('is_admin', true)),
            'users' => Tab::make()
                ->label(__('user.filter.users'))
                ->badge(User::whereIsAdmin(false)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('is_admin', false)),
            'active' => Tab::make()
                ->label(__('table.status_active'))
                ->badge(User::whereActive(true)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('active', true)),
            'inactive' => Tab::make()
                ->label(__('table.status_inactive'))
                ->badge(User::whereActive(false)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('active', false))
        ];
    }
}
