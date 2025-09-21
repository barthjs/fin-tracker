<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Illuminate\Support\Number;

final class ImportCsvWithLocale extends ImportCsv
{
    public function handle(): void
    {
        /** @var User $user */
        $user = $this->import->user;

        app()->setLocale($user->locale);
        Number::useLocale($user->locale);

        parent::handle();
    }
}
