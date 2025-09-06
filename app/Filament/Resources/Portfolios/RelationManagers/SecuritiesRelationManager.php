<?php

declare(strict_types=1);

namespace App\Filament\Resources\Portfolios\RelationManagers;

use App\Enums\TradeType;
use App\Filament\Resources\Securities\Pages\ViewSecurity;
use App\Filament\Resources\Securities\SecurityResource;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class SecuritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'securities';

    protected static string|BackedEnum|null $icon = 'tabler-file-percent';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Str::ucfirst(__('security.plural_label'));
    }

    public function form(Schema $schema): Schema
    {
        return SecurityResource::form($schema);
    }

    public function table(Table $table): Table
    {
        /** @var Portfolio $portfolio */
        $portfolio = $this->ownerRecord;

        $columns = SecurityResource::getTableColumns($portfolio);

        return SecurityResource::table($table)
            ->query(function () use ($portfolio) {
                $securityIds = Trade::wherePortfolioId($portfolio->id)
                    ->groupBy(['security_id'])
                    ->havingRaw('SUM(CASE WHEN type = ? THEN quantity ELSE -quantity END) > 0', [
                        TradeType::Buy->value,
                    ])
                    ->pluck('security_id')
                    ->toArray();

                return Security::whereIn('id', $securityIds);
            })
            ->columns($columns)
            ->recordUrl(fn (Security $record): string => ViewSecurity::getUrl(['record' => $record->id]));
    }
}
