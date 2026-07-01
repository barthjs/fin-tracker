<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Http\Traits\HasDynamicPresenceRule;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBudgetRequest extends FormRequest
{
    use HasDynamicPresenceRule;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $p = $this->presenceRule();

        $budget = $this->route('budget');

        return [
            // A budget needs an expense category of the current user, and each category has at most one budget
            'category_id' => [
                $p,
                Rule::exists(Category::class, 'id')
                    ->where('user_id', $this->user()?->id)
                    ->where('type', TransactionType::Expense->value),
                Rule::unique(Budget::class, 'category_id')->ignore($budget instanceof Budget ? $budget->id : null),
            ],
            'amount' => [$p, 'numeric', 'gt:0', 'max:1e9'],
            'period_unit' => [$p, Rule::enum(PeriodUnit::class)],
            'period_frequency' => [$p, 'integer', 'min:1', 'max:365'],
            'starts_at' => [$p, 'date', ...$this->startsAtBoundary($budget)],
            'ends_at' => ['nullable', 'date', ...$this->endsAtBoundary($budget)],
            'warning_threshold' => ['nullable', 'integer', 'min:1', 'max:99'],
            'notify_on_exceed' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Keeps a start date sent alone before the stored end date of the budget.
     *
     * @return array<int, string>
     */
    private function startsAtBoundary(mixed $budget): array
    {
        if ($this->has('ends_at') || ! $budget instanceof Budget || $budget->ends_at === null) {
            return [];
        }

        return ['before_or_equal:'.$budget->ends_at->toDateString()];
    }

    /**
     * Keeps the end date after the start date, compared with the stored start date when the request has none.
     *
     * @return array<int, string>
     */
    private function endsAtBoundary(mixed $budget): array
    {
        if ($this->has('starts_at') || ! $budget instanceof Budget) {
            return ['after_or_equal:starts_at'];
        }

        return ['after_or_equal:'.$budget->starts_at->toDateString()];
    }
}
