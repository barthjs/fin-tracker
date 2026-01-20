<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Defines models that possess files requiring automatic cleanup upon modification or deletion.
 */
interface HasDeletableFiles
{
    /**
     * Returns a list of model attribute names that store file paths for automated management.
     *
     * @return non-empty-array<string>
     */
    public function getFileFields(): array;

    /**
     * Specifies the storage disk where the associated files are located.
     */
    public function getFileDisk(): string;
}
