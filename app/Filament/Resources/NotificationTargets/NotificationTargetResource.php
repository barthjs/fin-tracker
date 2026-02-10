<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTargets;

use App\Data\NotificationPayload;
use App\Enums\NotificationProviderType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\NotificationTargets\Pages\ListNotificationTargets;
use App\Models\NotificationTarget;
use App\Services\Notifications\NotificationStrategyFactory;
use App\Services\NotificationTargetService;
use BackedEnum;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

final class NotificationTargetResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceTableColumns;

    protected static ?string $model = NotificationTarget::class;

    protected static ?int $navigationSort = 1;

    protected static string|null|BackedEnum $navigationIcon = 'tabler-bell-cog';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return __('settings.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('notification_target.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('notification_target.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::getFormFields());
    }

    /**
     * @return array<int, Section>
     */
    public static function getFormFields(): array
    {
        return [
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    self::nameField()
                        ->unique(modifyRuleUsing: function (Unique $rule, ?NotificationTarget $record) {
                            return $rule->where('user_id', auth()->id())->ignore($record?->id);
                        }),

                    ToggleButtons::make('type')
                        ->label(__('fields.type'))
                        ->options(NotificationProviderType::class)
                        ->default(NotificationProviderType::DATABASE)
                        ->live()
                        ->required(),

                    Toggle::make('is_default')
                        ->label(__('notification_target.fields.is_default'))
                        ->default(false)
                        ->inline(false),

                    self::statusToggleField(),
                ]),

            Section::make(__('notification_target.fields.configuration'))
                ->visible(fn (Get $get) => $get('type') !== NotificationProviderType::DATABASE)
                ->columnSpanFull()
                ->schema([
                    Group::make()
                        ->schema([
                            TextInput::make('configuration.url')
                                ->label(__('notification_target.configuration.generic_webhook.url'))
                                ->helperText(__('notification_target.configuration.generic_webhook.hint'))
                                ->placeholder('https://api.example.com/webhook')
                                ->required()
                                ->url()
                                ->maxLength(2048),

                            TextInput::make('configuration.secret')
                                ->label(__('notification_target.configuration.generic_webhook.secret'))
                                ->helperText(__('notification_target.configuration.generic_webhook.secret_hint'))
                                ->password()
                                ->revealable()
                                ->maxLength(255),

                            Select::make('configuration.content_type')
                                ->label(__('notification_target.configuration.generic_webhook.content_type'))
                                ->options([
                                    'json' => __('notification_target.configuration.generic_webhook.content_type_json'),
                                    'form' => __('notification_target.configuration.generic_webhook.content_type_form'),
                                ])
                                ->default('json')
                                ->required(),

                            Toggle::make('configuration.verify_ssl')
                                ->label(__('notification_target.configuration.generic_webhook.verify_ssl'))
                                ->default(true)
                                ->inline(false),
                        ])
                        ->visible(fn (Get $get): bool => $get('type') === NotificationProviderType::GENERIC_WEBHOOK),

                    Group::make()
                        ->visible(fn (Get $get): bool => $get('type') === NotificationProviderType::DATABASE),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::nameColumn('name'),

                self::nameColumn('type')
                    ->badge(),

                self::statusColumn(),

                self::createdAtColumn(),
                self::updatedAtColumn(),
            ])
            ->filters([
                self::inactiveFilter(),
            ])
            ->recordActions([
                self::tableEditAction()
                    /** @phpstan-ignore-next-line */
                    ->action(fn (NotificationTargetService $service, NotificationTarget $record, array $data): NotificationTarget => $service->update($record, $data)),

                Action::make('testNotification')
                    ->label(__('notification_target.actions.test_notification'))
                    ->iconButton()
                    ->icon('tabler-send')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (NotificationTarget $record, NotificationStrategyFactory $factory) {
                        try {
                            $payload = new NotificationPayload(
                                title: __('notification_target.test_payload.title'),
                                body: __('notification_target.test_payload.body'),
                                metadata: [
                                    'type' => 'test_ping',
                                    'sent_at' => now()->toIso8601String(),
                                ]
                            );

                            $strategy = $factory->make($record->type);
                            $strategy->send($record, $payload);

                            Notification::make()
                                ->title(__('notification_target.actions.ping_success'))
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title(__('notification_target.actions.ping_failed', ['error' => $e->getMessage()]))
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                self::tableDeleteAction(),
            ])
            ->toolbarActions([
                self::tableBulkDeleteAction(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationTargets::route('/'),
        ];
    }
}
