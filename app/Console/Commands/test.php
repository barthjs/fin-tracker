<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Scopes\AccountScope;
use App\Models\Scopes\TransactionScope;
use Carbon\Carbon;
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
        for ($i = 1; $i <= 12; $i++) {
            echo $monthColumn = strtolower(Carbon::create(null, $i)->format('M')) . PHP_EOL;;
        }
    }
}
