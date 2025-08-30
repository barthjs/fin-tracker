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
use App\Filament\Resources\Users\RelationManagers\CategoryRelationManager;
use App\Filament\Resources\Users\RelationManagers\PortfoliosRelationManager;
use App\Filament\Resources\Users\RelationManagers\SecuritiesRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Table;
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

    public static function getSlug(?Panel $panel = null): string
    {
        return __('user.slug');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        FileUpload::make('avatar')
                            ->label(__('user.fields.avatar'))
                            ->columnSpanFull()
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->moveFiles()
                            ->directory('users/avatars')
                            ->maxSize(1024),

                        TextInput::make('first_name')
                            ->label(__('user.fields.first_name'))
                            ->autofocus()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label(__('user.fields.last_name'))
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label(__('user.fields.username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
                            ->required()
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
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

                        Toggle::make('is_admin')
                            ->label(__('user.fields.is_admin'))
                            ->hidden(function (?User $record = null): bool {
                                // Prevent the current user from removing their admin status
                                if (! $record) {
                                    return false;
                                }

                                return $record->id === auth()->user()->id;
                            })
                            ->default(false)
                            ->inline(false),

                        self::statusToggleField()
                            ->hidden(function (?User $record = null): bool {
                                // Prevent the current user from making their account inactive
                                if (! $record) {
                                    return false;
                                }

                                return $record->id === auth()->user()->id;
                            }),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
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
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 3,
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

                self::createdAtColumn(),
                self::updatedAtColumn(),
            ])
            ->paginated(fn (): bool => User::count() > 20)
            ->defaultSort('username')
            ->recordUrl(fn (User $record): string => ViewUser::getUrl(['record' => $record->id]))
            ->emptyStateHeading(__('No :model found', ['model' => self::getPluralModelLabel()]));
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
