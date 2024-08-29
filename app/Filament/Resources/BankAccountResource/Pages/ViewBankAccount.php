<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\BankAccountResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBankAccount extends ViewRecord
{
    protected static string $resource = BankAccountResource::class;

    public function getTitle(): string
    {
        return __('bank_account.navigation_label');
    }

    public function getHeading(): string
    {
        return __('');
    }
}
