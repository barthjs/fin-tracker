<?php

declare(strict_types=1);

namespace App\Observers;

use App\Contracts\HasDeletableFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Automatically handles the removal of files from storage when models are updated or deleted.
 */
final class FileCleanupObserver
{
    /**
     * Compares original and new file paths to delete replaced files and prevent storage orphans.
     */
    public function updated(Model&HasDeletableFiles $model): void
    {
        $disk = $model->getFileDisk();

        foreach ($model->getFileFields() as $field) {
            /** @var string|null $oldPath */
            $oldPath = $model->getOriginal($field);
            /** @var string|null $newPath */
            $newPath = $model->getAttribute($field);

            if (! $oldPath || $oldPath === $newPath) {
                continue;
            }

            try {
                $this->deleteFile($disk, $oldPath);
            } catch (RuntimeException $e) {
                if (is_string($newPath)) {
                    $this->deleteFile($disk, $newPath);
                }
                throw $e;
            }
        }
    }

    /**
     * Ensures all files associated with the model are purged from the disk upon model deletion.
     */
    public function deleting(Model&HasDeletableFiles $model): void
    {
        $disk = $model->getFileDisk();

        foreach ($model->getFileFields() as $field) {
            /** @var string|null $path */
            $path = $model->getAttribute($field);

            if ($path) {
                $this->deleteFile($disk, "$path");
            }
        }
    }

    /**
     * Validates file existence and executes deletion.
     *
     * @throws RuntimeException
     */
    private function deleteFile(string $disk, string $path): void
    {
        if (! Storage::disk($disk)->exists($path)) {
            return;
        }

        if (! Storage::disk($disk)->delete($path)) {
            throw new RuntimeException("Could not delete file: $path on disk $disk");
        }
    }
}
