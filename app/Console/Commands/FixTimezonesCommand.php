<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Symfony\Component\Console\Helper\ProgressBar;

#[Signature('fix-timezones {--timezone=UTC : The fallback timezone to use if the user timezone is UTC} {--force : Skip confirmation prompt}')]
#[Description('Corrects timestamps by treating existing database values as local time and converting them to UTC.')]
final class FixTimezonesCommand extends Command
{
    public function handle(): int
    {
        $this->warn('CRITICAL WARNING: This command converts existing local timestamps to UTC.');
        $this->warn('Running this command more than once will corrupt your data by applying the offset multiple times.');

        if (! $this->option('force')) {
            $confirmed = $this->confirm('Have you backed up your database and are you sure you want to proceed?');

            if (! $confirmed) {
                $this->info('Command cancelled.');

                return self::SUCCESS;
            }
        }

        $fallbackTimezone = $this->option('timezone');

        if (! is_string($fallbackTimezone) || ! in_array($fallbackTimezone, DateTimeZone::listIdentifiers(), true)) {
            $this->error('A valid fallback timezone is required.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($fallbackTimezone): void {
            $this->processTransactions($fallbackTimezone);
            $this->processTrades($fallbackTimezone);
        });

        $this->newLine();
        $this->info('Timestamps corrected successfully.');

        return self::SUCCESS;
    }

    private function processTransactions(string $fallbackTimezone): void
    {
        $query = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->join('sys_users', 'accounts.user_id', '=', 'sys_users.id')
            ->select([
                'transactions.id as transaction_id',
                'transactions.date_time',
                'sys_users.timezone as user_timezone',
            ]);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No transactions found to process.');

            return;
        }

        $this->info('Processing transactions...');

        $this->withProgressBar($count, function (ProgressBar $bar) use ($query, $fallbackTimezone): void {
            $query->orderBy('transactions.id')->chunk(500, function (object $rows) use ($bar, $fallbackTimezone): void {
                /** @var stdClass $row */
                foreach ($rows as $row) {
                    /** @var string $dateTime */
                    $dateTime = $row->date_time;
                    /** @var string $userTz */
                    $userTz = $row->user_timezone;
                    /** @var string $transactionId */
                    $transactionId = $row->transaction_id;

                    $sourceTimezone = ($userTz === 'UTC') ? $fallbackTimezone : $userTz;

                    $correctedUtc = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, $sourceTimezone);

                    if ($correctedUtc === null) {
                        $this->error('Failed to convert transaction timestamp for transaction ID '.$transactionId.'.');
                        Log::error('Failed to convert transaction timestamp for transaction ID '.$transactionId.'.');

                        continue;
                    }

                    DB::table('transactions')
                        ->where('id', $transactionId)
                        ->update(['date_time' => $correctedUtc->setTimezone('UTC')]);

                    $bar->advance();
                }
            });
        });

        $this->newLine();
    }

    private function processTrades(string $fallbackTimezone): void
    {
        $query = DB::table('trades')
            ->join('accounts', 'trades.account_id', '=', 'accounts.id')
            ->join('sys_users', 'accounts.user_id', '=', 'sys_users.id')
            ->select([
                'trades.id as trade_id',
                'trades.date_time',
                'sys_users.timezone as user_timezone',
            ]);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No trades found to process.');

            return;
        }

        $this->info('Processing trades...');

        $this->withProgressBar($count, function (ProgressBar $bar) use ($query, $fallbackTimezone): void {
            $query->orderBy('trades.id')->chunk(500, function (object $rows) use ($bar, $fallbackTimezone): void {
                /** @var stdClass $row */
                foreach ($rows as $row) {
                    /** @var string $dateTime */
                    $dateTime = $row->date_time;
                    /** @var string $userTz */
                    $userTz = $row->user_timezone;
                    /** @var string $tradeId */
                    $tradeId = $row->trade_id;

                    $sourceTimezone = ($userTz === 'UTC') ? $fallbackTimezone : $userTz;

                    $correctedUtc = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, $sourceTimezone);

                    if ($correctedUtc === null) {
                        $this->error('Failed to convert trade timestamp for trade ID '.$tradeId.'.');
                        Log::error('Failed to convert trade timestamp for trade ID '.$tradeId.'.');

                        continue;
                    }

                    DB::table('trades')
                        ->where('id', $tradeId)
                        ->update(['date_time' => $correctedUtc->setTimezone('UTC')]);

                    $bar->advance();
                }
            });
        });

        $this->newLine();
    }
}
