<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\AccountsRelationManager;
use App\Filament\Resources\Users\RelationManagers\CategoriesRelationManager;
use App\Filament\Resources\Users\RelationManagers\PortfoliosRelationManager;
use App\Filament\Resources\Users\RelationManagers\SecuritiesRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

final class UserResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceInfolistEntries, HasResourceTableColumns;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-users';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getModelLabel(): string
    {
        return __('user.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        FileUpload::make('avatar')
                            ->label(__('user.fields.avatar'))
                            ->columnSpan(1)
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->moveFiles()
                            ->directory('users/avatars')
                            ->maxSize(1024),

                        Group::make([
                            TextInput::make('first_name')
                                ->label(__('user.fields.first_name'))
                                ->maxLength(255),

                            TextInput::make('last_name')
                                ->label(__('user.fields.last_name'))
                                ->maxLength(255),
                        ])->columnSpan(1),

                        Group::make([
                            TextInput::make('username')
                                ->label(__('user.fields.username'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ])->columnSpan(1),
                    ]),

                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->hidden(fn (?User $record): bool => $record?->id === auth()->user()->id)
                    ->schema([
                        TextInput::make('password')
                            ->label(__('filament-panels::auth/pages/edit-profile.form.password.label'))
                            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password.validation_attribute'))
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn (string $context): bool => $context === 'create')
                            ->showAllValidationMessages()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation'),

                        TextInput::make('passwordConfirmation')
                            ->label(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.label'))
                            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.validation_attribute'))
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                    ]),

                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->hidden(fn (?User $record): bool => $record?->id === auth()->user()->id)
                    ->schema([
                        Toggle::make('is_admin')
                            ->label(__('user.fields.is_admin'))
                            ->default(false)
                            ->inline(false),

                        self::statusToggleField(),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns([
                        'default' => 2,
                        'md' => 3,
                    ])
                    ->schema([
                        TextEntry::make('username')
                            ->label(__('user.fields.username'))
                            ->tooltip(fn (User $record): string => ! $record->is_active ? (string) __('fields.status_inactive') : (string) __('fields.status_active'))
                            ->color(fn (User $record): string => ! $record->is_active ? 'danger' : 'success')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('email')
                            ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
                            ->tooltip(fn (User $record): string => ! $record->is_active ? (string) __('fields.status_inactive') : (string) __('fields.status_active'))
                            ->color(fn (User $record): string => ! $record->is_active ? 'danger' : 'success')
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),

                        IconEntry::make('is_admin')
                            ->label(__('user.fields.is_admin'))
                            ->boolean(),
                    ]),

                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        IconEntry::make('password')
                            ->label(__('user.fields.status_local_password'))
                            ->state(fn (User $record): bool => $record->password !== null)
                            ->boolean()
                            ->trueIcon('tabler-lock-check')
                            ->falseIcon('tabler-lock-off')
                            ->trueColor('success')
                            ->falseColor('warning'),

                        TextEntry::make('providers.provider_name')
                            ->label(__('profile.oidc.heading'))
                            ->badge()
                            ->color('gray')
                            ->placeholder(__('profile.oidc.no_providers_connected')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::logoAndNameColumn('username')
                    ->label(__('user.fields.username'))
                    ->state(fn (User $record): array => [
                        'logo' => $record->avatar,
                        'name' => $record->username,
                    ]),

                self::nameColumn('first_name')
                    ->label(__('user.fields.first_name')),

                self::nameColumn('last_name')
                    ->label(__('user.fields.last_name')),

                self::nameColumn('email')
                    ->label(__('filament-panels::auth/pages/edit-profile.form.email.label')),

                ImageColumn::make('providers.provider_name')
                    ->view('filament.tables.columns.users.auth-methods')
                    ->label(__('user.fields.auth_methods')),

                self::createdAtColumn(),
                self::updatedAtColumn(),
            ])
            ->defaultSort('username')
            ->recordUrl(fn (User $record): string => ViewUser::getUrl(['record' => $record->id]));
    }

    /**
     * @return Builder<User>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<User> $query */
        $query = parent::getEloquentQuery();

        return $query->with(['providers']);
    }

    public static function getRelations(): array
    {
        return [
            AccountsRelationManager::class,
            CategoriesRelationManager::class,
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
