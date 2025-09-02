<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Cleanup old Filament import/export data and notifications.
 *
 * - Notifications: Delete notifications older than 7 days.
 * - Imports:
 *     - Delete uploaded import file immediately after "completed_at" is set.
 *     - Delete import record:
 *         - immediately if there are no failed rows,
 *         - otherwise only if "completed_at" is older than 7 days.
 * - Exports:
 *     - Delete the entire directory with the csv and xlsx file
 *      (storage/app/private/filament_exports/{id}) if "completed_at" is older than 7 days.
 *     - Delete the export record afterward.
 * - Livewire temporary files: Delete all files older than 24 hours.
 */
final class CleanupFilamentDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-filament';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old Filament import/export records, notifications and related files.';

    private CarbonInterface $cutoff;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->cutoff = Carbon::now()->subDays(7);

        $this->cleanupNotifications();
        $this->cleanupImports();
        $this->cleanupExports();
        $this->cleanupLivewireTmp();

        return self::SUCCESS;
    }

    private function cleanupNotifications(): void
    {
        DB::table('notifications')
            ->where('created_at', '<', $this->cutoff)
            ->delete();
    }

    private function cleanupImports(): void
    {
        $imports = Import::query()
            ->whereNotNull('completed_at')
            ->get();

        foreach ($imports as $import) {
            // Delete the file immediately if completed
            if (file_exists($import->file_path)) {
                @unlink($import->file_path);
            }

            if (
                ! $import->failedRows()->exists() ||
                /** @phpstan-ignore-next-line */
                ($import->completed_at !== null && Carbon::createFromTimestamp($import->completed_at)->lt($this->cutoff))
            ) {
                $import->delete();
            }
        }
    }

    private function cleanupExports(): void
    {
        $exports = Export::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '<', $this->cutoff)
            ->get();

        foreach ($exports as $export) {
            $exportFolder = "filament_exports/$export->id";
            if (Storage::disk($export->file_disk)->exists($exportFolder)) {
                Storage::disk($export->file_disk)->deleteDirectory($exportFolder);
            }

            $export->delete();
        }
    }

    /**
     * Delete old Livewire temporary upload files.
     *
     * Imports are guaranteed to finish in less than 24 hours,
     * so it is safe to delete files older than that
     * without affecting any in-progress imports.
     */
    private function cleanupLivewireTmp(): void
    {
        $storage = Storage::disk('local');

        /** @var string $filePathname */
        foreach ($storage->files('livewire-tmp') as $filePathname) {
            if (! $storage->exists($filePathname)) {
                continue;
            }

            if (now()->subHours(24)->timestamp > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
            }
        }
    }
}
