<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Filament\Actions\Exports\Jobs\ExportCsv;
use Illuminate\Support\Number;

final class ExportCsvWithLocale extends ExportCsv
{
    /**
     * @codeCoverageIgnore Thin locale wrapper; only runs inside the Filament export batch pipeline.
     */
    public function handle(): void
    {
        /** @var User $user */
        $user = $this->export->user;

        app()->setLocale($user->locale);
        Number::useLocale($user->locale);

        parent::handle();
    }
}
