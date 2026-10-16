<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Budget;
use App\Traits\PreservesZeroFraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Budget
 */
final class BudgetResource extends JsonResource
{
    use PreservesZeroFraction;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'period_unit' => $this->period_unit,
            'period_frequency' => $this->period_frequency,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'warning_threshold' => $this->warning_threshold,
            'notify_on_exceed' => $this->notify_on_exceed,
            'last_warned_at' => $this->last_warned_at,
            'last_exceeded_at' => $this->last_exceeded_at,
            'is_active' => $this->is_active,
        ];
    }
}
