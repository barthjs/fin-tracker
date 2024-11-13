<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\Trade;
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
        $securities = Trade::wherePortfolioId(1)
            ->pluck('security_id')
            ->unique()
            ->toArray();
    }
}
