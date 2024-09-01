<?php

namespace App\Console\Commands;

use App\Models\BankAccountTransaction;
use App\Models\Scopes\BankAccountScope;
use App\Models\Scopes\BankAccountTransactionScope;
use Illuminate\Console\Command;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bank = BankAccountTransaction::withoutGlobalScopes([BankAccountTransactionScope::class, BankAccountScope::class])
            ->whereId(353)
            ->first()
            ->updateOrCreate(['bank_account_id' => 1]);
    }
}
