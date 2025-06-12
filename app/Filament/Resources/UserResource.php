<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\RelationManagers\AccountsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\CategoryRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PortfoliosRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\SecuritiesRelationManager;
use App\Models\User;
use App\Tables\Columns\LogoColumn;
use Exception;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'tabler-users';

    public static function getSlug(): string
    {
        return __('user.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('user.navigation_label');
    }

    public static function getBreadcrumb(): string
    {
        return __('user.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('user.columns.first_name'))
                            ->maxLength(255)
                            ->required()
                            ->string(),
                        TextInput::make('last_name')
                            ->label(__('user.columns.last_name'))
                            ->maxLength(255)
                            ->required()
                            ->string(),
                        TextInput::make('name')
                            ->label(__('user.columns.name'))
                            ->maxLength(255)
                            ->required()
                            ->string()
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => __('user.columns.name_unique_warning')]),
                        TextInput::make('email')
                            ->label(__('user.columns.email'))
                            ->maxLength(255)
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['unique' => __('user.columns.email_unique_warning')]),
                    ])
                    ->columns(2),
                Section::make()
                    ->schema([
                        TextInput::make('password')
                            ->label(__('filament-breezy::default.fields.new_password'))
                            ->validationMessages(['min' => __('user.buttons.password_length_warning')])
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->rule(Password::default())
                            ->dehydrated(fn (string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->live(debounce: 200),
                        TextInput::make('passwordConfirmation')
                            ->label(__('user.buttons.password_confirmation'))
                            ->validationMessages(['same' => __('user.buttons.password_confirmation_warning')])
                            ->password()
                            ->revealable()
                            ->required(function (callable $get) {
                                if (! $get('password')) {
                                    return false;
                                }

                                return true;
                            })
                            ->dehydrated(false)
                            ->disabled(function (callable $get) {
                                if (! $get('password')) {
                                    return true;
                                }

                                return false;
                            })
                            ->same('password'),
                        Toggle::make('is_admin')
                            ->label(__('user.columns.is_admin'))
                            ->disabled(function (?User $record = null) {
                                // Prevent current user from removing his admin status
                                if (! $record) {
                                    return false;
                                }

                                return $record->id === auth()->user()->id;
                            })
                            ->default(false)
                            ->inline(false),
                        Toggle::make('active')
                            ->label(__('table.active'))
                            ->disabled(function (?User $record = null) {
                                if (! $record) {
                                    // Prevent current user from making his account inactive
                                    return false;
                                }

                                return $record->id === auth()->user()->id;
                            })
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        TextEntry::make('full_name')
                            ->label(__('user.columns.full_name'))
                            ->state(fn (User $record): string => $record->getFilamentName())
                            ->tooltip(fn (User $record): string => ! $record->active ? __('table.status_inactive') : __('table.status_active'))
                            ->color(fn (User $record): string => ! $record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('name')
                            ->label(__('user.columns.name'))
                            ->tooltip(fn (User $record): string => ! $record->active ? __('table.status_inactive') : __('table.status_active'))
                            ->color(fn (User $record): string => ! $record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        TextEntry::make('email')
                            ->label(__('user.columns.email'))
                            ->tooltip(fn (User $record): string => ! $record->active ? __('table.status_inactive') : __('table.status_active'))
                            ->color(fn (User $record): string => ! $record->active ? 'danger' : 'success')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->weight(FontWeight::SemiBold),
                        IconEntry::make('is_admin')
                            ->label(__('user.columns.is_admin'))
                            ->boolean(),
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ]),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                LogoColumn::make('name')
                    ->label(__('user.columns.name'))
                    ->state(fn (User $record): array => [
                        'logo' => $record->avatar,
                        'name' => $record->name,
                    ])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('user.columns.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label(__('user.columns.first_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('user.columns.last_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('table.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('table.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(fn (): bool => User::count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit'),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('user.buttons.delete_heading')),
            ])
            ->recordUrl(fn (User $record): string => ViewUser::getUrl([$record->id]))
            ->emptyStateHeading(__('user.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('user.buttons.create_button_label')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AccountsRelationManager::class,
            CategoryRelationManager::class,
            PortfoliosRelationManager::class,
            SecuritiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
