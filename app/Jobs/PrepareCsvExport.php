<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

final class PrepareCsvExport extends \Filament\Actions\Exports\Jobs\PrepareCsvExport implements ShouldQueue
{
    public function getExportCsvJob(): string
    {
        return ExportCsvWithLocale::class;
    }
}
