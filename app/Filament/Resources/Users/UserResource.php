<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Filament\Resources\Users\RelationManagers\CategoryRelationManager;
use App\Filament\Resources\Users\RelationManagers\PortfoliosRelationManager;
use App\Filament\Resources\Users\RelationManagers\SecuritiesRelationManager;
use App\Models\User;
use App\Tables\Columns\LogoColumn;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-users';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getSlug(?Panel $panel = null): string
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('user.columns.first_name'))
                            ->autofocus()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label(__('user.columns.last_name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label(__('user.columns.name'))
                            ->validationMessages(['unique' => __('user.columns.name_unique_warning')])
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('user.columns.email'))
                            ->validationMessages(['unique' => __('user.columns.email_unique_warning')])
                            ->required()
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make()
                    ->schema([
                        TextInput::make('password')
                            ->label(__('user.buttons.password'))
                            ->validationMessages(['min' => __('user.buttons.password_length_warning')])
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->live(debounce: 500),

                        TextInput::make('passwordConfirmation')
                            ->label(__('user.buttons.password_confirmation'))
                            ->validationMessages(['same' => __('user.buttons.password_confirmation_warning')])
                            ->password()
                            ->revealable()
                            ->required(function (Get $get) {
                                if (! $get('password')) {
                                    return false;
                                }

                                return true;
                            })
                            ->dehydrated(false)
                            ->same('password'),

                        Toggle::make('is_admin')
                            ->label(__('user.columns.is_admin'))
                            ->disabled(function (?User $record = null) {
                                // Prevent the current user from removing his admin status
                                if (! $record) {
                                    return false;
                                }

                                return $record->id === auth()->user()->id;
                            })
                            ->default(false)
                            ->inline(false),

                        Toggle::make('is_active')
                            ->label(__('table.active'))
                            ->disabled(function (?User $record = null) {
                                if (! $record) {
                                    // Prevent the current user from making his account inactive
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('full_name')
                            ->label(__('user.columns.full_name'))
                            ->state(fn (User $record): string => $record->getFilamentName())
                            ->tooltip(fn (User $record): string => ! $record->is_active ? __('table.status_inactive') : __('table.status_active'))
                            ->color(fn (User $record): string => ! $record->is_active ? 'danger' : 'success')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('username')
                            ->label(__('user.columns.name'))
                            ->tooltip(fn (User $record): string => ! $record->is_active ? __('table.status_inactive') : __('table.status_active'))
                            ->color(fn (User $record): string => ! $record->is_active ? 'danger' : 'success')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('email')
                            ->label(__('user.columns.email'))
                            ->tooltip(fn (User $record): string => ! $record->is_active ? __('table.status_inactive') : __('table.status_active'))
                            ->color(fn (User $record): string => ! $record->is_active ? 'danger' : 'success')
                            ->size(TextSize::Medium)
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                LogoColumn::make('username')
                    ->label(__('user.columns.name'))
                    ->state(fn (User $record): array => [
                        'logo' => $record->avatar,
                        'name' => $record->username,
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
            ->defaultSort('username')
            ->extremePaginationLinks()
            ->persistSortInSession()
            ->striped()
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit'),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('user.buttons.delete_heading')),
            ])
            ->recordUrl(fn (User $record): string => ViewUser::getUrl(['record' => $record->id]))
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
