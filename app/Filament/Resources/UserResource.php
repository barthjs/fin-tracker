<?php

namespace App\Filament\Resources;

use App\Models\User;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'tabler-users';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('last_name')
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('email')
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('name')
                        ->maxLength(255)
                        ->string(),
                ])->columns(2),
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn($state): bool => filled($state))
                        ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same('passwordConfirmation'),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn($state): bool => filled($state))
                        ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same('passwordConfirmation'),
                    Forms\Components\Toggle::make('is_admin')
                        ->disabled(function ($record) {
                            return $record->id == auth()->user()->id;
                        })
                        ->default(false)
                        ->inline(false),
                    Forms\Components\Toggle::make('active')
                        ->disabled(function ($record) {
                            return $record->id == auth()->user()->id;
                        })
                        ->default(true)
                        ->inline(false)
                ])->columns(2),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->url(fn($record) => "mailto:" . $record->email)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y.m.d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('Y.m.d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('email')
            ->persistSortInSession()
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UserResource\RelationManagers\BankAccountsRelationManager::class,
            UserResource\RelationManagers\TransactionCategoryRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'edit' => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
