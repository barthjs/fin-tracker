<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Jobs\ProcessDueSubscriptionJob;
use App\Jobs\SendSubscriptionReminderJob;
use App\Models\Subscription;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

final readonly class SubscriptionService
{
    private const float DAYS_IN_YEAR = 365.2425;

    private const float WEEKS_IN_YEAR = 52.1775;

    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Subscription
    {
        return DB::transaction(function () use ($data): Subscription {
            $subscription = new Subscription();

            $reminderTargets = $subscription->extractNotificationTargets($data, 'reminder_targets');

            if (isset($data['next_payment_date']) && is_string($data['next_payment_date'])) {
                $data['day_of_month'] = Carbon::parse($data['next_payment_date'])->day;
            }

            $subscription->fill($data);
            $subscription->save();

            if (! empty($reminderTargets)) {
                $subscription->syncNotificationAssignments(
                    NotificationEventType::SUBSCRIPTION_REMINDER,
                    $reminderTargets
                );
            }

            $this->triggerProcessJobIfDue($subscription);

            return $subscription;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        return DB::transaction(function () use ($subscription, $data) {
            $reminderTargets = $subscription->extractNotificationTargets($data, 'reminder_targets');

            if (isset($data['next_payment_date']) && is_string($data['next_payment_date'])) {
                $data['day_of_month'] = Carbon::parse($data['next_payment_date'])->day;
            }

            $subscription->update($data);

            if (! empty($reminderTargets)) {
                $subscription->syncNotificationAssignments(
                    NotificationEventType::SUBSCRIPTION_REMINDER,
                    $reminderTargets
                );
            }

            $refreshed = $subscription->fresh();
            if ($refreshed instanceof Subscription) {
                $this->triggerProcessJobIfDue($refreshed);
            }

            return $subscription;
        });
    }

    /**
     * Dispatches notifications for subscriptions that are due for a reminder.
     */
    public function dispatchReminders(): void
    {
        $today = now()->toDateString();

        Subscription::query()
            ->with(['user', 'notificationAssignments.target'])
            ->where('is_active', true)
            ->where('remind_before_payment', true)
            ->whereRaw('next_payment_date - reminder_days_before <= ?::date', [$today])
            ->where(function (Builder $query): void {
                $query->whereNull('last_reminded_at')
                    ->orWhereRaw('last_reminded_at < next_payment_date - reminder_days_before');
            })
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $today);
            })
            ->chunkById(100, function (Collection $subscriptions): void {
                /** @var Collection<int, Subscription> $subscriptions */
                foreach ($subscriptions as $subscription) {
                    SendSubscriptionReminderJob::dispatch($subscription);
                }
            });
    }

    /**
     * Dispatches jobs to generate transactions for subscriptions that are due.
     */
    public function dispatchDueSubscriptions(): void
    {
        $today = now()->startOfDay();

        Subscription::query()
            ->with(['account', 'category', 'user'])
            ->where('is_active', true)
            ->where('auto_generate_transaction', true)
            ->where('next_payment_date', '<=', $today->toDateString())
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('last_generated_at')
                    ->orWhere('last_generated_at', '<', $today);
            })
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $today->toDateString());
            })
            ->chunkById(100, function (Collection $subscriptions): void {
                /** @var Collection<int, Subscription> $subscriptions */
                foreach ($subscriptions as $subscription) {
                    ProcessDueSubscriptionJob::dispatch($subscription);
                }
            });
    }

    public function generateTransaction(Subscription $subscription): ?Transaction
    {
        return DB::transaction(function () use ($subscription): ?Transaction {
            $today = now()->startOfDay();
            $lastCreatedTransaction = null;
            $generatedCount = 0;

            while (
                $subscription->next_payment_date->lessThanOrEqualTo($today)
                && ($subscription->ended_at === null || $subscription->next_payment_date->lessThanOrEqualTo($subscription->ended_at))
            ) {
                $transactionDate = $subscription->next_payment_date->startOfDay();

                $lastCreatedTransaction = $this->transactionService->create([
                    'date_time' => $transactionDate,
                    'type' => $subscription->category->type,
                    'amount' => $subscription->amount,
                    'payee' => $subscription->name,
                    'account_id' => $subscription->account_id,
                    'category_id' => $subscription->category_id,
                    'subscription_id' => $subscription->id,
                    'notes' => __('subscription.generated_note', ['date' => $transactionDate->toDateString()]),
                ]);

                $subscription->next_payment_date = $this->calculateNextDate($subscription);
                $subscription->last_generated_at = now();
                $subscription->save();

                $generatedCount++;

                if ($generatedCount >= 100) {
                    break;
                }
            }

            if ($generatedCount > 0) {
                $this->sendGenerationNotification($subscription, $generatedCount);
            }

            return $lastCreatedTransaction;
        });
    }

    /**
     * @param  Collection<int, Subscription>  $subscriptions
     * @return array{
     *     monthly_avg: float,
     *     yearly_avg: float,
     *     due_this_month: float,
     *     daily_chart: array<int, float>
     * }
     */
    public function calculateStats(Collection $subscriptions): array
    {
        $monthlyAvg = 0.0;
        $dueThisMonth = 0.0;

        $now = CarbonImmutable::now();
        $monthEnd = $now->endOfMonth();

        $dailyChart = array_fill(0, $now->daysInMonth, 0.0);

        foreach ($subscriptions as $sub) {
            if ($sub->period_frequency < 1) {
                continue;
            }

            $monthlyAvg += $this->calculateMonthlyEquivalent($sub);

            $cursor = $sub->next_payment_date;

            if ($cursor->lt($now->subYear())) {
                continue;
            }

            while ($cursor->lte($monthEnd)) {
                if ($cursor->gte($now->startOfDay())) {
                    $dueThisMonth += $sub->amount;
                }

                if ($cursor->month === $now->month && $cursor->year === $now->year) {
                    $dailyChart[$cursor->day - 1] += $sub->amount;
                }

                $cursor = $this->simulateNextDate($cursor, $sub);
            }
        }

        return [
            'monthly_avg' => $monthlyAvg,
            'yearly_avg' => $monthlyAvg * 12,
            'due_this_month' => $dueThisMonth,
            'daily_chart' => $dailyChart,
        ];
    }

    private function triggerProcessJobIfDue(Subscription $subscription): void
    {
        if (! $subscription->is_active || ! $subscription->auto_generate_transaction) {
            return;
        }

        $today = Carbon::now()->startOfDay();

        $shouldDispatch = false;

        if ($subscription->next_payment_date->lessThan($today)) {
            $shouldDispatch = true;
        } elseif ($subscription->next_payment_date->isToday()) {
            $lastGenerated = $subscription->last_generated_at;
            if ($lastGenerated === null || $lastGenerated->lessThan($today)) {
                $shouldDispatch = true;
            }
        }

        if ($shouldDispatch) {
            DB::afterCommit(fn () => ProcessDueSubscriptionJob::dispatch($subscription));
        }
    }

    private function calculateNextDate(Subscription $subscription): CarbonImmutable
    {
        $currentDate = $subscription->next_payment_date->copy();
        $unit = $subscription->period_unit;
        $freq = $subscription->period_frequency;
        $anchorDay = $subscription->day_of_month;

        $newDate = match ($unit) {
            PeriodUnit::Day => $currentDate->addDays($freq),
            PeriodUnit::Week => $currentDate->addWeeks($freq),
            PeriodUnit::Month => $currentDate->addMonthsNoOverflow($freq),
            PeriodUnit::Year => $currentDate->addYearsNoOverflow($freq),
        };

        // Re-align to the original intended day if the target month allows it.
        // This prevents a subscription starting on the 31st from getting "stuck" on the 28th/30th.
        if ($unit === PeriodUnit::Month || $unit === PeriodUnit::Year) {
            $newDate = $newDate->day(min($anchorDay, $newDate->daysInMonth));
        }

        return $newDate;
    }

    private function simulateNextDate(CarbonImmutable $date, Subscription $sub): CarbonImmutable
    {
        return match ($sub->period_unit) {
            PeriodUnit::Day => $date->addDays($sub->period_frequency),
            PeriodUnit::Week => $date->addWeeks($sub->period_frequency),
            PeriodUnit::Month => $date->addMonths($sub->period_frequency),
            PeriodUnit::Year => $date->addYears($sub->period_frequency),
        };
    }

    private function calculateMonthlyEquivalent(Subscription $sub): float
    {
        return match ($sub->period_unit) {
            PeriodUnit::Day => $sub->amount * ((self::DAYS_IN_YEAR / 12) / $sub->period_frequency),
            PeriodUnit::Week => $sub->amount * ((self::WEEKS_IN_YEAR / 12) / $sub->period_frequency),
            PeriodUnit::Month => $sub->amount / $sub->period_frequency,
            PeriodUnit::Year => $sub->amount / (12 * $sub->period_frequency),
        };
    }

    private function sendGenerationNotification(Subscription $subscription, int $count): void
    {
        $user = $subscription->user;

        if ($count === 1) {
            $title = __('subscription.notifications.generated_title');
            $body = __('subscription.notifications.generated_body', [
                'name' => $subscription->name,
                'amount' => Number::currency($subscription->amount, $subscription->account->currency->value),
            ]);
        } else {
            $title = __('subscription.notifications.catch_up_title', ['count' => $count]);
            $body = __('subscription.notifications.catch_up_body', [
                'count' => $count,
                'name' => $subscription->name,
                'amount' => Number::currency($subscription->amount * $count, $subscription->account->currency->value),
            ]);
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->success()
            ->actions([
                Action::make('view')
                    ->label(__('fields.view'))
                    ->url(fn (): string => ListTransactions::getNavigationUrl()),
            ])
            ->sendToDatabase($user);
    }
}
