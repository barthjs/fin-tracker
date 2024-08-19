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

    public static function getNavigationLabel(): string
    {
        return __('resources.users.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label(__('resources.users.table.first_name'))
                        ->autofocus()
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('last_name')
                        ->label(__('resources.users.table.last_name'))
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('name')
                        ->label(__('resources.users.table.name'))
                        ->maxLength(255)
                        ->required()
                        ->string()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('email')
                        ->label(__('resources.users.table.email'))
                        ->maxLength(255)
                        ->email()
                        ->unique(ignoreRecord: true),
                ])->columns(2),
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('password')
                        ->label(__('resources.users.password'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn($state): bool => filled($state))
                        ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same('passwordConfirmation'),
                    Forms\Components\TextInput::make('passwordConfirmation')
                        ->label(__('resources.users.password_confirmation'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->dehydrated(false),
                    Forms\Components\Toggle::make('is_admin')
                        ->label(__('resources.users.table.is_admin'))
                        ->disabled(function ($record) {
                            if (!$record) {
                                return false;
                            }
                            return $record->id == auth()->user()->id;
                        })
                        ->default(false)
                        ->inline(false),
                    Forms\Components\Toggle::make('active')
                        ->label(__('tables.active'))
                        ->disabled(function ($record) {
                            if (!$record) {
                                return false;
                            }
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
                    ->label(__('resources.users.table.email'))
                    ->url(fn($record) => "mailto:" . $record->email)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.users.table.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('resources.users.table.first_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('resources.users.table.last_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('tables.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('tables.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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
