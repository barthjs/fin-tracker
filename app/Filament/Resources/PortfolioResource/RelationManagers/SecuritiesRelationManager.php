<?php declare(strict_types=1);

namespace App\Filament\Resources\PortfolioResource\RelationManagers;

use App\Filament\Resources\SecurityResource;
use App\Models\Security;
use App\Models\Trade;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SecuritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'securities';
    protected static ?string $icon = 'tabler-file-percent';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('security.navigation_label');
    }

    public function form(Form $form): Form
    {
        return SecurityResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return SecurityResource::table($table)
            ->query(function () {
                $securities = Trade::distinct()
                    ->wherePortfolioId($this->ownerRecord->id)
                    ->pluck('id')
                    ->toArray();
                return Security::where('total_quantity', '>', 0)
                    ->whereIn('id', $securities);
            })
            ->heading('')
            ->recordUrl(fn(Security $record): string => SecurityResource\Pages\ViewSecurity::getUrl([$record->id]), true);
    }
}
