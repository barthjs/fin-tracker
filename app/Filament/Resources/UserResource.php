<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\ViewUser;
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
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'tabler-users';
    protected static ?string $navigationGroup = 'System';

    public static function getSlug(): string
    {
        return __('user.url');
    }

    public static function getNavigationLabel(): string
    {
        return __('user.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('user.columns.first_name'))
                            ->autofocus()
                            ->maxLength(255)
                            ->required()
                            ->string(),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('user.columns.last_name'))
                            ->maxLength(255)
                            ->required()
                            ->string(),
                        Forms\Components\TextInput::make('name')
                            ->label(__('user.columns.name'))
                            ->maxLength(255)
                            ->required()
                            ->string()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->label(__('user.columns.email'))
                            ->maxLength(255)
                            ->email()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(__('user.buttons.password'))
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn($state): bool => filled($state))
                            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                            ->live(debounce: 200)
                            ->same('passwordConfirmation')
                            ->validationMessages(['same' => __('user.buttons.password_confirmation_warning')]),
                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label(__('user.buttons.password_confirmation'))
                            ->password()
                            ->revealable()
                            ->required(function (callable $get) {
                                if (!$get('password')) {
                                    return false;
                                }
                                return true;
                            })
                            ->disabled(function (callable $get) {
                                if (!$get('password')) {
                                    return true;
                                }
                                return false;
                            })
                            ->dehydrated(false),
                        Forms\Components\Toggle::make('is_admin')
                            ->label(__('user.columns.is_admin'))
                            ->disabled(function ($record) {
                                // Prevent current user from removing his admin status
                                if (!$record) {
                                    return false;
                                }
                                return $record->id == auth()->user()->id;
                            })
                            ->default(false)
                            ->inline(false),
                        Forms\Components\Toggle::make('active')
                            ->label(__('table.active'))
                            ->disabled(function ($record) {
                                if (!$record) {
                                    // Prevent current user from making his account inactive
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
                Tables\Columns\TextColumn::make('name')
                    ->label(__('user.columns.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('user.columns.email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('user.columns.first_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('user.columns.last_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('table.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('table.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(fn() => User::count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
            ])
            ->recordUrl(fn(User $record): string => ViewUser::getUrl([$record->id]))
            ->emptyStateHeading(__('user.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('user.buttons.create_button_label'))
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UserResource\RelationManagers\AccountsRelationManager::class,
            UserResource\RelationManagers\CategoryRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'view' => UserResource\Pages\ViewUser::route('/{record}'),
            'edit' => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
