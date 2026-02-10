<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PeriodUnit;
use App\Http\Traits\HasDynamicPresenceRule;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSubscriptionRequest extends FormRequest
{
    use HasDynamicPresenceRule;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $p = $this->presenceRule();

        return [
            'account_id' => [$p, Rule::exists(Account::class, 'id')],
            'category_id' => [$p, Rule::exists(Category::class, 'id')],
            'name' => [$p, 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => [$p, 'numeric', 'min:0', 'max:1e9'],
            'period_unit' => [$p, Rule::enum(PeriodUnit::class)],
            'period_frequency' => [$p, 'integer', 'min:1', 'max:365'],
            'started_at' => [$p, 'date'],
            'next_payment_date' => [$p, 'date', 'after_or_equal:started_at'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'auto_generate_transaction' => ['boolean'],
            'remind_before_payment' => ['boolean'],
            'reminder_days_before' => ['required_if:remind_before_payment,true', 'integer', 'min:1', 'max:30'],
            'color' => [$p, 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'],
            'is_active' => ['boolean'],
        ];
    }
}
