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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(User::all()->count()),
            'admins' => Tab::make()
                ->badge(User::whereIsAdmin(true)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_admin', true)),
            'users' => Tab::make()
                ->badge(User::whereIsAdmin(false)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_admin', false)),
            'active' => Tab::make()
                ->badge(User::whereActive(true)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make()
                ->badge(User::whereActive(false)->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', false))
        ];
    }
}
