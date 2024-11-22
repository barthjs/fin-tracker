<?php declare(strict_types=1);

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $icon = 'tabler-credit-card';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('transaction.navigation_label');
    }

    public function form(Form $form): Form
    {
        return $form->schema(TransactionResource::formParts(account: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->heading('');
    }
}
