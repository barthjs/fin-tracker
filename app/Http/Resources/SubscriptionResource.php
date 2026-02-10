<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
final class SubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'period_unit' => $this->period_unit,
            'period_frequency' => $this->period_frequency,
            'started_at' => $this->started_at,
            'next_payment_date' => $this->next_payment_date,
            'ended_at' => $this->ended_at,
            'auto_generate_transaction' => $this->auto_generate_transaction,
            'last_generated_at' => $this->last_generated_at,
            'remind_before_payment' => $this->remind_before_payment,
            'reminder_days_before' => $this->reminder_days_before,
            'last_reminded_at' => $this->last_reminded_at,
            'logo' => $this->logo,
            'color' => $this->color,
            'is_active' => $this->is_active,
        ];
    }
}
