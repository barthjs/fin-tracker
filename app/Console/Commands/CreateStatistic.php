<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Scopes\AccountScope;
use App\Models\Scopes\TransactionScope;
use App\Models\Scopes\CategoryScope;
use App\Models\Scopes\CategoryStatisticScope;
use App\Models\Category;
use App\Models\CategoryStatistic;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateStatistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-statistic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $categoryIds = Category::withoutGlobalScopes([CategoryScope::class])
            ->whereActive(true)
            ->pluck('id');

        $yearCurrent = Carbon::now()->year;
        foreach ($categoryIds as $categoryId) {
            $oldestRecord = Transaction::withoutGlobalScopes([TransactionScope::class, AccountScope::class])
                ->where('category_id', $categoryId)
                ->orderBy('date_time')
                ->first();

            if ($oldestRecord) {
                $yearFirst = Carbon::parse($oldestRecord->date)->year;
                for ($year = $yearFirst; $year <= $yearCurrent; $year++) {
                    for ($i = 1; $i <= 12; $i++) {
                        $sumPerMonth[$i] = Transaction::withoutGlobalScopes([TransactionScope::class, AccountScope::class])
                            ->where('category_id', '=', $categoryId)
                            ->whereYear('date_time', $year)
                            ->whereMonth('date_time', $i)
                            ->sum('amount');

                    }
                    CategoryStatistic::withoutGlobalScopes([CategoryStatisticScope::class])->updateOrCreate(['year' => $year,
                        'category_id' => $categoryId,
                    ],
                        [
                            'jan' => $sumPerMonth[1],
                            'feb' => $sumPerMonth[2],
                            'mar' => $sumPerMonth[3],
                            'apr' => $sumPerMonth[4],
                            'may' => $sumPerMonth[5],
                            'jun' => $sumPerMonth[6],
                            'jul' => $sumPerMonth[7],
                            'aug' => $sumPerMonth[8],
                            'sep' => $sumPerMonth[9],
                            'oct' => $sumPerMonth[10],
                            'nov' => $sumPerMonth[11],
                            'dec' => $sumPerMonth[12],
                        ]);
                }

            }
        }
        return self::SUCCESS;
    }
}
