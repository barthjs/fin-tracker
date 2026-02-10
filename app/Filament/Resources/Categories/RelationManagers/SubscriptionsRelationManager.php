<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property Category $ownerRecord
 */
final class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static string|BackedEnum|null $icon = 'tabler-calendar-repeat';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Str::ucfirst(__('subscription.plural_label'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(SubscriptionResource::getFormFields(category: $this->ownerRecord));
    }

    public function table(Table $table): Table
    {
        return SubscriptionResource::table($table);
    }
}
