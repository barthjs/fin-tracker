<?php

declare(strict_types=1);

use App\Console\Commands\App\AppCleanupTempDataCommand;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    Storage::fake('local');
});

it('removes notifications older than seven days', function (): void {
    DB::table('notifications')->insert([
        [
            'id' => Str::uuid()->toString(),
            'type' => 'test',
            'notifiable_type' => 'user',
            'notifiable_id' => '1',
            'data' => '{}',
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ],
        [
            'id' => Str::uuid()->toString(),
            'type' => 'test',
            'notifiable_type' => 'user',
            'notifiable_id' => '1',
            'data' => '{}',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
    ]);

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(DB::table('notifications')->count())->toBe(1);
});

it('deletes completed imports without failed rows', function (): void {
    $user = User::factory()->create();

    $import = new Import;
    $import->user_id = $user->id;
    $import->file_name = 'import.csv';
    $import->file_path = 'does-not-exist.csv';
    $import->importer = 'X';
    $import->total_rows = 1;
    $import->processed_rows = 1;
    $import->successful_rows = 1;
    $import->completed_at = now();
    $import->save();

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(Import::query()->count())->toBe(0);
});

it('deletes old completed exports and their files', function (): void {
    $user = User::factory()->create();

    $export = new Export;
    $export->user_id = $user->id;
    $export->file_name = 'export';
    $export->file_disk = 'local';
    $export->exporter = 'X';
    $export->total_rows = 1;
    $export->successful_rows = 1;
    $export->completed_at = now()->subDays(8);
    $export->save();

    Storage::disk('local')->put('filament_exports/'.$export->id.'/export.csv', 'data');

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(Export::query()->count())->toBe(0)
        ->and(Storage::disk('local')->exists('filament_exports/'.$export->id))->toBeFalse();
});

it('removes expired cache entries while keeping fresh ones', function (): void {
    DB::table(config()->string('cache.stores.database.table'))->insert([
        ['key' => 'expired', 'value' => 'a', 'expiration' => now()->subMinute()->timestamp],
        ['key' => 'fresh', 'value' => 'b', 'expiration' => now()->addHour()->timestamp],
    ]);

    DB::table(config()->string('cache.stores.database.lock_table'))->insert([
        ['key' => 'lock-expired', 'owner' => 'x', 'expiration' => now()->subMinute()->timestamp],
        ['key' => 'lock-fresh', 'owner' => 'y', 'expiration' => now()->addHour()->timestamp],
    ]);

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(DB::table(config()->string('cache.stores.database.table')))->pluck('key')->all()->toBe(['fresh'])
        ->and(DB::table(config()->string('cache.stores.database.lock_table'))->pluck('key')->all())->toBe(['lock-fresh']);
});

it('deletes livewire-tmp files older than 24 hours and keeps recent ones', function (): void {
    $disk = Storage::disk('local');

    $disk->put('livewire-tmp/old.txt', 'old');
    $disk->put('livewire-tmp/recent.txt', 'recent');

    touch($disk->path('livewire-tmp/old.txt'), now()->subHours(25)->timestamp);
    touch($disk->path('livewire-tmp/recent.txt'), now()->subHours(1)->timestamp);

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect($disk->exists('livewire-tmp/old.txt'))->toBeFalse()
        ->and($disk->exists('livewire-tmp/recent.txt'))->toBeTrue();
});

it('succeeds when there is nothing to clean up', function (): void {
    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(DB::table(config()->string('cache.stores.database.table')))->count()->toBe(0)
        ->and(DB::table(config()->string('cache.stores.database.lock_table'))->count())->toBe(0)
        ->and(Storage::disk('local')->files('livewire-tmp'))->toBe([]);
});
