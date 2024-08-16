<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Filament\Resources\BankAccountResource\Pages;
use App\Filament\Resources\BankAccountResource\RelationManagers\TransactionRelationManager;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'tabler-bank-building';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Select::make('currency')
                    ->options(Currency::class)
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->autosize()
                    ->columnSpanFull()
                    ->maxLength(1000)
                    ->rows(1)
                    ->string()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric(2)
                    ->badge()
                    ->color(function ($record) {
                        $balance = $record->balance;
                        return match (true) {
                            floatval($balance) == 0 => 'gray',
                            floatval($balance) < 0 => 'danger',
                            default => 'success',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y.m.d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('Y.m.d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn(Model $record): string => Pages\ViewBankAccount::getUrl([$record->id]))
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransactionRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'view' => Pages\ViewBankAccount::route('/{record}'),
        ];
    }
}
