<?php

declare(strict_types=1);

use App\Enums\NotificationProviderType;
use App\Filament\Resources\NotificationTargets\Pages\ListNotificationTargets;
use App\Models\NotificationTarget;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('lists targets scoped to the user and filters by inactivity', function (): void {
    $active = NotificationTarget::factory()->create();
    $inactive = NotificationTarget::factory()->create(['is_active' => false]);
    $other = NotificationTarget::factory()->create(['user_id' => User::factory()->create()->id]);

    livewire(ListNotificationTargets::class)
        ->assertCanSeeTableRecords([$active, $inactive])
        ->assertCanNotSeeTableRecords([$other])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactive])
        ->assertCanNotSeeTableRecords([$active]);
});

it('can send a test notification for a database target', function (): void {
    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);

    livewire(ListNotificationTargets::class)
        ->callAction(TestAction::make('testNotification')->table($target))
        ->assertHasNoActionErrors()
        ->assertNotified();
});
it('can create a notification target', function (): void {
    livewire(ListNotificationTargets::class)
        ->callAction('create', [
            'name' => 'Audit Log',
            'type' => NotificationProviderType::DATABASE->value,
            'is_default' => false,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    assertDatabaseHas(NotificationTarget::class, [
        'name' => 'Audit Log',
        'user_id' => auth()->id(),
    ]);
});

it('can edit a notification target', function (): void {
    $target = NotificationTarget::factory()->create([
        'type' => NotificationProviderType::DATABASE,
        'name' => 'Old Name',
    ]);

    livewire(ListNotificationTargets::class)
        ->callAction(TestAction::make('edit')->table($target), [
            'name' => 'New Name',
            'type' => NotificationProviderType::DATABASE->value,
            'is_default' => false,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    assertDatabaseHas(NotificationTarget::class, ['id' => $target->id, 'name' => 'New Name']);
});

it('can delete a notification target', function (): void {
    $target = NotificationTarget::factory()->create();

    livewire(ListNotificationTargets::class)
        ->callAction(TestAction::make('delete')->table($target));

    assertModelMissing($target);
});

it('can bulk delete notification targets', function (): void {
    $targets = NotificationTarget::factory()->count(3)->create();

    livewire(ListNotificationTargets::class)
        ->callTableBulkAction('delete', $targets);

    foreach ($targets as $target) {
        $this->assertModelMissing($target);
    }
});
