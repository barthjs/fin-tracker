<?php

namespace App\Filament\Clusters\UserResource\Pages;

use App\Filament\Clusters\UserResource;
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
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'admins' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('role', 'admin')),
            'unverified' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('email_verified_at')),
            'verified' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('email_verified_at')),
        ];
    }
}
