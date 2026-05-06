<?php

declare(strict_types=1);

use App\Jobs\ProcessDueSubscriptionJob;
use App\Jobs\SendSubscriptionReminderJob;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\artisan;

beforeEach(fn () => asUser());

it('dispatches due and reminder subscription jobs', function (): void {
    Bus::fake();

    $account = Account::factory()->create();
    $category = Category::factory()->create();

    Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'next_payment_date' => today(),
        'auto_generate_transaction' => true,
        'is_active' => true,
    ]);

    Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'remind_before_payment' => true,
        'reminder_days_before' => 3,
        'next_payment_date' => today()->addDays(2),
        'is_active' => true,
    ]);

    artisan('app:dispatch-subscription-jobs')->assertSuccessful();

    Bus::assertDispatched(ProcessDueSubscriptionJob::class);
    Bus::assertDispatched(SendSubscriptionReminderJob::class);
});
