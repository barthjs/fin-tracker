<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Scopes\AccountScope;
use App\Models\Scopes\TransactionScope;
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
        $bank = Transaction::withoutGlobalScopes([TransactionScope::class, AccountScope::class])
            ->whereId(353)
            ->first()
            ->updateOrCreate(['bank_account_id' => 1]);
    }
}
