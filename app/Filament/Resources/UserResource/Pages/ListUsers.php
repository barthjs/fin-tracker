<?php declare(strict_types=1);

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
            'inactive' => Tab::make()
                ->label(__('table.status_inactive'))
                ->badge(User::whereActive(false)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('active', false)),
            'unverified' => Tab::make()
                ->label(__('user.filter.unverified'))
                ->badge(User::whereVerified(false)->count())
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('verified', false)),
        ];
    }
}
