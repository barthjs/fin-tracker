<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Filament\Actions\Exports\Jobs\ExportCompletion;
use Illuminate\Support\Number;

final class ExportCompletionWithLocale extends ExportCompletion
{
    public function handle(): void
    {
        /** @var User $user */
        $user = $this->export->user;

        app()->setLocale($user->locale);
        Number::useLocale($user->locale);

        parent::handle();
    }
}
